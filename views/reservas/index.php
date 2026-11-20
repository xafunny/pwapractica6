<?php
/**
 * views/reservas/index.php - Reservas e historial del MIEMBRO.
 * Variables: $proximas, $historial (reservas pasadas), $entrenos (ejercicios realizados)
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['reservas.js'];
$colorEstado = ['Reservada' => 'danger', 'Asistio' => 'success', 'Falto' => 'warning', 'Cancelada' => 'secondary'];
$textoEstado = ['Reservada' => 'Reservada', 'Asistio' => 'Asistió', 'Falto' => 'Faltó', 'Cancelada' => 'Cancelada'];
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">Mis reservas</h1>
    <p class="text-secondary mb-0">Tus próximas clases y tu historial de actividades.</p>
  </div>
  <a href="<?= url('clases/index') ?>" class="btn btn-danger"><i class="bi bi-calendar-plus"></i> Reservar otra clase</a>
</div>

<ul class="nav nav-pills fp-pills mb-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabProximas" type="button">Próximas (<?= count($proximas) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabHistorial" type="button">Historial de clases</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabEntrenos" type="button">Entrenamientos</button></li>
</ul>

<div class="tab-content">
  <!-- Próximas reservas -->
  <div class="tab-pane fade show active" id="tabProximas">
    <div class="row g-3">
      <?php foreach ($proximas as $r): $cancelada = $r['estado_clase'] === 'Cancelada'; ?>
        <div class="col-md-6 col-xl-4">
          <div class="card fp-card h-100" data-clase="<?= (int) $r['id_clase'] ?>">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <span class="badge text-bg-secondary"><?= e($r['tipo']) ?></span>
                <span class="badge text-bg-<?= $cancelada ? 'dark' : $colorEstado[$r['estado']] ?> fp-estado"><?= $cancelada ? 'Clase cancelada' : $textoEstado[$r['estado']] ?></span>
              </div>
              <h2 class="h5 mt-2 mb-1"><?= e($r['nombre']) ?></h2>
              <ul class="list-unstyled small text-secondary mb-3">
                <li><i class="bi bi-clock"></i> <?= fecha_clase($r['fecha_hora']) ?> · <?= (int) $r['duracion_min'] ?> min</li>
                <li><i class="bi bi-person-badge"></i> <?= e($r['instructor']) ?> · <i class="bi bi-geo-alt"></i> <?= e($r['instalacion']) ?></li>
              </ul>
              <?php if ($r['estado'] === 'Reservada' && !$cancelada): ?>
                <button class="btn btn-sm btn-outline-light w-100 fp-btn-reserva" data-accion="cancelar" data-quitar="1">Cancelar reserva</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$proximas): ?>
        <div class="col-12"><div class="fp-empty"><i class="bi bi-calendar-plus"></i><p>No tienes reservas próximas.</p></div></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Historial de clases pasadas -->
  <div class="tab-pane fade" id="tabHistorial">
    <div class="card fp-card"><div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead><tr><th>Clase</th><th>Fecha</th><th class="d-none d-md-table-cell">Instructor</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($historial as $r): ?>
          <tr>
            <td><?= e($r['nombre']) ?></td>
            <td><?= fecha($r['fecha_hora'], true) ?></td>
            <td class="d-none d-md-table-cell"><?= e($r['instructor']) ?></td>
            <td><span class="badge text-bg-<?= $colorEstado[$r['estado']] ?>"><?= $textoEstado[$r['estado']] ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$historial): ?><tr><td colspan="4" class="text-center text-secondary py-4">Sin clases pasadas.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <!-- Historial de ejercicios marcados como hechos -->
  <div class="tab-pane fade" id="tabEntrenos">
    <div class="card fp-card"><div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead><tr><th>Fecha</th><th>Ejercicio</th><th>Series × reps</th><th>Peso</th></tr></thead>
        <tbody>
        <?php foreach ($entrenos as $en): ?>
          <tr>
            <td><?= fecha($en['fecha']) ?></td>
            <td><?= e($en['nombre']) ?></td>
            <td><?= e($en['series']) ?> × <?= e($en['repeticiones']) ?></td>
            <td><?= $en['peso'] ? e($en['peso']) . ' kg' : '—' ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$entrenos): ?><tr><td colspan="4" class="text-center text-secondary py-4">Marca ejercicios como hechos en tu rutina para verlos aquí.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div>
