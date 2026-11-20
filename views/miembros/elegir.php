<?php
/**
 * views/miembros/elegir.php - Selector de miembro. Aparece cuando el
 * instructor/admin entra a Rutina, Dieta o Progreso sin elegir miembro.
 * Variables: $modulo (ruta destino), $miembros
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card fp-card">
      <div class="card-body p-4">
        <h1 class="h4 fw-bold">Elige un miembro</h1>
        <p class="text-secondary small">Selecciona a quién quieres ver o editar.</p>
        <input type="search" class="form-control mb-3" data-filtrar-lista="listaElegir" placeholder="Buscar…" aria-label="Buscar miembro">
        <div class="list-group" id="listaElegir">
          <?php foreach ($miembros as $m): ?>
            <a class="list-group-item list-group-item-action d-flex align-items-center gap-2" href="<?= url($modulo, ['miembro' => $m['id']]) ?>">
              <span class="fp-avatar"><?= e(iniciales($m['nombre'], $m['apellido'])) ?></span>
              <span><?= e($m['nombre'] . ' ' . $m['apellido']) ?><br><small class="text-secondary"><?= e($m['correo']) ?></small></span>
            </a>
          <?php endforeach; ?>
          <?php if (!$miembros): ?><p class="text-secondary">No hay miembros registrados.</p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
