<?php
/**
 * views/inicio/admin.php - Panel del ADMINISTRADOR con métricas.
 * Variables: $miembros, $instructores, $instalaciones, $clasesProx, $reservasAct, $proximas
 */
defined('APP') or exit('Acceso directo no permitido');

// Tarjetas de métricas: [valor, etiqueta, icono, enlace]
$metricas = [
    [$miembros, 'Miembros', 'people', url('usuarios/index', ['rol' => Auth::MIEMBRO])],
    [$instructores, 'Instructores', 'person-badge', url('usuarios/index', ['rol' => Auth::INSTRUCTOR])],
    [$instalaciones, 'Instalaciones', 'building', url('instalaciones/index')],
    [$clasesProx, 'Clases próximas', 'calendar-event', url('clases/index')],
    [$reservasAct, 'Reservas activas', 'journal-check', url('reservas/index', ['estado' => 'Reservada'])],
];
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">Panel de administración</h1>
    <p class="text-secondary mb-0">Resumen general del gimnasio · <?= fecha(date('Y-m-d')) ?></p>
  </div>
  <a href="<?= url('clases/index') ?>" class="btn btn-danger"><i class="bi bi-plus-lg"></i> Programar clase</a>
</div>

<div class="row g-3 mb-4">
  <?php foreach ($metricas as [$valor, $etiqueta, $icono, $link]): ?>
    <div class="col-6 col-md-4 col-xl">
      <a href="<?= $link ?>" class="card fp-card fp-stat h-100 text-decoration-none">
        <div class="card-body">
          <i class="bi bi-<?= $icono ?> fp-stat-icon"></i>
          <div class="fp-stat-value" data-contador="<?= (int) $valor ?>"><?= (int) $valor ?></div>
          <div class="fp-stat-label"><?= $etiqueta ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="card fp-card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="fw-semibold"><i class="bi bi-clock"></i> Próximas clases</span>
    <a href="<?= url('clases/index') ?>" class="small link-danger">Ver todas</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Clase</th><th>Fecha</th><th>Instructor</th><th>Sala</th><th class="text-center">Cupos</th></tr></thead>
      <tbody>
        <?php foreach ($proximas as $c): ?>
          <tr>
            <td><span class="fw-semibold"><?= e($c['nombre']) ?></span><br><span class="badge text-bg-secondary"><?= e($c['tipo']) ?></span></td>
            <td><?= fecha_clase($c['fecha_hora']) ?></td>
            <td><?= e($c['instructor']) ?></td>
            <td><?= e($c['instalacion']) ?></td>
            <td class="text-center"><?= (int) $c['ocupados'] ?>/<?= (int) $c['cupo'] ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$proximas): ?>
          <tr><td colspan="5" class="text-center text-secondary py-4">No hay clases programadas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
