<?php
/**
 * ---------------------------------------------------------------------
 * views/layout/footer.php
 * ---------------------------------------------------------------------
 * Cierre de la página: pie, contenedor de notificaciones (toasts) y
 * scripts. Cada vista puede pedir scripts extra definiendo $scripts
 * (ej. ['reservas.js']) y Chart.js con $usarGraficas = true.
 * ---------------------------------------------------------------------
 */
defined('APP') or exit('Acceso directo no permitido');
?>
</main>

<footer class="fp-footer">
  <div class="container d-flex flex-wrap justify-content-between gap-2">
    <span><span class="fp-logo small">FUERZA<span>PRO</span></span> · Sistema de gestión de gimnasio</span>
    <span class="text-secondary">Programación Web Avanzada · <?= date('Y') ?></span>
  </div>
</footer>

<!-- Contenedor de notificaciones que usa FP.toast() en app.js -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toasts"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($usarGraficas)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php endif; ?>
<script src="<?= asset('js/app.js') ?>"></script>
<?php foreach ($scripts ?? [] as $s): ?>
<script src="<?= asset('js/' . $s) ?>"></script>
<?php endforeach; ?>
</body>
</html>
