<?php
/**
 * ---------------------------------------------------------------------
 * controllers/RutinaController.php
 * ---------------------------------------------------------------------
 * Rutina semanal de ejercicios (funcionalidad original de Fuerza Pro).
 *   - El MIEMBRO ve y edita su propia rutina y marca los ejercicios
 *     hechos en la semana.
 *   - El INSTRUCTOR / ADMIN eligen un miembro y le arman la rutina.
 *
 *   rutina/index     vista por días (?dia=Lunes&miembro=4)
 *   rutina/guardar   crear / editar ejercicio       [POST]
 *   rutina/eliminar  eliminar ejercicio             [POST]
 *   rutina/marcar    marcar/desmarcar como hecho     [POST JSON]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class RutinaController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();
        $m = $this->exigirMiembro('rutina/index', 'Rutinas');
        $dia = in_array($_GET['dia'] ?? '', DIAS, true) ? $_GET['dia'] : dia_actual();
        [$hechos, $total] = Ejercicio::progresoSemana((int) $m['id']);

        $this->view('rutina/index', [
            'titulo'  => Auth::es(Auth::MIEMBRO) ? 'Mi rutina' : 'Rutina de ' . $m['nombre'],
            'miembro' => $m,
            'rutina'  => Ejercicio::rutinaSemanal((int) $m['id']),
            'dia'     => $dia,
            'hechos'  => $hechos,
            'total'   => $total,
            'pm'      => $this->paramsMiembro($m),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $m = $this->exigirMiembro('rutina/index', 'Rutinas');
        $id = post_int('id') ?? 0;

        $d = [
            'dia'          => post('dia'),
            'nombre'       => post('nombre'),
            'series'       => nulo(post('series')),
            'repeticiones' => nulo(post('repeticiones')),
            'descanso'     => nulo(post('descanso')),
            'peso'         => nulo(post('peso')),
            'notas'        => nulo(post('notas')),
        ];
        $volver = $this->paramsMiembro($m, ['dia' => $d['dia']]);

        if (!in_array($d['dia'], DIAS, true) || $d['nombre'] === '' || mb_strlen($d['nombre']) > 60) {
            flash('danger', 'Indica el día y un nombre de ejercicio (máx. 60 caracteres).');
            redirect('rutina/index', $volver);
        }

        if ($id) {
            $this->ejercicioDe($id, $m);
            Ejercicio::update($id, $d);
            flash('success', 'Ejercicio actualizado.');
        } else {
            $d['id_miembro'] = $m['id'];
            $d['orden']      = Ejercicio::siguienteOrden((int) $m['id'], $d['dia']);
            $d['creado_por'] = Auth::id();
            Ejercicio::create($d);
            flash('success', 'Ejercicio agregado a ' . dia_bonito($d['dia']) . '.');
        }
        redirect('rutina/index', $volver);
    }

    public function eliminar(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $m = $this->exigirMiembro('rutina/index', 'Rutinas');
        $e = $this->ejercicioDe(post_int('id') ?? 0, $m);
        Ejercicio::delete((int) $e['id']);
        flash('success', 'Ejercicio eliminado.');
        redirect('rutina/index', $this->paramsMiembro($m, ['dia' => $e['dia']]));
    }

    public function marcar(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $m = $this->exigirMiembro('rutina/index', 'Rutinas');
        $e = $this->ejercicioDe(post_int('id') ?? 0, $m);
        $hecho = Ejercicio::alternarHecho((int) $e['id']);
        [$hechos, $total] = Ejercicio::progresoSemana((int) $m['id']);
        $this->json(['ok' => true, 'hecho' => $hecho, 'hechos' => $hechos, 'total' => $total]);
    }

    /** Devuelve el ejercicio si pertenece al miembro; si no, corta la petición. */
    private function ejercicioDe(int $id, array $m): array
    {
        $e = Ejercicio::find($id);
        if (!$e || (int) $e['id_miembro'] !== (int) $m['id']) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'Ejercicio no encontrado'], 404);
            flash('danger', 'Ejercicio no encontrado.');
            redirect('rutina/index', $this->paramsMiembro($m));
        }
        return $e;
    }
}
