<?php
/**
 * ---------------------------------------------------------------------
 * controllers/ProgresoController.php
 * ---------------------------------------------------------------------
 * Composición corporal del miembro (peso, grasa, músculo, IMC) con
 * gráficas de evolución (Chart.js).
 *   progreso/index     tarjetas + gráfica + historial
 *   progreso/guardar   registrar / editar medición  [POST]
 *   progreso/eliminar  eliminar medición            [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class ProgresoController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();
        $m = $this->exigirMiembro('progreso/index', 'Progreso');
        $this->view('progreso/index', [
            'titulo'     => Auth::es(Auth::MIEMBRO) ? 'Mi progreso' : 'Progreso de ' . $m['nombre'],
            'miembro'    => $m,
            'mediciones' => Medicion::deMiembro((int) $m['id']),
            'pm'         => $this->paramsMiembro($m),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $m = $this->exigirMiembro('progreso/index', 'Progreso');
        $id = post_int('id') ?? 0;

        $num = fn(string $k) => post($k) === '' ? null : (float) str_replace(',', '.', post($k));
        $d = [
            'fecha'    => post('fecha'),
            'peso'     => $num('peso'),
            'estatura' => $num('estatura'),
            'grasa'    => $num('grasa'),
            'musculo'  => $num('musculo'),
        ];

        $err = null;
        if (!strtotime($d['fecha']) || $d['fecha'] > date('Y-m-d')) $err = 'Ingresa una fecha válida (no futura).';
        elseif (!$d['peso'] || $d['peso'] < 20 || $d['peso'] > 400) $err = 'El peso debe estar entre 20 y 400 kg.';
        elseif ($d['estatura'] !== null && ($d['estatura'] < 100 || $d['estatura'] > 250)) $err = 'La estatura debe estar entre 100 y 250 cm.';
        elseif ($d['grasa'] !== null && ($d['grasa'] < 2 || $d['grasa'] > 70)) $err = 'El % de grasa debe estar entre 2 y 70.';
        elseif ($d['musculo'] !== null && ($d['musculo'] < 10 || $d['musculo'] > 70)) $err = 'El % de músculo debe estar entre 10 y 70.';
        if ($err) {
            flash('danger', $err);
            redirect('progreso/index', $this->paramsMiembro($m));
        }

        // El IMC se calcula en el servidor a partir de peso y estatura.
        $d['imc'] = Medicion::calcularImc($d['peso'], $d['estatura']);

        if ($id) {
            $med = Medicion::find($id);
            if ($med && (int) $med['id_miembro'] === (int) $m['id']) {
                Medicion::update($id, $d);
                flash('success', 'Medición actualizada.');
            }
        } else {
            Medicion::create($d + ['id_miembro' => $m['id']]);
            flash('success', 'Medición registrada.');
        }
        redirect('progreso/index', $this->paramsMiembro($m));
    }

    public function eliminar(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $m = $this->exigirMiembro('progreso/index', 'Progreso');
        $med = Medicion::find(post_int('id') ?? 0);
        if ($med && (int) $med['id_miembro'] === (int) $m['id']) {
            Medicion::delete((int) $med['id']);
            flash('success', 'Medición eliminada.');
        }
        redirect('progreso/index', $this->paramsMiembro($m));
    }
}
