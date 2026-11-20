<?php
/**
 * views/dieta/index.php - Plan de alimentación semanal.
 * El miembro solo consulta; instructor/admin pueden agregar, editar y eliminar.
 * Variables: $miembro, $plan, $dia, $editable, $pm
 */
defined('APP') or exit('Acceso directo no permitido');
$esMiembro = Auth::es(Auth::MIEMBRO);
$iconos = ['Desayuno' => 'cup-hot', 'Almuerzo' => 'egg-fried', 'Merienda' => 'apple', 'Cena' => 'moon'];
$scripts = ['dias.js']; // preselecciona en el modal el día de la pestaña activa
?>
<?php if (!$esMiembro): ?>
  <a href="<?= url('miembros/index') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Miembros</a>
<?php endif; ?>

<div class="fp-page-head">
  <div>
    <h1 class="fp-title"><?= $esMiembro ? 'Mi dieta' : 'Dieta de ' . e($miembro['nombre'] . ' ' . $miembro['apellido']) ?></h1>
    <p class="text-secondary mb-0"><?= $editable ? 'Arma el plan de alimentación del miembro.' : 'Plan de alimentación preparado por tu instructor.' ?></p>
  </div>
  <?php if ($editable): ?>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalComida"><i class="bi bi-plus-lg"></i> Agregar comida</button>
  <?php endif; ?>
</div>

<ul class="nav nav-pills fp-pills fp-dias mb-3" role="tablist">
  <?php foreach (DIAS as $d): ?>
    <li class="nav-item"><button class="nav-link <?= $d === $dia ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#dia-<?= $d ?>" type="button" data-dia="<?= $d ?>">
      <?= mb_substr(dia_bonito($d), 0, 3) ?><span class="d-none d-md-inline"><?= mb_substr(dia_bonito($d), 3) ?></span></button></li>
  <?php endforeach; ?>
</ul>

<div class="tab-content">
  <?php foreach (DIAS as $d):
      $kcal = 0;
      foreach ($plan[$d] as $lista) foreach ($lista as $c) $kcal += (int) $c['calorias'];
  ?>
    <div class="tab-pane fade <?= $d === $dia ? 'show active' : '' ?>" id="dia-<?= $d ?>">
      <p class="text-secondary small">Total estimado del día: <strong class="text-light"><?= $kcal ?> kcal</strong></p>
      <div class="row g-3">
        <?php foreach (MOMENTOS as $mo): ?>
          <div class="col-md-6 col-xl-3">
            <div class="card fp-card h-100">
              <div class="card-header fw-semibold"><i class="bi bi-<?= $iconos[$mo] ?> text-danger"></i> <?= $mo ?></div>
              <ul class="list-group list-group-flush">
                <?php foreach ($plan[$d][$mo] as $c): ?>
                  <li class="list-group-item">
                    <div><?= e($c['descripcion']) ?></div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                      <small class="text-secondary"><?= $c['calorias'] ? (int) $c['calorias'] . ' kcal' : '' ?></small>
                      <?php if ($editable): ?>
                        <span class="d-flex gap-1">
                          <button class="btn btn-sm btn-link text-light p-0" data-bs-toggle="modal" data-bs-target="#modalComida" data-editar='<?= e(json_encode($c)) ?>' aria-label="Editar"><i class="bi bi-pencil"></i></button>
                          <form method="post" action="<?= url('dieta/eliminar') ?>" data-confirmar="¿Eliminar esta comida?">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><input type="hidden" name="miembro" value="<?= (int) $miembro['id'] ?>">
                            <button class="btn btn-sm btn-link text-danger p-0" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                          </form>
                        </span>
                      <?php endif; ?>
                    </div>
                  </li>
                <?php endforeach; ?>
                <?php if (!$plan[$d][$mo]): ?><li class="list-group-item text-secondary small">Sin asignar</li><?php endif; ?>
              </ul>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($editable): ?>
<!-- ============== MODAL AGREGAR / EDITAR COMIDA ============== -->
<div class="modal fade" id="modalComida" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('dieta/guardar') ?>" novalidate
          data-titulo-nuevo="Nueva comida" data-titulo-editar="Editar comida">
      <div class="modal-header">
        <h2 class="modal-title h5">Nueva comida</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <input type="hidden" name="miembro" value="<?= (int) $miembro['id'] ?>" data-fijo>
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label">Día</label>
            <select class="form-select" name="dia" id="selectDia" required>
              <?php foreach (DIAS as $d): ?><option value="<?= $d ?>"><?= dia_bonito($d) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Momento</label>
            <select class="form-select" name="momento" required>
              <?php foreach (MOMENTOS as $mo): ?><option><?= $mo ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="2" required maxlength="255"></textarea>
            <div class="invalid-feedback">Obligatorio.</div>
          </div>
          <div class="col-6">
            <label class="form-label">Calorías (kcal)</label>
            <input type="number" class="form-control" name="calorias" min="0" max="5000">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-danger"><i class="bi bi-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
