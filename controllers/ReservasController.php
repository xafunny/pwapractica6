<?php
/**
 * ---------------------------------------------------------------------
 * controllers/ReservasController.php
 * ---------------------------------------------------------------------
 *   reservas/index    Miembro: próximas reservas + historial
 *                     Admin: todas las reservas del gimnasio
 *   reservas/crear    el miembro reserva una clase   [POST JSON]
 *   reservas/cancelar el miembro cancela su reserva  [POST JSON]
 * Las acciones crear/cancelar se llaman con fetch() desde
 * assets/js/reservas.js y responden JSON.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class ReservasController extends Controller
{
    public function index(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::MIEMBRO);

        if (Auth::es(Auth::ADMIN)) {
            $estado = in_array($_GET['estado'] ?? '', ['Reservada', 'Asistio', 'Falto', 'Cancelada'], true) ? $_GET['estado'] : '';
            $this->view('reservas/admin', [
                'titulo'   => 'Reservas',
                'reservas' => Reserva::todas($estado),
                'estado'   => $estado,
            ]);
            return;
        }

        $this->view('reservas/index', [
            'titulo'      => 'Mis reservas',
            'proximas'    => Reserva::deMiembro(Auth::id(), true),
            'historial'   => Reserva::deMiembro(Auth::id(), false),
            'entrenos'    => Ejercicio::historial(Auth::id()),
        ]);
    }

    public function crear(): void
    {
        $this->soloRoles(Auth::MIEMBRO);
        $this->soloPost();
        $clase = post_int('clase') ?? 0;
        $error = Reserva::reservar(Auth::id(), $clase);
        if ($error) $this->json(['ok' => false, 'error' => $error], 409);
        $c = Clase::detalle($clase);
        $this->json(['ok' => true, 'mensaje' => 'Reserva confirmada.', 'ocupados' => (int) $c['ocupados'], 'cupo' => (int) $c['cupo']]);
    }

    public function cancelar(): void
    {
        $this->soloRoles(Auth::MIEMBRO);
        $this->soloPost();
        $clase = post_int('clase') ?? 0;
        $r = Reserva::deMiembroEnClase(Auth::id(), $clase);
        $c = Clase::detalle($clase);
        if (!$r || $r['estado'] !== 'Reservada' || !$c) {
            $this->json(['ok' => false, 'error' => 'No tienes una reserva activa en esta clase.'], 404);
        }
        if (strtotime($c['fecha_hora']) < time()) {
            $this->json(['ok' => false, 'error' => 'La clase ya comenzó; no se puede cancelar.'], 409);
        }
        Reserva::update((int) $r['id'], ['estado' => 'Cancelada']);
        $this->json(['ok' => true, 'mensaje' => 'Reserva cancelada.', 'ocupados' => (int) $c['ocupados'] - 1, 'cupo' => (int) $c['cupo']]);
    }
}
