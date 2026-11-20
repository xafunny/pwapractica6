<?php
/**
 * ---------------------------------------------------------------------
 * core/helpers.php
 * ---------------------------------------------------------------------
 * Funciones de ayuda globales usadas en controladores y vistas:
 *   - e()            escapar HTML (previene XSS)
 *   - url()          construir enlaces internos (?r=controlador/accion)
 *   - redirect()     redirigir y terminar
 *   - flash()        mensajes de una sola vez entre peticiones
 *   - csrf_*()       protección contra CSRF en formularios y fetch()
 *   - fechas y días en español
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

/** Días de la semana tal como se guardan en la BD (sin tildes). */
const DIAS = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

/** Momentos de comida del plan de alimentación. */
const MOMENTOS = ['Desayuno', 'Almuerzo', 'Merienda', 'Cena'];

/** Escapa texto para imprimirlo en HTML de forma segura. */
function e($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Construye una URL interna.
 *   url('clases/index', ['id' => 3])  ->  index.php?r=clases/index&id=3
 */
function url(string $ruta = 'inicio/index', array $params = []): string
{
    $params = array_merge(['r' => $ruta], $params);
    return 'index.php?' . http_build_query($params);
}

/** Ruta pública de un recurso estático (css, js, img). */
function asset(string $ruta): string
{
    $archivo = __DIR__ . '/../assets/' . $ruta;
    // Se agrega la fecha de modificación para evitar caché vieja del navegador.
    $version = is_file($archivo) ? filemtime($archivo) : 1;
    return 'assets/' . $ruta . '?v=' . $version;
}

/** Redirige a otra ruta interna y detiene la ejecución. */
function redirect(string $ruta, array $params = []): never
{
    header('Location: ' . url($ruta, $params));
    exit;
}

/**
 * Guarda (con 2 argumentos) o lee y borra (sin argumentos) los mensajes
 * flash que se muestran una sola vez como alertas de Bootstrap.
 *   flash('success', 'Guardado');   // guardar
 *   foreach (flash() as $m) ...      // leer
 */
function flash(?string $tipo = null, ?string $mensaje = null): array
{
    if ($tipo !== null) {
        $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
        return [];
    }
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

/** Token CSRF de la sesión (se crea una vez por sesión). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo oculto con el token CSRF para incluir en cada <form method="post">. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Verifica el token CSRF recibido por POST (formularios) o por la
 * cabecera X-CSRF-Token (peticiones fetch desde JavaScript).
 */
function csrf_valido(): bool
{
    $recibido = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($recibido) && hash_equals(csrf_token(), $recibido);
}

/** Lee un campo POST como texto recortado. */
function post(string $campo, string $defecto = ''): string
{
    $v = $_POST[$campo] ?? $defecto;
    return is_string($v) ? trim($v) : $defecto;
}

/** Lee un campo POST como entero (o null si viene vacío). */
function post_int(string $campo): ?int
{
    $v = post($campo);
    return $v === '' ? null : (int) $v;
}

/** Convierte una cadena vacía en null (para columnas opcionales). */
function nulo(string $v): ?string
{
    return $v === '' ? null : $v;
}

/** Nombre del día con tildes para mostrar en pantalla. */
function dia_bonito(string $dia): string
{
    return ['Miercoles' => 'Miércoles', 'Sabado' => 'Sábado'][$dia] ?? $dia;
}

/** Día de la semana actual en el formato de la BD (Lunes..Domingo). */
function dia_actual(): string
{
    return DIAS[(int) date('N') - 1];
}

/** Fecha (Y-m-d) del lunes de la semana actual. */
function inicio_semana(): string
{
    return date('Y-m-d', strtotime('monday this week'));
}

/** Formatea 2025-03-04 => 04/03/2025 (y la hora si la tiene). */
function fecha(?string $valor, bool $conHora = false): string
{
    if (!$valor) return '';
    $t = strtotime($valor);
    return $conHora ? date('d/m/Y H:i', $t) : date('d/m/Y', $t);
}

/** Nombre corto del día y fecha de una clase, ej.: "Mié 05/03 · 18:00". */
function fecha_clase(string $valor): string
{
    $t = strtotime($valor);
    $dias = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    return $dias[(int) date('N', $t) - 1] . ' ' . date('d/m', $t) . ' · ' . date('H:i', $t);
}

/** Calcula la edad a partir de la fecha de nacimiento. */
function edad(?string $fechaNac): ?int
{
    if (!$fechaNac) return null;
    return (new DateTime($fechaNac))->diff(new DateTime())->y;
}

/** Iniciales para el avatar (ej. "Luis Paredes" -> "LP"). */
function iniciales(string $nombre, string $apellido = ''): string
{
    return mb_strtoupper(mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1));
}
