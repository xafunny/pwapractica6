<?php
/**
 * views/auth/registro.php - Registro público de nuevos MIEMBROS.
 * Variables: $errores (array), $d (datos del formulario para no perderlos)
 */
defined('APP') or exit('Acceso directo no permitido');
$sel = fn($campo, $valor) => $d[$campo] === $valor ? 'selected' : '';
?>
<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-7">
    <div class="card fp-card">
      <div class="card-body p-4 p-md-5">
        <div class="fp-logo mb-1">FUERZA<span>PRO</span></div>
        <h1 class="h4 fw-bold">Crear cuenta de miembro</h1>
        <p class="text-secondary small mb-4">Completa tus datos para empezar a entrenar.</p>

        <?php if ($errores): ?>
          <div class="alert alert-danger small">
            <ul class="mb-0 ps-3"><?php foreach ($errores as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= url('auth/registro') ?>" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="nombre">Nombre</label>
              <input class="form-control" id="nombre" name="nombre" value="<?= e($d['nombre']) ?>" required minlength="2" maxlength="50">
              <div class="invalid-feedback">Ingresa tu nombre.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="apellido">Apellido</label>
              <input class="form-control" id="apellido" name="apellido" value="<?= e($d['apellido']) ?>" required minlength="2" maxlength="50">
              <div class="invalid-feedback">Ingresa tu apellido.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="correo">Correo</label>
              <input type="email" class="form-control" id="correo" name="correo" value="<?= e($d['correo']) ?>" required maxlength="100">
              <div class="invalid-feedback">Correo no válido.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="telefono">Teléfono <span class="text-secondary">(opcional)</span></label>
              <input class="form-control" id="telefono" name="telefono" value="<?= e($d['telefono']) ?>" pattern="\d{7,15}" inputmode="numeric">
              <div class="invalid-feedback">Solo números, de 7 a 15 dígitos.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="fecha_nacimiento">Fecha de nacimiento</label>
              <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= e($d['fecha_nacimiento']) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="genero">Género</label>
              <select class="form-select" id="genero" name="genero" required>
                <option value="">Seleccionar…</option>
                <?php foreach (['Masculino', 'Femenino', 'Otro'] as $g): ?>
                  <option <?= $sel('genero', $g) ?>><?= $g ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Selecciona una opción.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="nivel">Nivel</label>
              <select class="form-select" id="nivel" name="nivel" required>
                <option value="">Seleccionar…</option>
                <?php foreach (['Principiante', 'Intermedio', 'Avanzado'] as $n): ?>
                  <option <?= $sel('nivel', $n) ?>><?= $n ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Selecciona tu nivel.</div>
            </div>
            <div class="col-12">
              <label class="form-label" for="objetivo">¿Cuál es tu objetivo? <span class="text-secondary">(opcional)</span></label>
              <input class="form-control" id="objetivo" name="objetivo" value="<?= e($d['objetivo']) ?>" maxlength="255" placeholder="Ej.: bajar de peso, ganar masa muscular…">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="contrasena">Contraseña</label>
              <input type="password" class="form-control" id="contrasena" name="contrasena" required minlength="8"
                     pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password" data-medidor="medidorClave">
              <div class="progress mt-2" style="height:4px"><div class="progress-bar" id="medidorClave"></div></div>
              <div class="invalid-feedback">Mínimo 8 caracteres, con letras y números.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="contrasena2">Repetir contraseña</label>
              <input type="password" class="form-control" id="contrasena2" name="contrasena2" required data-igual-a="contrasena" autocomplete="new-password">
              <div class="invalid-feedback">Las contraseñas no coinciden.</div>
            </div>
          </div>
          <button class="btn btn-danger w-100 py-2 fw-semibold mt-4">Crear cuenta</button>
        </form>
        <p class="text-center small mt-4 mb-0">¿Ya tienes cuenta? <a href="<?= url('auth/login') ?>" class="link-danger fw-semibold">Inicia sesión</a></p>
      </div>
    </div>
  </div>
</div>
