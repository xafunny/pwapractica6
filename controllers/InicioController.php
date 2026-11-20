<?php
/**
 * ---------------------------------------------------------------------
 * controllers/InicioController.php
 * ---------------------------------------------------------------------
 * Panel principal (dashboard). Muestra información distinta según el
 * rol del usuario autenticado.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class InicioController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();
        $id = Auth::id();

        // ---- ADMINISTRADOR: métricas generales del gimnasio ----
        if (Auth::es(Auth::ADMIN)) {
            $porRol = Usuario::contarPorRol();
            $this->view('inicio/admin', [
                'titulo'        => 'Panel de administración',
                'miembros'      => $porRol[Auth::MIEMBRO] ?? 0,
                'instructores'  => $porRol[Auth::INSTRUCTOR] ?? 0,
                'instalaciones' => count(Instalacion::all()),
                'clasesProx'    => Clase::contarProximas(),
                'reservasAct'   => Reserva::contarActivas(),
                'proximas'      => array_slice(Clase::listar(null, 'proximas'), 0, 6),
            ]);
            return;
        }

        // ---- INSTRUCTOR: sus próximas clases y accesos rápidos ----
        if (Auth::es(Auth::INSTRUCTOR)) {
            $this->view('inicio/instructor', [
                'titulo'   => 'Panel del instructor',
                'proximas' => array_slice(Clase::listar($id, 'proximas'), 0, 6),
                'totalProx'=> Clase::contarProximas($id),
                'miembros' => count(Usuario::miembrosResumen()),
            ]);
            return;
        }

        // ---- MIEMBRO: rutina de hoy, progreso semanal y reservas ----
        [$hechos, $total] = Ejercicio::progresoSemana($id);
        $rutina = Ejercicio::rutinaSemanal($id);
        $mediciones = Medicion::deMiembro($id);
        $this->view('inicio/miembro', [
            'titulo'      => 'Mi panel',
            'hoy'         => dia_actual(),
            'rutinaHoy'   => $rutina[dia_actual()],
            'hechos'      => $hechos,
            'total'       => $total,
            'reservas'    => array_slice(Reserva::deMiembro($id, true), 0, 4),
            'asistencias' => Reserva::asistencias($id),
            'ultimaMed'   => $mediciones ? end($mediciones) : null,
        ]);
    }
}
