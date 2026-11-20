<?php
/**
 * views/clases/index.php - Gestión de clases (ADMINISTRADOR e INSTRUCTOR).
 * Variables: $clases, $periodo, $instructores, $instalaciones
 * El instructor solo ve y crea SUS clases (no elige instructor).
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['clases.js'];
$esAdmin = Auth::es(Auth::ADMIN);
$periodos = ['proximas' => 'Próximas', 'pasadas' => 'Pasadas', 'todas' => 'Todas'];
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title"><?= $esAdmin ? 'Clases' : 'Mis clases' ?></h1>
    <p class="text-secondary mb-0">Programa clases, controla cupos y toma asistencia.</p>
  </div>
  <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalClase"><i class="bi bi-plus-lg"></i> Nueva clase</button>
</div>

<ul class="nav nav-pills fp-pills mb-3">
  <?php foreach ($periodos as $k => $txt): ?>
    <li class="nav-item"><a class="nav-link <?= $periodo === $k ? 'active' : '' ?>" href="<?= url('clases/index', ['periodo' => $k]) ?>"><?= $txt ?></a></li>
  <?php endforeach; ?>
</ul>

<div class="card fp-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Clase</th><th>Fecha y hora</th><?php if ($esAdmin): ?><th>Instructor</th><?php endif; ?><th class="d-none d-md-table-cell">Sala</th><th>Cupos</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($clases as $c):
          $pct = $c['cupo'] ? min(100, round($c['ocupados'] / $c['cupo'] * 100)) : 0;
          // Para el input datetime-local el formato es Y-m-d\TH:i
          $edit = $c;
          $edit['fecha_hora'] = date('Y-m-d\TH:i', strtotime($c['fecha_hora']));
      ?>
        <tr class="<?= $c['estado'] === 'Cancelada' ? 'opacity-50' : '' ?>">
          <td><span class="fw-semibold"><?= e($c['nombre']) ?></span><br><span class="badge text-bg-secondary"><?= e($c['tipo']) ?></span></td>
          <td class="text-nowrap"><?= fecha_clase($c['fecha_hora']) ?><br><small class="text-secondary"><?= (int) $c['duracion_min'] ?> min</small></td>
          <?php if ($esAdmin): ?><td><?= e($c['instructor']) ?></td><?php endif; ?>
          <td class="d-none d-md-table-cell"><?= e($c['instalacion']) ?></td>
          <td style="min-width:110px">
            <small><?= (int) $c['ocupados'] ?>/<?= (int) $c['cupo'] ?></small>
            <div class="progress fp-progress"><div class="progress-bar <?= $pct >= 100 ? 'bg-warning' : 'bg-danger' ?>" style="width:<?= $pct ?>%"></div></div>
          </td>
          <td><span class="badge <?= $c['estado'] === 'Programada' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= e($c['estado']) ?></span></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-light" href="<?= url('clases/inscritos', ['id' => $c['id']]) ?>" title="Inscritos y asistencia"><i class="bi bi-people"></i></a>
            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalClase" data-editar='<?= e(json_encode($edit)) ?>' title="Editar"><i class="bi bi-pencil"></i></button>
            <form method="post" action="<?= url('clases/estado') ?>" class="d-inline"
                  data-confirmar="<?= $c['estado'] === 'Programada' ? '¿Cancelar esta clase? Los miembros inscritos la verán como cancelada.' : '¿Volver a programar esta clase?' ?>">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <button class="btn btn-sm btn-outline-warning" title="<?= $c['estado'] === 'Programada' ? 'Cancelar clase' : 'Reprogramar' ?>">
                <i class="bi bi-<?= $c['estado'] === 'Programada' ? 'x-circle' : 'arrow-counterclockwise' ?>"></i></button>
            </form>
            <form method="post" action="<?= url('clases/eliminar') ?>" class="d-inline" data-confirmar="¿Eliminar definitivamente la clase y sus reservas?">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$clases): ?>
        <tr><td colspan="7" class="text-center text-secondary py-4">No hay clases en este periodo.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ============== MODAL CREAR / EDITAR CLASE ============== -->
<div class="modal fade" id="modalClase" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content needs-validation" method="post" action="<?= url('clases/guardar') ?>" novalidate
          data-titulo-nuevo="Nueva clase" data-titulo-editar="Editar clase">
      <div class="modal-header">
        <h2 class="modal-title h5">Nueva clase</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <div class="row g-3">
          <div class="col-md-7">
            <label class="form-label">Nombre de la clase</label>
            <input class="form-control" name="nombre" required maxlength="50" placeholder="Ej.: HIIT Funcional">
            <div class="invalid-feedback">Obligatorio.</div>
          </div>
          <div class="col-md-5">
            <label class="form-label">Tipo</label>
            <input class="form-control" name="tipo" required maxlength="30" list="tiposClase">
            <datalist id="tiposClase"><option>Musculacion</option><option>Funcional</option><option>Cardio</option><option>Flexibilidad</option></datalist>
            <div class="invalid-feedback">Obligatorio.</div>
          </div>
          <?php if ($esAdmin): ?>
          <div class="col-md-6">
            <label class="form-label">Instructor</label>
            <select class="form-select" name="id_instructor" required>
              <option value="">Seleccionar…</option>
              <?php foreach ($instructores as $in): ?>
                <option value="<?= (int) $in['id'] ?>"><?= e($in['nombre'] . ' ' . $in['apellido']) ?><?= $in['especialidad'] ? ' · ' . e($in['especialidad']) : '' ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecciona un instructor.</div>
          </div>
          <?php endif; ?>
          <div class="col-md-6">
            <label class="form-label">Instalación</label>
            <select class="form-select" name="id_instalacion" id="selectSala" required>
              <option value="">Seleccionar…</option>
              <?php foreach ($instalaciones as $s): ?>
                <option value="<?= (int) $s['id'] ?>" data-capacidad="<?= (int) $s['capacidad'] ?>"><?= e($s['nombre']) ?> (máx. <?= (int) $s['capacidad'] ?>)</option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecciona una sala.</div>
          </div>
          <div class="col-md-5">
            <label class="form-label">Fecha y hora</label>
            <input type="datetime-local" class="form-control" name="fecha_hora" required>
            <div class="invalid-feedback">Obligatorio.</div>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Duración (min)</label>
            <input type="number" class="form-control" name="duracion_min" value="60" min="15" max="240" step="5" required>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label">Cupo</label>
            <input type="number" class="form-control" name="cupo" id="inputCupo" value="15" min="1" required>
            <div class="invalid-feedback" id="cupoFeedback">Cupo no válido.</div>
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="2" maxlength="255"></textarea>
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
