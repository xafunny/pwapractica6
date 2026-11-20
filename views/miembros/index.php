<?php
/**
 * views/miembros/index.php - Listado de miembros para seguimiento
 * (INSTRUCTOR / ADMINISTRADOR) con accesos a rutina, dieta y progreso.
 * Variables: $miembros, $buscar
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">Miembros</h1>
    <p class="text-secondary mb-0">Arma rutinas y dietas, y revisa el progreso de cada miembro.</p>
  </div>
  <form class="d-flex gap-2" method="get">
    <input type="hidden" name="r" value="miembros/index">
    <input type="search" class="form-control" name="q" value="<?= e($buscar) ?>" placeholder="Buscar miembro…" aria-label="Buscar miembro">
    <button class="btn btn-outline-light"><i class="bi bi-search"></i></button>
  </form>
</div>

<div class="row g-3">
  <?php foreach ($miembros as $m): ?>
    <div class="col-md-6 col-xl-4">
      <div class="card fp-card h-100 <?= $m['activo'] ? '' : 'opacity-50' ?>">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <span class="fp-avatar fp-avatar-lg"><?= e(iniciales($m['nombre'], $m['apellido'])) ?></span>
            <div class="lh-sm">
              <h2 class="h6 fw-bold mb-1"><?= e($m['nombre'] . ' ' . $m['apellido']) ?></h2>
              <small class="text-secondary"><?= e($m['correo']) ?></small>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-1 mb-2">
            <?php if ($m['nivel']): ?><span class="fp-chip"><?= e($m['nivel']) ?></span><?php endif; ?>
            <?php if ($m['fecha_nacimiento']): ?><span class="fp-chip"><?= edad($m['fecha_nacimiento']) ?> años</span><?php endif; ?>
            <span class="fp-chip"><?= (int) $m['total_ejercicios'] ?> ejercicios</span>
            <?php if (!$m['activo']): ?><span class="badge text-bg-secondary">Inactivo</span><?php endif; ?>
          </div>
          <?php if ($m['objetivo']): ?><p class="small mb-1"><i class="bi bi-bullseye text-danger"></i> <?= e($m['objetivo']) ?></p><?php endif; ?>
          <p class="small text-secondary mb-0"><i class="bi bi-clipboard-data"></i> Última medición: <?= $m['ultima_medicion'] ? fecha($m['ultima_medicion']) : 'sin registros' ?></p>
        </div>
        <div class="card-footer d-flex gap-2">
          <a class="btn btn-sm btn-danger flex-fill" href="<?= url('rutina/index', ['miembro' => $m['id']]) ?>"><i class="bi bi-lightning-charge"></i> Rutina</a>
          <a class="btn btn-sm btn-outline-light flex-fill" href="<?= url('dieta/index', ['miembro' => $m['id']]) ?>"><i class="bi bi-egg-fried"></i> Dieta</a>
          <a class="btn btn-sm btn-outline-light flex-fill" href="<?= url('progreso/index', ['miembro' => $m['id']]) ?>"><i class="bi bi-graph-up"></i> Progreso</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$miembros): ?>
    <div class="col-12"><div class="fp-empty"><i class="bi bi-people"></i><p>No se encontraron miembros.</p></div></div>
  <?php endif; ?>
</div>
