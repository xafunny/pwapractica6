<?php
/**
 * views/usuarios/index.php - CRUD de usuarios (ADMINISTRADOR).
 * Variables: $usuarios, $rol (filtro), $buscar
 *
 * El mismo modal sirve para crear y editar: los botones "Editar"
 * llevan los datos en data-editar (JSON) y app.js llena el formulario.
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['usuarios.js'];
$filtros = [0 => 'Todos', Auth::MIEMBRO => 'Miembros', Auth::INSTRUCTOR => 'Instructores', Auth::ADMIN => 'Administradores'];
$colorRol = [Auth::ADMIN => 'danger', Auth::INSTRUCTOR => 'warning', Auth::MIEMBRO => 'info'];
?>
<div class="fp-page-head">
  <div>
    <h1 class="fp-title">Usuarios</h1>
    <p class="text-secondary mb-0">Administra miembros, instructores y administradores.</p>
  </div>
  <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalUsuario"><i class="bi bi-person-plus"></i> Nuevo usuario</button>
</div>

<!-- Filtros por rol y búsqueda (GET) -->
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
  <div class="btn-group flex-wrap" role="group" aria-label="Filtrar por rol">
    <?php foreach ($filtros as $id => $txt): ?>
      <a href="<?= url('usuarios/index', array_filter(['rol' => $id, 'q' => $buscar])) ?>"
         class="btn btn-sm <?= (int) $rol === $id ? 'btn-danger' : 'btn-outline-secondary' ?>"><?= $txt ?></a>
    <?php endforeach; ?>
  </div>
  <form class="d-flex gap-2" method="get">
    <input type="hidden" name="r" value="usuarios/index">
    <?php if ($rol): ?><input type="hidden" name="rol" value="<?= (int) $rol ?>"><?php endif; ?>
    <input class="form-control form-control-sm" type="search" name="q" value="<?= e($buscar) ?>" placeholder="Buscar nombre o correo…">
    <button class="btn btn-sm btn-outline-light"><i class="bi bi-search"></i></button>
  </form>
</div>

<div class="card fp-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Usuario</th><th>Rol</th><th class="d-none d-md-table-cell">Teléfono</th><th class="d-none d-lg-table-cell">Detalle</th><th>Activo</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($usuarios as $u):
          // Datos para llenar el modal de edición (sin la contraseña).
          $json = json_encode(array_intersect_key($u, array_flip(['id', 'nombre', 'apellido', 'correo', 'id_rol', 'telefono', 'fecha_nacimiento', 'genero', 'especialidad', 'nivel', 'objetivo'])));
          $esYo = (int) $u['id'] === Auth::id();
      ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="fp-avatar"><?= e(iniciales($u['nombre'], $u['apellido'])) ?></span>
              <div class="lh-sm"><span class="fw-semibold"><?= e($u['nombre'] . ' ' . $u['apellido']) ?></span><?= $esYo ? ' <small class="text-secondary">(tú)</small>' : '' ?><br>
                <small class="text-secondary"><?= e($u['correo']) ?></small></div>
            </div>
          </td>
          <td><span class="badge text-bg-<?= $colorRol[$u['id_rol']] ?>"><?= e($u['rol']) ?></span></td>
          <td class="d-none d-md-table-cell"><?= e($u['telefono']) ?: '<span class="text-secondary">—</span>' ?></td>
          <td class="d-none d-lg-table-cell small text-secondary">
            <?= e($u['especialidad'] ?? '') ?><?= e($u['nivel'] ?? '') ?><?= $u['fecha_nacimiento'] ? ' · ' . edad($u['fecha_nacimiento']) . ' años' : '' ?>
          </td>
          <td>
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" role="switch" aria-label="Activo"
                     data-estado-usuario="<?= (int) $u['id'] ?>" <?= $u['activo'] ? 'checked' : '' ?> <?= $esYo ? 'disabled' : '' ?>>
            </div>
          </td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalUsuario" data-editar='<?= e($json) ?>' title="Editar"><i class="bi bi-pencil"></i></button>
            <?php if (!$esYo): ?>
              <form method="post" action="<?= url('usuarios/eliminar') ?>" class="d-inline"
                    data-confirmar="¿Eliminar a <?= e($u['nombre']) ?>? Se borrarán también sus reservas, rutina, dieta y mediciones.">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$usuarios): ?>
        <tr><td colspan="6" class="text-center text-secondary py-4">No se encontraron usuarios.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ============== MODAL CREAR / EDITAR USUARIO ============== -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="tituloModalUsuario" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content needs-validation" method="post" action="<?= url('usuarios/guardar') ?>" novalidate
          data-titulo-nuevo="Nuevo usuario" data-titulo-editar="Editar usuario">
      <div class="modal-header">
        <h2 class="modal-title h5" id="tituloModalUsuario">Nuevo usuario</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Rol</label>
            <select class="form-select" name="id_rol" id="selectRol" required>
              <option value="<?= Auth::MIEMBRO ?>">Miembro</option>
              <option value="<?= Auth::INSTRUCTOR ?>">Instructor</option>
              <option value="<?= Auth::ADMIN ?>">Administrador</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Nombre</label>
            <input class="form-control" name="nombre" required minlength="2" maxlength="50">
            <div class="invalid-feedback">Obligatorio.</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Apellido</label>
            <input class="form-control" name="apellido" maxlength="50">
          </div>
          <div class="col-md-6">
            <label class="form-label">Correo</label>
            <input type="email" class="form-control" name="correo" required maxlength="100">
            <div class="invalid-feedback">Correo no válido.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Contraseña <small class="text-secondary" data-solo-edicion>(vacío = no cambiar)</small></label>
            <input type="password" class="form-control" name="contrasena" minlength="8" data-requerido-al-crear autocomplete="new-password">
            <div class="invalid-feedback">Mínimo 8 caracteres.</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Teléfono</label>
            <input class="form-control" name="telefono" pattern="\d{7,15}" inputmode="numeric">
            <div class="invalid-feedback">Solo números (7 a 15).</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Fecha de nacimiento</label>
            <input type="date" class="form-control" name="fecha_nacimiento" max="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Género</label>
            <select class="form-select" name="genero">
              <option value="">—</option><option>Masculino</option><option>Femenino</option><option>Otro</option>
            </select>
          </div>
          <!-- Campos que solo aplican a instructores -->
          <div class="col-md-6" data-solo-rol="<?= Auth::INSTRUCTOR ?>">
            <label class="form-label">Especialidad</label>
            <input class="form-control" name="especialidad" maxlength="50" placeholder="Ej.: Musculación, Funcional, Yoga">
          </div>
          <!-- Campos que solo aplican a miembros -->
          <div class="col-md-4" data-solo-rol="<?= Auth::MIEMBRO ?>">
            <label class="form-label">Nivel</label>
            <select class="form-select" name="nivel">
              <option value="">—</option><option>Principiante</option><option>Intermedio</option><option>Avanzado</option>
            </select>
          </div>
          <div class="col-md-8" data-solo-rol="<?= Auth::MIEMBRO ?>">
            <label class="form-label">Objetivo</label>
            <input class="form-control" name="objetivo" maxlength="255">
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
