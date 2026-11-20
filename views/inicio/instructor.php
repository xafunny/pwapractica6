<?php
/**
 * views/inicio/instructor.php - Panel del INSTRUCTOR.
 * Variables: $proximas (clases), $totalProx, $miembros
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">Hola, <?= e(Auth::user()['nombre']) ?></h1>
    <p class="text-secondary mb-0">Gestiona tus clases y da seguimiento a tus miembros.</p>
  </div>
  <a href="<?= url('clases/index') ?>" class="btn btn-danger"><i class="bi bi-plus-lg"></i> Nueva clase</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <a href="<?= url('clases/index') ?>" class="card fp-card fp-stat h-100 text-decoration-none"><div class="card-body">
      <i class="bi bi-calendar-event fp-stat-icon"></i>
      <div class="fp-stat-value" data-contador="<?= $totalProx ?>"><?= $totalProx ?></div>
      <div class="fp-stat-label">Clases próximas</div>
    </div></a>
  </div>
  <div class="col-6 col-md-3">
    <a href="<?= url('miembros/index') ?>" class="card fp-card fp-stat h-100 text-decoration-none"><div class="card-body">
      <i class="bi bi-people fp-stat-icon"></i>
      <div class="fp-stat-value" data-contador="<?= $miembros ?>"><?= $miembros ?></div>
      <div class="fp-stat-label">Miembros</div>
    </div></a>
  </div>
</div>

<h2 class="h5 fw-bold mb-3">Mis próximas clases</h2>
<div class="row g-3">
  <?php foreach ($proximas as $c): $pct = $c['cupo'] ? round($c['ocupados'] / $c['cupo'] * 100) : 0; ?>
    <div class="col-md-6 col-xl-4">
      <div class="card fp-card h-100 <?= $c['estado'] === 'Cancelada' ? 'opacity-50' : '' ?>">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <span class="badge text-bg-secondary"><?= e($c['tipo']) ?></span>
            <?php if ($c['estado'] === 'Cancelada'): ?><span class="badge text-bg-danger">Cancelada</span><?php endif; ?>
          </div>
          <h3 class="h5 mt-2 mb-1"><?= e($c['nombre']) ?></h3>
          <p class="small text-secondary mb-2"><i class="bi bi-clock"></i> <?= fecha_clase($c['fecha_hora']) ?> · <i class="bi bi-geo-alt"></i> <?= e($c['instalacion']) ?></p>
          <div class="progress fp-progress" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar bg-danger" style="width:<?= $pct ?>%"></div>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-secondary"><?= (int) $c['ocupados'] ?>/<?= (int) $c['cupo'] ?> inscritos</small>
            <a href="<?= url('clases/inscritos', ['id' => $c['id']]) ?>" class="btn btn-sm btn-outline-light">Ver inscritos</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$proximas): ?>
    <div class="col-12"><div class="fp-empty"><i class="bi bi-calendar-x"></i><p>No tienes clases próximas.</p></div></div>
  <?php endif; ?>
</div>
