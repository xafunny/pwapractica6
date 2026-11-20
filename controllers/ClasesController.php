<?php
/**
 * ---------------------------------------------------------------------
 * controllers/ClasesController.php
 * ---------------------------------------------------------------------
 * Gestión de clases grupales.
 *   clases/index      Admin: todas | Instructor: las suyas | Miembro: disponibles para reservar
 *   clases/guardar    crear / editar (Admin e Instructor)           [POST]
 *   clases/estado     cancelar / reprogramar (Admin o su instructor) [POST]
 *   clases/eliminar   eliminar (Admin o su instructor)              [POST]
 *   clases/inscritos  lista de miembros inscritos + asistencia
 *   clases/asistencia marcar Asistió / Faltó vía fetch()            [POST JSON]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class ClasesController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();

        // El miembro ve el catálogo de clases para reservar.
        if (Auth::es(Auth::MIEMBRO)) {
            $tipo = trim((string) ($_GET['tipo'] ?? ''));
            $this->view('clases/disponibles', [
                'titulo' => 'Reservar clases',
                'clases' => Clase::disponibles(Auth::id(), $tipo),
                'tipos'  => Clase::tipos(),
                'tipo'   => $tipo,
            ]);
            return;
        }

        $periodo = in_array($_GET['periodo'] ?? '', ['proximas', 'pasadas', 'todas'], true) ? $_GET['periodo'] : 'proximas';
        $soloMias = Auth::es(Auth::INSTRUCTOR) ? Auth::id() : null;
        $this->view('clases/index', [
            'titulo'        => Auth::es(Auth::INSTRUCTOR) ? 'Mis clases' : 'Clases',
            'clases'        => Clase::listar($soloMias, $periodo),
            'periodo'       => $periodo,
            'instructores'  => Usuario::instructores(),
            'instalaciones' => Instalacion::all('nombre'),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::INSTRUCTOR);
        $this->soloPost();

        $id = post_int('id') ?? 0;
        if ($id) $this->claseEditable($id); // verifica permisos sobre la clase

        $fechaHora = str_replace('T', ' ', post('fecha_hora'));
        $d = [
            'nombre'         => post('nombre'),
            'tipo'           => post('tipo'),
            'descripcion'    => nulo(post('descripcion')),
            // Un instructor solo puede crear clases a su nombre.
            'id_instructor'  => Auth::es(Auth::INSTRUCTOR) ? Auth::id() : (post_int('id_instructor') ?? 0),
            'id_instalacion' => post_int('id_instalacion') ?? 0,
            'fecha_hora'     => $fechaHora,
            'duracion_min'   => post_int('duracion_min') ?? 60,
            'cupo'           => post_int('cupo') ?? 0,
        ];

        $sala = Instalacion::find($d['id_instalacion']);
        $inst = Usuario::find($d['id_instructor']);
        $ts = strtotime($fechaHora);

        $err = null;
        if ($d['nombre'] === '' || $d['tipo'] === '') $err = 'Nombre y tipo son obligatorios.';
        elseif (!$sala) $err = 'Selecciona una instalación válida.';
        elseif (!$inst || (int) $inst['id_rol'] !== Auth::INSTRUCTOR) $err = 'Selecciona un instructor válido.';
        elseif (!$ts) $err = 'Fecha y hora no válidas.';
        elseif (!$id && $ts < time()) $err = 'La clase debe programarse en una fecha futura.';
        elseif ($d['duracion_min'] < 15 || $d['duracion_min'] > 240) $err = 'La duración debe estar entre 15 y 240 minutos.';
        elseif ($d['cupo'] < 1 || $d['cupo'] > (int) $sala['capacidad']) $err = 'El cupo debe estar entre 1 y la capacidad de la sala (' . $sala['capacidad'] . ').';
        elseif (Clase::choqueHorario($d['id_instalacion'], date('Y-m-d H:i:s', $ts), $d['duracion_min'], $id)) $err = 'La instalación ya está ocupada en ese horario.';

        if ($err) {
            flash('danger', $err);
            redirect('clases/index');
        }

        $d['fecha_hora'] = date('Y-m-d H:i:s', $ts);
        if ($id) {
            Clase::update($id, $d);
            flash('success', 'Clase actualizada.');
        } else {
            Clase::create($d);
            flash('success', 'Clase programada.');
        }
        redirect('clases/index');
    }

    public function estado(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::INSTRUCTOR);
        $this->soloPost();
        $c = $this->claseEditable(post_int('id') ?? 0);
        $nuevo = $c['estado'] === 'Programada' ? 'Cancelada' : 'Programada';
        Clase::update((int) $c['id'], ['estado' => $nuevo]);
        flash('success', $nuevo === 'Cancelada' ? 'Clase cancelada. Los miembros la verán como cancelada.' : 'Clase reprogramada.');
        redirect('clases/index');
    }

    public function eliminar(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::INSTRUCTOR);
        $this->soloPost();
        $c = $this->claseEditable(post_int('id') ?? 0);
        Clase::delete((int) $c['id']); // sus reservas se eliminan en cascada
        flash('success', 'Clase eliminada.');
        redirect('clases/index');
    }

    public function inscritos(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::INSTRUCTOR);
        $c = $this->claseEditable((int) ($_GET['id'] ?? 0));
        $this->view('clases/inscritos', [
            'titulo'    => 'Inscritos: ' . $c['nombre'],
            'clase'     => Clase::detalle((int) $c['id']),
            'inscritos' => Reserva::deClase((int) $c['id']),
        ]);
    }

    public function asistencia(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::INSTRUCTOR);
        $this->soloPost();
        $r = Reserva::find(post_int('id') ?? 0);
        $estado = post('estado');
        if (!$r || !in_array($estado, ['Asistio', 'Falto', 'Reservada'], true)) {
            $this->json(['ok' => false, 'error' => 'Datos no válidos'], 400);
        }
        $this->claseEditable((int) $r['id_clase']);
        Reserva::update((int) $r['id'], ['estado' => $estado]);
        $this->json(['ok' => true, 'estado' => $estado]);
    }

    /**
     * Devuelve la clase si existe y el usuario puede modificarla:
     * el administrador cualquiera; el instructor solo las suyas.
     */
    private function claseEditable(int $id): array
    {
        $c = Clase::find($id);
        $permitido = $c && (Auth::es(Auth::ADMIN) || (int) $c['id_instructor'] === Auth::id());
        if (!$permitido) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'Clase no encontrada'], 404);
            flash('danger', 'Clase no encontrada o sin permiso.');
            redirect('clases/index');
        }
        return $c;
    }
}
