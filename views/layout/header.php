<?php
/**
 * ---------------------------------------------------------------------
 * views/layout/header.php
 * ---------------------------------------------------------------------
 * Encabezado común de todas las páginas: <head>, librerías (Bootstrap 5,
 * Bootstrap Icons, fuentes), barra de navegación según el ROL del
 * usuario y mensajes flash. Se incluye desde Controller::view().
 * Variables: $titulo (string)
 * ---------------------------------------------------------------------
 */
defined('APP') or exit('Acceso directo no permitido');

$rutaActual = is_string($_GET['r'] ?? null) ? $_GET['r'] : '';
$usuario = Auth::user();

/** Devuelve 'active' si la ruta actual empieza con el módulo indicado. */
$activo = fn(string $modulo) => str_starts_with($rutaActual, $modulo . '/') ? 'active' : '';

// Opciones del menú según el rol: [módulo, ruta, icono, texto]
$menu = [];
if (Auth::es(Auth::ADMIN)) {
    $menu = [
        ['inicio', 'inicio/index', 'speedometer2', 'Inicio'],
        ['usuarios', 'usuarios/index', 'people', 'Usuarios'],
        ['instalaciones', 'instalaciones/index', 'building', 'Instalaciones'],
        ['clases', 'clases/index', 'calendar-event', 'Clases'],
        ['reservas', 'reservas/index', 'journal-check', 'Reservas'],
        ['miembros', 'miembros/index', 'person-lines-fill', 'Seguimiento'],
    ];
} elseif (Auth::es(Auth::INSTRUCTOR)) {
    $menu = [
        ['inicio', 'inicio/index', 'speedometer2', 'Inicio'],
        ['clases', 'clases/index', 'calendar-event', 'Mis clases'],
        ['miembros', 'miembros/index', 'person-lines-fill', 'Miembros'],
    ];
} elseif (Auth::es(Auth::MIEMBRO)) {
    $menu = [
        ['inicio', 'inicio/index', 'house', 'Inicio'],
        ['rutina', 'rutina/index', 'lightning-charge', 'Rutina'],
        ['clases', 'clases/index', 'calendar-plus', 'Clases'],
        ['reservas', 'reservas/index', 'journal-check', 'Mis reservas'],
        ['dieta', 'dieta/index', 'egg-fried', 'Dieta'],
        ['progreso', 'progreso/index', 'graph-up-arrow', 'Progreso'],
    ];
}
?>
<!doctype html>
<html lang="es" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Fuerza Pro - Sistema de gestión de gimnasio">
  <!-- Token CSRF que usa assets/js/app.js en las peticiones fetch() -->
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($titulo) ?> · <?= e(APP_NAME) ?></title>

  <!-- Bootstrap 5 + iconos (CDN) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Tipografías: Bebas Neue (títulos) e Inter (texto) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap">
  <!-- Estilos propios -->
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
  <link rel="icon" href="<?= asset('img/logo.svg') ?>" type="image/svg+xml">
</head>
<body>

<?php if ($usuario): ?>
<!-- ============== BARRA DE NAVEGACIÓN (según rol) ============== -->
<nav class="navbar navbar-expand-lg fp-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand fp-logo" href="<?= url('inicio/index') ?>">FUERZA<span>PRO</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
            aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($menu as [$mod, $ruta, $icono, $texto]): ?>
          <li class="nav-item">
            <a class="nav-link <?= $activo($mod) ?>" href="<?= url($ruta) ?>">
              <i class="bi bi-<?= $icono ?>"></i> <?= $texto ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="dropdown">
        <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="fp-avatar"><?= e(iniciales($usuario['nombre'], $usuario['apellido'])) ?></span>
          <span class="text-start lh-sm">
            <span class="d-block fw-semibold"><?= e($usuario['nombre']) ?></span>
            <small class="text-secondary"><?= e($usuario['rol']) ?></small>
          </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= url('perfil/index') ?>"><i class="bi bi-person-gear"></i> Mi perfil</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <!-- Cerrar sesión por POST con CSRF (no por un simple enlace GET) -->
            <form method="post" action="<?= url('auth/salir') ?>">
              <?= csrf_field() ?>
              <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>

<main class="container py-4 fp-main">
  <!-- Mensajes flash (se muestran una sola vez) -->
  <?php foreach (flash() as $f): ?>
    <div class="alert alert-<?= e($f['tipo']) ?> alert-dismissible fade show fp-flash" role="alert">
      <?= e($f['mensaje']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endforeach; ?>
