<?php
/**
 * ---------------------------------------------------------------------
 * controllers/AuthController.php
 * ---------------------------------------------------------------------
 * Inicio de sesión, registro de nuevos miembros y cierre de sesión.
 *   auth/login     GET muestra el formulario, POST valida credenciales
 *   auth/registro  GET formulario, POST crea un usuario con rol Miembro
 *   auth/salir     POST cierra la sesión
 * Los instructores y administradores los crea el Administrador.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class AuthController extends Controller
{
    /** Intentos fallidos permitidos antes de bloquear temporalmente. */
    private const MAX_INTENTOS = 5;
    private const BLOQUEO_SEG  = 60;

    public function login(): void
    {
        if (Auth::check()) redirect('inicio/index');

        $error = '';
        $correo = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = mb_strtolower(post('correo'));
            $clave  = $_POST['contrasena'] ?? '';
            $intentos = $_SESSION['intentos_login'] ?? ['n' => 0, 't' => 0];

            if (!csrf_valido()) {
                $error = 'La sesión expiró, vuelve a intentarlo.';
            } elseif ($intentos['n'] >= self::MAX_INTENTOS && time() - $intentos['t'] < self::BLOQUEO_SEG) {
                $error = 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.';
            } elseif ($correo === '' || $clave === '') {
                $error = 'Completa todos los campos.';
            } else {
                $u = Usuario::porCorreo($correo);
                if (!$u || !password_verify($clave, $u['contrasena'])) {
                    // Mensaje genérico: no revela si el correo existe.
                    $_SESSION['intentos_login'] = ['n' => $intentos['n'] + 1, 't' => time()];
                    $error = 'Correo o contraseña incorrectos.';
                } elseif (!$u['activo']) {
                    $error = 'Tu cuenta fue desactivada. Comunícate con la administración.';
                } else {
                    unset($_SESSION['intentos_login']);
                    Auth::login($u);
                    flash('success', '¡Bienvenido, ' . $u['nombre'] . '!');
                    redirect('inicio/index');
                }
            }
        }

        $this->view('auth/login', ['titulo' => 'Iniciar sesión', 'error' => $error, 'correo' => $correo]);
    }

    public function registro(): void
    {
        if (Auth::check()) redirect('inicio/index');

        $errores = [];
        $datos = ['nombre' => '', 'apellido' => '', 'correo' => '', 'telefono' => '',
                  'fecha_nacimiento' => '', 'genero' => '', 'nivel' => '', 'objetivo' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($datos as $k => $_) $datos[$k] = post($k);
            $datos['correo'] = mb_strtolower($datos['correo']);
            $clave  = $_POST['contrasena']  ?? '';
            $clave2 = $_POST['contrasena2'] ?? '';

            // Validaciones del lado del servidor (JS también valida en el navegador).
            if (!csrf_valido()) $errores[] = 'La sesión expiró, vuelve a intentarlo.';
            if (mb_strlen($datos['nombre']) < 2)   $errores[] = 'Ingresa tu nombre.';
            if (mb_strlen($datos['apellido']) < 2) $errores[] = 'Ingresa tu apellido.';
            if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
            elseif (Usuario::correoExiste($datos['correo'])) $errores[] = 'Ese correo ya está registrado.';
            if ($datos['telefono'] !== '' && !preg_match('/^\d{7,15}$/', $datos['telefono'])) $errores[] = 'El teléfono debe tener solo números (7 a 15 dígitos).';
            if (!in_array($datos['genero'], ['Masculino', 'Femenino', 'Otro'], true)) $errores[] = 'Selecciona tu género.';
            if (!in_array($datos['nivel'], ['Principiante', 'Intermedio', 'Avanzado'], true)) $errores[] = 'Selecciona tu nivel.';
            if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/\d/', $clave)) {
                $errores[] = 'La contraseña debe tener al menos 8 caracteres, con letras y números.';
            }
            if ($clave !== $clave2) $errores[] = 'Las contraseñas no coinciden.';

            if (!$errores) {
                Usuario::create([
                    'nombre'           => $datos['nombre'],
                    'apellido'         => $datos['apellido'],
                    'correo'           => $datos['correo'],
                    'contrasena'       => password_hash($clave, PASSWORD_DEFAULT),
                    'id_rol'           => Auth::MIEMBRO,
                    'telefono'         => nulo($datos['telefono']),
                    'fecha_nacimiento' => nulo($datos['fecha_nacimiento']),
                    'genero'           => $datos['genero'],
                    'nivel'            => $datos['nivel'],
                    'objetivo'         => nulo($datos['objetivo']),
                ]);
                Auth::login(Usuario::porCorreo($datos['correo'])); // inicia sesión automáticamente
                flash('success', '¡Cuenta creada! Ya puedes reservar clases y armar tu rutina.');
                redirect('inicio/index');
            }
        }

        $this->view('auth/registro', ['titulo' => 'Crear cuenta', 'errores' => $errores, 'd' => $datos]);
    }

    public function salir(): void
    {
        $this->soloPost();
        Auth::logout();
        session_start();          // nueva sesión solo para el mensaje flash
        flash('info', 'Sesión cerrada correctamente.');
        redirect('auth/login');
    }
}
