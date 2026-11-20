<?php
/**
 * views/inicio/miembro.php - Panel del MIEMBRO.
 * Variables: $hoy, $rutinaHoy, $hechos, $total, $reservas, $asistencias, $ultimaMed
 */
defined('APP') or exit('Acceso directo no permitido');
$pct = $total ? round($hechos / $total * 100) : 0;
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">¡Vamos, <?= e(Auth::user()['nombre']) ?>!</h1>
    <p class="text-secondary mb-0">Hoy es <?= dia_bonito($hoy) ?>. Esto es lo que tienes pendiente.</p>
  </div>
  <a href="<?= url('clases/index') ?>" class="btn btn-danger"><i class="bi bi-calendar-plus"></i> Reservar clase</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card fp-card h-100"><div class="card-body">
      <div class="fp-stat-label mb-2">Progreso de la semana</div>
      <div class="d-flex align-items-end gap-2">
        <span class="fp-stat-value"><?= $pct ?>%</span>
        <span class="text-secondary mb-1"><?= $hechos ?>/<?= $total ?> ejercicios</span>
      </div>
      <div class="progress fp-progress mt-2"><div class="progress-bar bg-danger" style="width:<?= $pct ?>%"></div></div>
    </div></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="card fp-card fp-stat h-100"><div class="card-body">
      <i class="bi bi-trophy fp-stat-icon"></i>
      <div class="fp-stat-value" data-contador="<?= $asistencias ?>"><?= $asistencias ?></div>
      <div class="fp-stat-label">Clases asistidas</div>
    </div></div>
  </div>
  <div class="col-6 col-md-4">
    <a href="<?= url('progreso/index') ?>" class="card fp-card fp-stat h-100 text-decoration-none"><div class="card-body">
      <i class="bi bi-speedometer fp-stat-icon"></i>
      <div class="fp-stat-value"><?= $ultimaMed ? e((float) $ultimaMed['peso']) . '<small> kg</small>' : '—' ?></div>
      <div class="fp-stat-label">Último peso<?= $ultimaMed ? ' · ' . fecha($ultimaMed['fecha']) : '' ?></div>
    </div></a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card fp-card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-lightning-charge"></i> Rutina de hoy</span>
        <a href="<?= url('rutina/index') ?>" class="small link-danger">Ver semana</a>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($rutinaHoy as $ej): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?= $ej['hecho'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-secondary"></i>' ?>
              <?= e($ej['nombre']) ?></span>
            <small class="text-secondary"><?= e($ej['series']) ?>×<?= e($ej['repeticiones']) ?><?= $ej['peso'] ? ' · ' . e($ej['peso']) . ' kg' : '' ?></small>
          </li>
        <?php endforeach; ?>
        <?php if (!$rutinaHoy): ?>
          <li class="list-group-item text-secondary text-center py-4">Hoy es día de descanso 😴</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card fp-card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-calendar-check"></i> Próximas reservas</span>
        <a href="<?= url('reservas/index') ?>" class="small link-danger">Ver todas</a>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($reservas as $r): ?>
          <li class="list-group-item">
            <div class="d-flex justify-content-between">
              <span class="fw-semibold"><?= e($r['nombre']) ?></span>
              <span class="badge <?= $r['estado'] === 'Cancelada' || $r['estado_clase'] === 'Cancelada' ? 'text-bg-secondary' : 'text-bg-danger' ?>">
                <?= $r['estado_clase'] === 'Cancelada' ? 'Clase cancelada' : e($r['estado']) ?></span>
            </div>
            <small class="text-secondary"><?= fecha_clase($r['fecha_hora']) ?> · <?= e($r['instalacion']) ?></small>
          </li>
        <?php endforeach; ?>
        <?php if (!$reservas): ?>
          <li class="list-group-item text-secondary text-center py-4">No tienes reservas próximas.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>
