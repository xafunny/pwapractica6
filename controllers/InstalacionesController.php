<?php
/**
 * ---------------------------------------------------------------------
 * controllers/InstalacionesController.php   (solo ADMINISTRADOR)
 * ---------------------------------------------------------------------
 * CRUD de las instalaciones (salas) del gimnasio.
 *   instalaciones/index     listado en tarjetas
 *   instalaciones/guardar   crear / editar  [POST]
 *   instalaciones/eliminar  eliminar        [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class InstalacionesController extends Controller
{
    public function index(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->view('instalaciones/index', [
            'titulo'        => 'Instalaciones',
            'instalaciones' => Instalacion::conUso(),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $id = post_int('id') ?? 0;
        $d = [
            'nombre'      => post('nombre'),
            'tipo'        => post('tipo'),
            'capacidad'   => post_int('capacidad') ?? 0,
            'descripcion' => nulo(post('descripcion')),
        ];
        if ($d['nombre'] === '' || $d['tipo'] === '' || $d['capacidad'] < 1) {
            flash('danger', 'Completa nombre, tipo y una capacidad mayor a 0.');
            redirect('instalaciones/index');
        }
        if ($id) {
            Instalacion::update($id, $d);
            flash('success', 'Instalación actualizada.');
        } else {
            Instalacion::create($d);
            flash('success', 'Instalación creada.');
        }
        redirect('instalaciones/index');
    }

    public function eliminar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $id = post_int('id') ?? 0;
        if (Instalacion::enUso($id)) {
            flash('danger', 'No se puede eliminar: hay clases registradas en esta instalación.');
        } else {
            Instalacion::delete($id);
            flash('success', 'Instalación eliminada.');
        }
        redirect('instalaciones/index');
    }
}
