<?php
/**
 * views/progreso/index.php - Composición corporal y gráficas (Chart.js).
 * Variables: $miembro, $mediciones (ordenadas por fecha), $pm
 * Los datos se pasan a JavaScript como JSON en window.FP_MEDICIONES y
 * progreso.js dibuja la gráfica según la métrica elegida.
 */
defined('APP') or exit('Acceso directo no permitido');
$usarGraficas = true;
$scripts = ['progreso.js'];
$esMiembro = Auth::es(Auth::MIEMBRO);
$ultima  = $mediciones ? $mediciones[count($mediciones) - 1] : null;
$primera = $mediciones[0] ?? null;

/** Diferencia entre la primera y la última medición de un campo (texto con signo). */
$delta = function (string $campo) use ($primera, $ultima): string {
    if (!$primera || !$ultima || $primera === $ultima || $primera[$campo] === null || $ultima[$campo] === null) return '';
    $d = round($ultima[$campo] - $primera[$campo], 1);
    return ($d > 0 ? '+' : '') . $d;
};
$tarjetas = [['peso', 'Peso', 'kg'], ['grasa', 'Grasa', '%'], ['musculo', 'Músculo', '%'], ['imc', 'IMC', '']];
?>
<?php if (!$esMiembro): ?>
  <a href="<?= url('miembros/index') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Miembros</a>
<?php endif; ?>

<div class="fp-page-head">
  <div>
    <h1 class="fp-title"><?= $esMiembro ? 'Mi progreso' : 'Progreso de ' . e($miembro['nombre'] . ' ' . $miembro['apellido']) ?></h1>
    <p class="text-secondary mb-0"><?= $ultima ? 'Última medición: ' . fecha($ultima['fecha']) : 'Registra la primera medición para empezar.' ?></p>
  </div>
  <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalMedicion"><i class="bi bi-plus-lg"></i> Nueva medición</button>
</div>

<?php if ($ultima): ?>
  <div class="row g-3 mb-4">
    <?php foreach ($tarjetas as [$campo, $txt, $unidad]): ?>
      <div class="col-6 col-lg-3">
        <div class="card fp-card h-100"><div class="card-body">
          <div class="fp-stat-label"><?= $txt ?></div>
          <div class="fp-stat-value"><?= $ultima[$campo] !== null ? e((float) $ultima[$campo]) : '—' ?><small> <?= $unidad ?></small></div>
          <?php if ($delta($campo) !== ''): ?><small class="text-secondary"><?= $delta($campo) ?> desde el inicio</small><?php endif; ?>
        </div></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card fp-card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
      <span class="fw-semibold"><i class="bi bi-graph-up"></i> Evolución</span>
      <!-- Botones para cambiar la métrica de la gráfica (progreso.js) -->
      <div class="btn-group btn-group-sm" role="group" aria-label="Métrica">
        <?php foreach ($tarjetas as $i => [$campo, $txt, $unidad]): ?>
          <button type="button" class="btn btn-outline-danger <?= $i === 0 ? 'active' : '' ?>" data-metrica="<?= $campo ?>" data-unidad="<?= $unidad ?>"><?= $txt ?></button>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card-body"><div class="fp-chart"><canvas id="graficaProgreso" aria-label="Gráfica de progreso"></canvas></div></div>
  </div>
<?php endif; ?>

<div class="card fp-card">
  <div class="card-header fw-semibold"><i class="bi bi-clock-history"></i> Historial de mediciones</div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Fecha</th><th>Peso</th><th class="d-none d-sm-table-cell">Estatura</th><th>Grasa</th><th>Músculo</th><th>IMC</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
      <?php foreach (array_reverse($mediciones) as $m): ?>
        <tr>
          <td><?= fecha($m['fecha']) ?></td>
          <td><?= e((float) $m['peso']) ?> kg</td>
          <td class="d-none d-sm-table-cell"><?= $m['estatura'] ? e((float) $m['estatura']) . ' cm' : '—' ?></td>
          <td><?= $m['grasa'] !== null ? e((float) $m['grasa']) . '%' : '—' ?></td>
          <td><?= $m['musculo'] !== null ? e((float) $m['musculo']) . '%' : '—' ?></td>
          <td><?= $m['imc'] !== null ? e((float) $m['imc']) : '—' ?></td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalMedicion" data-editar='<?= e(json_encode($m)) ?>' aria-label="Editar"><i class="bi bi-pencil"></i></button>
            <form method="post" action="<?= url('progreso/eliminar') ?>" class="d-inline" data-confirmar="¿Eliminar la medición del <?= fecha($m['fecha']) ?>?">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <?php if (!$esMiembro): ?><input type="hidden" name="miembro" value="<?= (int) $miembro['id'] ?>"><?php endif; ?>
              <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$mediciones): ?><tr><td colspan="7" class="text-center text-secondary py-4">Aún no hay mediciones.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ============== MODAL NUEVA / EDITAR MEDICIÓN ============== -->
<div class="modal fade" id="modalMedicion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('progreso/guardar') ?>" novalidate
          data-titulo-nuevo="Nueva medición" data-titulo-editar="Editar medición">
      <div class="modal-header">
        <h2 class="modal-title h5">Nueva medición</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <?php if (!$esMiembro): ?><input type="hidden" name="miembro" value="<?= (int) $miembro['id'] ?>" data-fijo><?php endif; ?>
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label">Fecha</label>
            <input type="date" class="form-control" name="fecha" required max="<?= date('Y-m-d') ?>" data-hoy>
            <div class="invalid-feedback">Fecha no válida.</div>
          </div>
          <div class="col-6">
            <label class="form-label">Peso (kg)</label>
            <input type="number" class="form-control" name="peso" id="inputPeso" step="0.1" min="20" max="400" required>
            <div class="invalid-feedback">Entre 20 y 400.</div>
          </div>
          <div class="col-6">
            <label class="form-label">Estatura (cm)</label>
            <!-- Se propone la última estatura registrada -->
            <input type="number" class="form-control" name="estatura" id="inputEstatura" step="0.1" min="100" max="250"
                   data-defecto="<?= $ultima ? e((float) $ultima['estatura']) : '' ?>">
          </div>
          <div class="col-6">
            <label class="form-label">IMC (automático)</label>
            <input class="form-control" id="imcPrevio" readonly tabindex="-1">
          </div>
          <div class="col-6"><label class="form-label">% Grasa</label><input type="number" class="form-control" name="grasa" step="0.1" min="2" max="70"></div>
          <div class="col-6"><label class="form-label">% Músculo</label><input type="number" class="form-control" name="musculo" step="0.1" min="10" max="70"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-danger"><i class="bi bi-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Datos para la gráfica (progreso.js) -->
<script>
  window.FP_MEDICIONES = <?= json_encode(array_map(fn($m) => [
      'fecha'   => fecha($m['fecha']),
      'peso'    => (float) $m['peso'],
      'grasa'   => $m['grasa'] !== null ? (float) $m['grasa'] : null,
      'musculo' => $m['musculo'] !== null ? (float) $m['musculo'] : null,
      'imc'     => $m['imc'] !== null ? (float) $m['imc'] : null,
  ], $mediciones)) ?>;
</script>
