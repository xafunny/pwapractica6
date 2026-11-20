<?php
/**
 * views/reservas/admin.php - Todas las reservas (ADMINISTRADOR).
 * Variables: $reservas, $estado (filtro)
 */
defined('APP') or exit('Acceso directo no permitido');
$estados = ['' => 'Todas', 'Reservada' => 'Reservadas', 'Asistio' => 'Asistió', 'Falto' => 'Faltó', 'Cancelada' => 'Canceladas'];
$color = ['Reservada' => 'danger', 'Asistio' => 'success', 'Falto' => 'warning', 'Cancelada' => 'secondary'];
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">Reservas</h1>
    <p class="text-secondary mb-0">Supervisión de todas las reservas del gimnasio (últimas 300).</p>
  </div>
  <input type="search" class="form-control fp-search" data-filtrar-tabla="tablaReservas" placeholder="Filtrar…" aria-label="Filtrar tabla">
</div>

<ul class="nav nav-pills fp-pills mb-3">
  <?php foreach ($estados as $k => $txt): ?>
    <li class="nav-item"><a class="nav-link <?= $estado === $k ? 'active' : '' ?>" href="<?= url('reservas/index', array_filter(['estado' => $k])) ?>"><?= $txt ?></a></li>
  <?php endforeach; ?>
</ul>

<div class="card fp-card"><div class="table-responsive">
  <table class="table table-hover align-middle mb-0" id="tablaReservas">
    <thead><tr><th>Miembro</th><th>Clase</th><th>Fecha clase</th><th class="d-none d-md-table-cell">Instructor</th><th class="d-none d-lg-table-cell">Reservó</th><th>Estado</th></tr></thead>
    <tbody>
    <?php foreach ($reservas as $r): ?>
      <tr>
        <td><?= e($r['miembro']) ?></td>
        <td><?= e($r['clase']) ?></td>
        <td class="text-nowrap"><?= fecha($r['fecha_hora'], true) ?></td>
        <td class="d-none d-md-table-cell"><?= e($r['instructor']) ?></td>
        <td class="d-none d-lg-table-cell small"><?= fecha($r['fecha_reserva'], true) ?></td>
        <td><span class="badge text-bg-<?= $color[$r['estado']] ?>"><?= e($estados[$r['estado']] ?? $r['estado']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$reservas): ?><tr><td colspan="6" class="text-center text-secondary py-4">No hay reservas.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
