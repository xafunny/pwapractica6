<?php
/**
 * views/rutina/index.php - Rutina semanal por días.
 *   - Pestañas Lunes..Domingo (cambian sin recargar, con Bootstrap).
 *   - Cada ejercicio se marca como hecho con un clic (fetch -> rutina/marcar).
 *   - Barra de progreso semanal que se actualiza en vivo (rutina.js).
 * Variables: $miembro, $rutina (por día), $dia (pestaña activa), $hechos, $total, $pm (params de miembro)
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['dias.js', 'rutina.js'];
$pct = $total ? round($hechos / $total * 100) : 0;
$esMiembro = Auth::es(Auth::MIEMBRO);
?>
<?php if (!$esMiembro): ?>
  <a href="<?= url('miembros/index') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Miembros</a>
<?php endif; ?>

<div class="fp-page-head">
  <div>
    <h1 class="fp-title"><?= $esMiembro ? 'Mi rutina' : 'Rutina de ' . e($miembro['nombre'] . ' ' . $miembro['apellido']) ?></h1>
    <p class="text-secondary mb-0">La semana se reinicia automáticamente cada lunes.</p>
  </div>
  <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEjercicio"><i class="bi bi-plus-lg"></i> Agregar ejercicio</button>
</div>

<!-- Progreso semanal -->
<div class="card fp-card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between mb-2">
      <span class="fw-semibold">Progreso de la semana</span>
      <span><span id="progresoTexto"><?= $hechos ?>/<?= $total ?></span> ejercicios · <strong id="progresoPct"><?= $pct ?>%</strong></span>
    </div>
    <div class="progress fp-progress fp-progress-lg"><div class="progress-bar bg-danger" id="progresoBarra" style="width:<?= $pct ?>%"></div></div>
  </div>
</div>

<!-- Pestañas por día -->
<ul class="nav nav-pills fp-pills fp-dias mb-3" role="tablist">
  <?php foreach (DIAS as $d): $n = count($rutina[$d]); ?>
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $d === $dia ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#dia-<?= $d ?>" type="button" role="tab" data-dia="<?= $d ?>">
        <?= mb_substr(dia_bonito($d), 0, 3) ?><span class="d-none d-md-inline"><?= mb_substr(dia_bonito($d), 3) ?></span>
        <?php if ($n): ?><span class="badge rounded-pill text-bg-dark ms-1"><?= $n ?></span><?php endif; ?>
        <?php if ($d === dia_actual()): ?><span class="fp-hoy" title="Hoy"></span><?php endif; ?>
      </button>
    </li>
  <?php endforeach; ?>
</ul>

<div class="tab-content">
  <?php foreach (DIAS as $d): ?>
    <div class="tab-pane fade <?= $d === $dia ? 'show active' : '' ?>" id="dia-<?= $d ?>" role="tabpanel">
      <div class="row g-3">
        <?php foreach ($rutina[$d] as $ej): ?>
          <div class="col-md-6 col-xl-4">
            <div class="card fp-card fp-ejercicio h-100 <?= $ej['hecho'] ? 'fp-hecho' : '' ?>">
              <div class="card-body">
                <div class="d-flex gap-3">
                  <!-- Botón para marcar como hecho (rutina.js) -->
                  <button class="fp-check" data-marcar="<?= (int) $ej['id'] ?>" aria-pressed="<?= $ej['hecho'] ? 'true' : 'false' ?>" aria-label="Marcar <?= e($ej['nombre']) ?> como hecho">
                    <i class="bi bi-check-lg"></i>
                  </button>
                  <div class="flex-grow-1">
                    <h2 class="h6 fw-bold mb-2 fp-ej-nombre"><?= e($ej['nombre']) ?></h2>
                    <div class="d-flex flex-wrap gap-1">
                      <?php if ($ej['series']): ?><span class="fp-chip"><?= e($ej['series']) ?> series</span><?php endif; ?>
                      <?php if ($ej['repeticiones']): ?><span class="fp-chip"><?= e($ej['repeticiones']) ?> reps</span><?php endif; ?>
                      <?php if ($ej['peso']): ?><span class="fp-chip fp-chip-acc"><?= e($ej['peso']) ?> kg</span><?php endif; ?>
                      <?php if ($ej['descanso']): ?><span class="fp-chip"><i class="bi bi-hourglass-split"></i> <?= e($ej['descanso']) ?></span><?php endif; ?>
                    </div>
                    <?php if ($ej['notas']): ?><p class="small text-secondary mt-2 mb-0"><?= e($ej['notas']) ?></p><?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalEjercicio" data-editar='<?= e(json_encode($ej)) ?>'><i class="bi bi-pencil"></i></button>
                <form method="post" action="<?= url('rutina/eliminar') ?>" data-confirmar="¿Quitar «<?= e($ej['nombre']) ?>» de la rutina?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $ej['id'] ?>">
                  <?php if (!$esMiembro): ?><input type="hidden" name="miembro" value="<?= (int) $miembro['id'] ?>"><?php endif; ?>
                  <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$rutina[$d]): ?>
          <div class="col-12"><div class="fp-empty"><i class="bi bi-moon-stars"></i><p>Sin ejercicios para <?= dia_bonito($d) ?>. ¡Día de descanso o agrega uno!</p></div></div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ============== MODAL AGREGAR / EDITAR EJERCICIO ============== -->
<div class="modal fade" id="modalEjercicio" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('rutina/guardar') ?>" novalidate
          data-titulo-nuevo="Nuevo ejercicio" data-titulo-editar="Editar ejercicio">
      <div class="modal-header">
        <h2 class="modal-title h5">Nuevo ejercicio</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <?php if (!$esMiembro): ?><input type="hidden" name="miembro" value="<?= (int) $miembro['id'] ?>" data-fijo><?php endif; ?>
        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label">Día</label>
            <!-- rutina.js preselecciona el día de la pestaña activa -->
            <select class="form-select" name="dia" id="selectDia" required>
              <?php foreach (DIAS as $d): ?><option value="<?= $d ?>"><?= dia_bonito($d) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-7">
            <label class="form-label">Ejercicio</label>
            <input class="form-control" name="nombre" required maxlength="60" list="listaEjercicios" placeholder="Ej.: Press banca">
            <datalist id="listaEjercicios">
              <option>Press banca</option><option>Press militar</option><option>Sentadilla</option><option>Peso muerto</option>
              <option>Dominadas</option><option>Remo con barra</option><option>Curl de bíceps</option><option>Extensión de tríceps</option>
              <option>Prensa</option><option>Zancadas</option><option>Plancha</option><option>Hip thrust</option>
            </datalist>
            <div class="invalid-feedback">Obligatorio.</div>
          </div>
          <div class="col-6 col-md-3"><label class="form-label">Series</label><input class="form-control" name="series" maxlength="10" placeholder="4"></div>
          <div class="col-6 col-md-3"><label class="form-label">Reps</label><input class="form-control" name="repeticiones" maxlength="15" placeholder="10-12"></div>
          <div class="col-6 col-md-3"><label class="form-label">Peso (kg)</label><input class="form-control" name="peso" maxlength="10" inputmode="decimal"></div>
          <div class="col-6 col-md-3"><label class="form-label">Descanso</label><input class="form-control" name="descanso" maxlength="10" placeholder="90s"></div>
          <div class="col-12"><label class="form-label">Notas</label><input class="form-control" name="notas" maxlength="255" placeholder="Técnica, tempo, etc."></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-danger"><i class="bi bi-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Datos que necesita rutina.js -->
<script>
  window.FP_RUTINA = { urlMarcar: <?= json_encode(url('rutina/marcar')) ?>, miembro: <?= $esMiembro ? 'null' : (int) $miembro['id'] ?> };
</script>
