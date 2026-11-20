<?php
/**
 * views/clases/inscritos.php - Miembros inscritos en una clase y toma
 * de asistencia (ADMINISTRADOR / INSTRUCTOR dueño de la clase).
 * Los botones de asistencia usan fetch() (assets/js/asistencia.js).
 * Variables: $clase, $inscritos
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['asistencia.js'];
$yaPaso = strtotime($clase['fecha_hora']) <= time();
?>
<a href="<?= url('clases/index') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver a clases</a>

<div class="card fp-card mb-4">
  <div class="card-body d-flex flex-wrap justify-content-between gap-3">
    <div>
      <span class="badge text-bg-secondary"><?= e($clase['tipo']) ?></span>
      <h1 class="fp-title mt-1 mb-1"><?= e($clase['nombre']) ?></h1>
      <p class="text-secondary mb-0"><i class="bi bi-clock"></i> <?= fecha_clase($clase['fecha_hora']) ?> · <?= (int) $clase['duracion_min'] ?> min
        · <i class="bi bi-geo-alt"></i> <?= e($clase['instalacion']) ?> · <i class="bi bi-person-badge"></i> <?= e($clase['instructor']) ?></p>
    </div>
    <div class="text-end">
      <div class="fp-stat-value"><?= (int) $clase['ocupados'] ?>/<?= (int) $clase['cupo'] ?></div>
      <div class="fp-stat-label">Inscritos</div>
    </div>
  </div>
</div>

<?php if (!$yaPaso): ?>
  <div class="alert alert-info small"><i class="bi bi-info-circle"></i> La asistencia se puede marcar desde el inicio de la clase.</div>
<?php endif; ?>

<div class="card fp-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Miembro</th><th class="d-none d-md-table-cell">Contacto</th><th>Reservó</th><th class="text-end">Asistencia</th></tr></thead>
      <tbody>
      <?php foreach ($inscritos as $r): ?>
        <tr>
          <td><span class="fw-semibold"><?= e($r['nombre'] . ' ' . $r['apellido']) ?></span></td>
          <td class="d-none d-md-table-cell small text-secondary"><?= e($r['correo']) ?><br><?= e($r['telefono']) ?></td>
          <td class="small"><?= fecha($r['fecha_reserva'], true) ?></td>
          <td class="text-end">
            <!-- Grupo de botones: el seleccionado queda marcado con la clase "active" -->
            <div class="btn-group btn-group-sm" role="group" data-asistencia="<?= (int) $r['id'] ?>">
              <button class="btn btn-outline-success <?= $r['estado'] === 'Asistio' ? 'active' : '' ?>" data-estado="Asistio" <?= $yaPaso ? '' : 'disabled' ?>><i class="bi bi-check-lg"></i> Asistió</button>
              <button class="btn btn-outline-danger <?= $r['estado'] === 'Falto' ? 'active' : '' ?>" data-estado="Falto" <?= $yaPaso ? '' : 'disabled' ?>><i class="bi bi-x-lg"></i> Faltó</button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$inscritos): ?>
        <tr><td colspan="4" class="text-center text-secondary py-4">Todavía no hay miembros inscritos.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
