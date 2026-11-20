<?php
/**
 * views/perfil/index.php - Datos personales y cambio de contraseña.
 * Los campos extra dependen del rol (nivel/objetivo para miembros,
 * especialidad para instructores).
 * Variables: $u (usuario completo)
 */
defined('APP') or exit('Acceso directo no permitido');
$sel = fn($campo, $valor) => ($u[$campo] ?? '') === $valor ? 'selected' : '';
?>
<div class="fp-page-head">
  <div class="d-flex align-items-center gap-3">
    <span class="fp-avatar fp-avatar-xl"><?= e(iniciales($u['nombre'], $u['apellido'])) ?></span>
    <div>
      <h1 class="fp-title mb-0"><?= e($u['nombre'] . ' ' . $u['apellido']) ?></h1>
      <p class="text-secondary mb-0"><?= e(Auth::user()['rol']) ?> · miembro desde <?= fecha($u['creado_en']) ?></p>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card fp-card">
      <div class="card-header fw-semibold"><i class="bi bi-person-vcard"></i> Datos personales</div>
      <div class="card-body">
        <form method="post" action="<?= url('perfil/guardar') ?>" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nombre</label>
              <input class="form-control" name="nombre" value="<?= e($u['nombre']) ?>" required minlength="2" maxlength="50">
              <div class="invalid-feedback">Obligatorio.</div></div>
            <div class="col-md-6"><label class="form-label">Apellido</label>
              <input class="form-control" name="apellido" value="<?= e($u['apellido']) ?>" maxlength="50"></div>
            <div class="col-md-6"><label class="form-label">Correo</label>
              <input type="email" class="form-control" name="correo" value="<?= e($u['correo']) ?>" required maxlength="100">
              <div class="invalid-feedback">Correo no válido.</div></div>
            <div class="col-md-6"><label class="form-label">Teléfono</label>
              <input class="form-control" name="telefono" value="<?= e($u['telefono']) ?>" pattern="\d{7,15}" inputmode="numeric">
              <div class="invalid-feedback">Solo números (7 a 15).</div></div>
            <div class="col-md-6"><label class="form-label">Fecha de nacimiento</label>
              <input type="date" class="form-control" name="fecha_nacimiento" value="<?= e($u['fecha_nacimiento']) ?>" max="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-6"><label class="form-label">Género</label>
              <select class="form-select" name="genero"><option value="">—</option>
                <?php foreach (['Masculino', 'Femenino', 'Otro'] as $g): ?><option <?= $sel('genero', $g) ?>><?= $g ?></option><?php endforeach; ?>
              </select></div>
            <?php if (Auth::es(Auth::MIEMBRO)): ?>
              <div class="col-md-4"><label class="form-label">Nivel</label>
                <select class="form-select" name="nivel"><option value="">—</option>
                  <?php foreach (['Principiante', 'Intermedio', 'Avanzado'] as $n): ?><option <?= $sel('nivel', $n) ?>><?= $n ?></option><?php endforeach; ?>
                </select></div>
              <div class="col-md-8"><label class="form-label">Objetivo</label>
                <input class="form-control" name="objetivo" value="<?= e($u['objetivo']) ?>" maxlength="255"></div>
            <?php elseif (Auth::es(Auth::INSTRUCTOR)): ?>
              <div class="col-md-6"><label class="form-label">Especialidad</label>
                <input class="form-control" name="especialidad" value="<?= e($u['especialidad']) ?>" maxlength="50"></div>
            <?php endif; ?>
          </div>
          <button class="btn btn-danger mt-4"><i class="bi bi-save"></i> Guardar cambios</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card fp-card">
      <div class="card-header fw-semibold"><i class="bi bi-shield-lock"></i> Cambiar contraseña</div>
      <div class="card-body">
        <form method="post" action="<?= url('perfil/contrasena') ?>" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Contraseña actual</label>
            <input type="password" class="form-control" name="actual" required autocomplete="current-password">
            <div class="invalid-feedback">Obligatoria.</div></div>
          <div class="mb-3"><label class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" name="nueva" id="nuevaClave" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password" data-medidor="medidorPerfil">
            <div class="progress mt-2" style="height:4px"><div class="progress-bar" id="medidorPerfil"></div></div>
            <div class="invalid-feedback">Mínimo 8 caracteres, con letras y números.</div></div>
          <div class="mb-3"><label class="form-label">Repetir nueva contraseña</label>
            <input type="password" class="form-control" name="nueva2" required data-igual-a="nuevaClave" autocomplete="new-password">
            <div class="invalid-feedback">No coincide.</div></div>
          <button class="btn btn-outline-danger"><i class="bi bi-key"></i> Actualizar contraseña</button>
        </form>
      </div>
    </div>
  </div>
</div>
