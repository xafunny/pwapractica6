<?php
/** views/errores/404.php - Página no encontrada. */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="text-center py-5">
  <div class="fp-display text-danger" style="font-size:6rem">404</div>
  <p class="lead">La página que buscas no existe.</p>
  <a href="<?= url('inicio/index') ?>" class="btn btn-danger"><i class="bi bi-house"></i> Volver al inicio</a>
</div>
