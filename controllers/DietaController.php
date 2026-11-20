<?php
/**
 * ---------------------------------------------------------------------
 * controllers/DietaController.php
 * ---------------------------------------------------------------------
 * Plan de alimentación semanal del miembro.
 *   - El MIEMBRO consulta su dieta (solo lectura).
 *   - El INSTRUCTOR / ADMIN la crean y editan.
 *
 *   dieta/index     plan por día (?dia=Lunes&miembro=4)
 *   dieta/guardar   crear / editar comida   [POST]
 *   dieta/eliminar  eliminar comida         [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class DietaController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();
        $m = $this->exigirMiembro('dieta/index', 'Dietas');
        $dia = in_array($_GET['dia'] ?? '', DIAS, true) ? $_GET['dia'] : dia_actual();
        $this->view('dieta/index', [
            'titulo'   => Auth::es(Auth::MIEMBRO) ? 'Mi dieta' : 'Dieta de ' . $m['nombre'],
            'miembro'  => $m,
            'plan'     => Comida::planSemanal((int) $m['id']),
            'dia'      => $dia,
            'editable' => !Auth::es(Auth::MIEMBRO),
            'pm'       => $this->paramsMiembro($m),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::INSTRUCTOR);
        $this->soloPost();
        $m = $this->exigirMiembro('dieta/index', 'Dietas');
        $id = post_int('id') ?? 0;
        $d = [
            'dia'         => post('dia'),
            'momento'     => post('momento'),
            'descripcion' => post('descripcion'),
            'calorias'    => post_int('calorias'),
        ];
        $volver = $this->paramsMiembro($m, ['dia' => $d['dia']]);

        if (!in_array($d['dia'], DIAS, true) || !in_array($d['momento'], MOMENTOS, true) || $d['descripcion'] === '') {
            flash('danger', 'Completa el día, el momento y la descripción de la comida.');
            redirect('dieta/index', $volver);
        }
        if ($d['calorias'] !== null && ($d['calorias'] < 0 || $d['calorias'] > 5000)) {
            flash('danger', 'Las calorías deben estar entre 0 y 5000.');
            redirect('dieta/index', $volver);
        }

        if ($id) {
            $c = Comida::find($id);
            if (!$c || (int) $c['id_miembro'] !== (int) $m['id']) redirect('dieta/index', $volver);
            Comida::update($id, $d);
            flash('success', 'Comida actualizada.');
        } else {
            Comida::create($d + ['id_miembro' => $m['id']]);
            flash('success', 'Comida agregada.');
        }
        redirect('dieta/index', $volver);
    }

    public function eliminar(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::INSTRUCTOR);
        $this->soloPost();
        $m = $this->exigirMiembro('dieta/index', 'Dietas');
        $c = Comida::find(post_int('id') ?? 0);
        if ($c && (int) $c['id_miembro'] === (int) $m['id']) {
            Comida::delete((int) $c['id']);
            flash('success', 'Comida eliminada.');
        }
        redirect('dieta/index', $this->paramsMiembro($m, ['dia' => $c['dia'] ?? dia_actual()]));
    }
}
