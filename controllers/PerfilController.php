<?php
/**
 * ---------------------------------------------------------------------
 * controllers/PerfilController.php   (todos los roles)
 * ---------------------------------------------------------------------
 *   perfil/index       ver y editar datos personales
 *   perfil/guardar     guardar datos personales     [POST]
 *   perfil/contrasena  cambiar contraseña           [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class PerfilController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();
        $this->view('perfil/index', [
            'titulo' => 'Mi perfil',
            'u'      => Usuario::find(Auth::id()),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $d = [
            'nombre'           => post('nombre'),
            'apellido'         => post('apellido'),
            'correo'           => mb_strtolower(post('correo')),
            'telefono'         => nulo(post('telefono')),
            'fecha_nacimiento' => nulo(post('fecha_nacimiento')),
            'genero'           => nulo(post('genero')),
        ];
        // Campos según el rol.
        if (Auth::es(Auth::MIEMBRO)) {
            $d['nivel']    = nulo(post('nivel'));
            $d['objetivo'] = nulo(post('objetivo'));
        }
        if (Auth::es(Auth::INSTRUCTOR)) {
            $d['especialidad'] = nulo(post('especialidad'));
        }

        $err = null;
        if (mb_strlen($d['nombre']) < 2) $err = 'El nombre es obligatorio.';
        elseif (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) $err = 'Correo no válido.';
        elseif (Usuario::correoExiste($d['correo'], Auth::id())) $err = 'Ese correo ya está en uso.';
        elseif ($d['telefono'] && !preg_match('/^\d{7,15}$/', $d['telefono'])) $err = 'El teléfono debe tener solo números (7 a 15 dígitos).';
        elseif ($d['genero'] && !in_array($d['genero'], ['Masculino', 'Femenino', 'Otro'], true)) $err = 'Género no válido.';
        elseif (isset($d['nivel']) && $d['nivel'] && !in_array($d['nivel'], ['Principiante', 'Intermedio', 'Avanzado'], true)) $err = 'Nivel no válido.';

        if ($err) {
            flash('danger', $err);
        } else {
            Usuario::update(Auth::id(), $d);
            Auth::refrescar($d);
            flash('success', 'Perfil actualizado.');
        }
        redirect('perfil/index');
    }

    public function contrasena(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $u = Usuario::find(Auth::id());
        $actual = $_POST['actual'] ?? '';
        $nueva  = $_POST['nueva'] ?? '';
        $nueva2 = $_POST['nueva2'] ?? '';

        if (!password_verify($actual, $u['contrasena'])) {
            flash('danger', 'La contraseña actual no es correcta.');
        } elseif (strlen($nueva) < 8 || !preg_match('/[A-Za-z]/', $nueva) || !preg_match('/\d/', $nueva)) {
            flash('danger', 'La nueva contraseña debe tener al menos 8 caracteres, con letras y números.');
        } elseif ($nueva !== $nueva2) {
            flash('danger', 'Las contraseñas nuevas no coinciden.');
        } else {
            Usuario::cambiarContrasena(Auth::id(), $nueva);
            flash('success', 'Contraseña actualizada.');
        }
        redirect('perfil/index');
    }
}
