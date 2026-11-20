<?php
/**
 * ---------------------------------------------------------------------
 * controllers/UsuariosController.php      (solo ADMINISTRADOR)
 * ---------------------------------------------------------------------
 * CRUD de usuarios: miembros, instructores y administradores.
 *   usuarios/index     listado con filtros por rol y búsqueda
 *   usuarios/guardar   crear (sin id) o editar (con id)  [POST]
 *   usuarios/estado    activar / desactivar               [POST]
 *   usuarios/eliminar  eliminar                           [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class UsuariosController extends Controller
{
    public function index(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $rol = (int) ($_GET['rol'] ?? 0) ?: null;
        $buscar = trim((string) ($_GET['q'] ?? ''));
        $this->view('usuarios/index', [
            'titulo'   => 'Usuarios',
            'usuarios' => Usuario::listar($rol, $buscar),
            'rol'      => $rol,
            'buscar'   => $buscar,
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();

        $id = post_int('id') ?? 0;
        $d = [
            'nombre'           => post('nombre'),
            'apellido'         => post('apellido'),
            'correo'           => mb_strtolower(post('correo')),
            'id_rol'           => post_int('id_rol'),
            'telefono'         => nulo(post('telefono')),
            'fecha_nacimiento' => nulo(post('fecha_nacimiento')),
            'genero'           => nulo(post('genero')),
            'especialidad'     => nulo(post('especialidad')),
            'nivel'            => nulo(post('nivel')),
            'objetivo'         => nulo(post('objetivo')),
        ];
        $clave = $_POST['contrasena'] ?? '';

        // --- Validaciones ---
        $err = null;
        if (mb_strlen($d['nombre']) < 2) $err = 'El nombre es obligatorio.';
        elseif (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) $err = 'Correo no válido.';
        elseif (Usuario::correoExiste($d['correo'], $id)) $err = 'Ese correo ya está registrado.';
        elseif (!in_array($d['id_rol'], [Auth::ADMIN, Auth::INSTRUCTOR, Auth::MIEMBRO], true)) $err = 'Rol no válido.';
        elseif (!$id && strlen($clave) < 8) $err = 'La contraseña debe tener al menos 8 caracteres.';
        elseif ($id && $clave !== '' && strlen($clave) < 8) $err = 'La nueva contraseña debe tener al menos 8 caracteres.';
        elseif ($id === Auth::id() && $d['id_rol'] !== Auth::ADMIN) $err = 'No puedes quitarte el rol de administrador a ti mismo.';
        elseif ($d['genero'] && !in_array($d['genero'], ['Masculino', 'Femenino', 'Otro'], true)) $err = 'Género no válido.';
        elseif ($d['nivel'] && !in_array($d['nivel'], ['Principiante', 'Intermedio', 'Avanzado'], true)) $err = 'Nivel no válido.';

        if ($err) {
            flash('danger', $err);
            redirect('usuarios/index');
        }

        // Campos que solo aplican a un rol se limpian en los demás.
        if ($d['id_rol'] !== Auth::INSTRUCTOR) $d['especialidad'] = null;
        if ($d['id_rol'] !== Auth::MIEMBRO) { $d['nivel'] = null; $d['objetivo'] = null; }

        if ($clave !== '') $d['contrasena'] = password_hash($clave, PASSWORD_DEFAULT);

        if ($id) {
            if ($d['id_rol'] !== Auth::INSTRUCTOR && Usuario::tieneClases($id)) {
                flash('danger', 'Este instructor tiene clases asignadas. Reasígnalas antes de cambiar su rol.');
                redirect('usuarios/index');
            }
            Usuario::update($id, $d);
            if ($id === Auth::id()) Auth::refrescar($d);
            flash('success', 'Usuario actualizado.');
        } else {
            Usuario::create($d);
            flash('success', 'Usuario creado.');
        }
        redirect('usuarios/index');
    }

    public function estado(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $id = post_int('id') ?? 0;
        $u = Usuario::find($id);
        if (!$u || $id === Auth::id()) $this->json(['ok' => false, 'error' => 'No se puede cambiar el estado de este usuario.'], 400);
        $nuevo = $u['activo'] ? 0 : 1;
        Usuario::update($id, ['activo' => $nuevo]);
        $this->json(['ok' => true, 'activo' => $nuevo]);
    }

    public function eliminar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $id = post_int('id') ?? 0;
        if ($id === Auth::id()) {
            flash('danger', 'No puedes eliminar tu propia cuenta.');
        } elseif (Usuario::tieneClases($id)) {
            flash('danger', 'El instructor tiene clases asignadas. Elimina o reasigna sus clases primero (o desactívalo).');
        } else {
            Usuario::delete($id); // reservas, rutina, dieta y mediciones se borran en cascada
            flash('success', 'Usuario eliminado.');
        }
        redirect('usuarios/index');
    }
}
