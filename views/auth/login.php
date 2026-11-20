<?php
/**
 * views/auth/login.php - Formulario de inicio de sesión.
 * Variables: $error (string), $correo (string)
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="fp-auth">
  <div class="fp-auth-hero d-none d-lg-flex">
    <div>
      <div class="fp-logo fp-logo-xl">FUERZA<span>PRO</span></div>
      <p class="lead text-secondary mt-3">Tu gimnasio en un solo lugar: reserva clases, sigue tu rutina semanal y mide tu progreso.</p>
      <ul class="list-unstyled mt-4 fp-feature-list">
        <li><i class="bi bi-calendar-check"></i> Reserva clases con cupos en tiempo real</li>
        <li><i class="bi bi-lightning-charge"></i> Rutina semanal con seguimiento</li>
        <li><i class="bi bi-graph-up-arrow"></i> Gráficas de peso, grasa y músculo</li>
      </ul>
    </div>
  </div>

  <div class="card fp-card fp-auth-box">
    <div class="card-body p-4 p-md-5">
      <div class="fp-logo text-center d-lg-none mb-2">FUERZA<span>PRO</span></div>
      <h1 class="h4 fw-bold mb-1">Iniciar sesión</h1>
      <p class="text-secondary small mb-4">Ingresa con tu correo y contraseña.</p>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle"></i> <?= e($error) ?></div>
      <?php endif; ?>

      <!-- novalidate: la validación la hace app.js con estilos de Bootstrap -->
      <form method="post" action="<?= url('auth/login') ?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label for="correo" class="form-label">Correo electrónico</label>
          <input type="email" class="form-control" id="correo" name="correo" value="<?= e($correo) ?>" required autofocus autocomplete="email">
          <div class="invalid-feedback">Ingresa un correo válido.</div>
        </div>
        <div class="mb-4">
          <label for="contrasena" class="form-label">Contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" id="contrasena" name="contrasena" required autocomplete="current-password">
            <button class="btn btn-outline-secondary" type="button" data-toggle-pass="contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback">Ingresa tu contraseña.</div>
          </div>
        </div>
        <button class="btn btn-danger w-100 py-2 fw-semibold">Entrar <i class="bi bi-arrow-right"></i></button>
      </form>

      <p class="text-center small mt-4 mb-0">¿Aún no eres miembro?
        <a href="<?= url('auth/registro') ?>" class="link-danger fw-semibold">Crea tu cuenta</a></p>
    </div>
  </div>
</div>
