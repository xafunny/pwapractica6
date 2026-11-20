<?php
/**
 * views/clases/disponibles.php - Catálogo de clases para el MIEMBRO.
 * Reservar / cancelar se hace sin recargar la página con fetch()
 * (assets/js/reservas.js), que actualiza cupos y botones.
 * Variables: $clases, $tipos, $tipo (filtro)
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['reservas.js'];
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">Reservar clases</h1>
    <p class="text-secondary mb-0">Elige una clase y asegura tu cupo.</p>
  </div>
  <input type="search" class="form-control fp-search" id="buscarClase" placeholder="Buscar clase o instructor…" aria-label="Buscar clase">
</div>

<!-- Filtro por tipo -->
<div class="d-flex flex-wrap gap-2 mb-4">
  <a href="<?= url('clases/index') ?>" class="btn btn-sm <?= $tipo === '' ? 'btn-danger' : 'btn-outline-secondary' ?>">Todas</a>
  <?php foreach ($tipos as $t): ?>
    <a href="<?= url('clases/index', ['tipo' => $t]) ?>" class="btn btn-sm <?= $tipo === $t ? 'btn-danger' : 'btn-outline-secondary' ?>"><?= e($t) ?></a>
  <?php endforeach; ?>
</div>

<div class="row g-3" id="listaClases">
  <?php foreach ($clases as $c):
      $reservada = $c['mi_reserva'] === 'Reservada';
      $llena = (int) $c['ocupados'] >= (int) $c['cupo'];
      $pct = $c['cupo'] ? min(100, round($c['ocupados'] / $c['cupo'] * 100)) : 0;
  ?>
    <div class="col-md-6 col-xl-4" data-buscar="<?= e(mb_strtolower($c['nombre'] . ' ' . $c['instructor'] . ' ' . $c['tipo'])) ?>">
      <div class="card fp-card fp-clase h-100 <?= $reservada ? 'fp-clase-reservada' : '' ?>" data-clase="<?= (int) $c['id'] ?>">
        <div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start">
            <span class="badge text-bg-secondary"><?= e($c['tipo']) ?></span>
            <span class="badge text-bg-success fp-badge-reservada <?= $reservada ? '' : 'd-none' ?>"><i class="bi bi-check2"></i> Reservada</span>
          </div>
          <h2 class="h5 mt-2 mb-1"><?= e($c['nombre']) ?></h2>
          <p class="small text-secondary mb-2"><?= e($c['descripcion']) ?></p>
          <ul class="list-unstyled small mb-3">
            <li><i class="bi bi-clock text-danger"></i> <?= fecha_clase($c['fecha_hora']) ?> · <?= (int) $c['duracion_min'] ?> min</li>
            <li><i class="bi bi-person-badge text-danger"></i> <?= e($c['instructor']) ?></li>
            <li><i class="bi bi-geo-alt text-danger"></i> <?= e($c['instalacion']) ?></li>
          </ul>
          <div class="mt-auto">
            <div class="d-flex justify-content-between small mb-1">
              <span>Cupos</span><span class="fp-cupos"><?= (int) $c['ocupados'] ?>/<?= (int) $c['cupo'] ?></span>
            </div>
            <div class="progress fp-progress mb-3"><div class="progress-bar bg-danger fp-cupos-barra" style="width:<?= $pct ?>%"></div></div>
            <button class="btn w-100 fp-btn-reserva <?= $reservada ? 'btn-outline-light' : 'btn-danger' ?>"
                    data-accion="<?= $reservada ? 'cancelar' : 'crear' ?>" <?= !$reservada && $llena ? 'disabled' : '' ?>>
              <?= $reservada ? 'Cancelar reserva' : ($llena ? 'Clase llena' : 'Reservar') ?>
            </button>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$clases): ?>
    <div class="col-12"><div class="fp-empty"><i class="bi bi-calendar-x"></i><p>No hay clases disponibles por ahora.</p></div></div>
  <?php endif; ?>
</div>
<p class="text-secondary text-center d-none mt-4" id="sinResultados">Ninguna clase coincide con la búsqueda.</p>
