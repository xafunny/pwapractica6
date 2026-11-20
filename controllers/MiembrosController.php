<?php
/**
 * ---------------------------------------------------------------------
 * controllers/MiembrosController.php   (ADMINISTRADOR e INSTRUCTOR)
 * ---------------------------------------------------------------------
 * Listado de miembros con accesos directos a su rutina, dieta y
 * progreso. El instructor lo usa para dar seguimiento a sus alumnos.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class MiembrosController extends Controller
{
    public function index(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::INSTRUCTOR);
        $buscar = trim((string) ($_GET['q'] ?? ''));
        $this->view('miembros/index', [
            'titulo'   => 'Miembros',
            'miembros' => Usuario::miembrosResumen($buscar),
            'buscar'   => $buscar,
        ]);
    }
}
