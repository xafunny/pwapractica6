<?php
/**
 * views/instalaciones/index.php - CRUD de instalaciones (ADMINISTRADOR).
 * Variables: $instalaciones (con clases_futuras)
 */
defined('APP') or exit('Acceso directo no permitido');
// Icono según el tipo de instalación.
$iconos = ['Musculacion' => 'bi-trophy', 'Funcional' => 'bi-lightning', 'Cardio' => 'bi-bicycle', 'Flexibilidad' => 'bi-flower1'];
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">Instalaciones</h1>
    <p class="text-secondary mb-0">Salas y áreas del gimnasio donde se dictan las clases.</p>
  </div>
  <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalInstalacion"><i class="bi bi-plus-lg"></i> Nueva instalación</button>
</div>

<div class="row g-3">
  <?php foreach ($instalaciones as $i): ?>
    <div class="col-sm-6 col-xl-3">
      <div class="card fp-card h-100">
        <div class="card-body">
          <div class="fp-icon-box mb-3"><i class="bi <?= $iconos[$i['tipo']] ?? 'bi-building' ?>"></i></div>
          <h2 class="h5 mb-1"><?= e($i['nombre']) ?></h2>
          <span class="badge text-bg-secondary mb-2"><?= e($i['tipo']) ?></span>
          <p class="small text-secondary mb-3"><?= e($i['descripcion']) ?></p>
          <div class="d-flex justify-content-between small">
            <span><i class="bi bi-people"></i> <?= (int) $i['capacidad'] ?> personas</span>
            <span><i class="bi bi-calendar-event"></i> <?= (int) $i['clases_futuras'] ?> clases</span>
          </div>
        </div>
        <div class="card-footer d-flex gap-2 justify-content-end">
          <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalInstalacion"
                  data-editar='<?= e(json_encode($i)) ?>'><i class="bi bi-pencil"></i> Editar</button>
          <form method="post" action="<?= url('instalaciones/eliminar') ?>" data-confirmar="¿Eliminar la instalación «<?= e($i['nombre']) ?>»?">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$instalaciones): ?>
    <div class="col-12"><div class="fp-empty"><i class="bi bi-building"></i><p>Aún no hay instalaciones registradas.</p></div></div>
  <?php endif; ?>
</div>

<!-- ============== MODAL CREAR / EDITAR INSTALACIÓN ============== -->
<div class="modal fade" id="modalInstalacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('instalaciones/guardar') ?>" novalidate
          data-titulo-nuevo="Nueva instalación" data-titulo-editar="Editar instalación">
      <div class="modal-header">
        <h2 class="modal-title h5">Nueva instalación</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input class="form-control" name="nombre" required maxlength="50">
          <div class="invalid-feedback">Obligatorio.</div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-7">
            <label class="form-label">Tipo</label>
            <input class="form-control" name="tipo" required maxlength="30" list="tiposInstalacion">
            <datalist id="tiposInstalacion"><option>Musculacion</option><option>Funcional</option><option>Cardio</option><option>Flexibilidad</option></datalist>
            <div class="invalid-feedback">Obligatorio.</div>
          </div>
          <div class="col-5">
            <label class="form-label">Capacidad</label>
            <input type="number" class="form-control" name="capacidad" required min="1" max="500">
            <div class="invalid-feedback">Mayor a 0.</div>
          </div>
        </div>
        <label class="form-label">Descripción</label>
        <textarea class="form-control" name="descripcion" rows="2" maxlength="255"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-danger"><i class="bi bi-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>
