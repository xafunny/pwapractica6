/**
 * assets/js/rutina.js  -  Marcar ejercicios como hechos
 * ---------------------------------------------------------------------
 * Cada tarjeta tiene un botón [data-marcar="idEjercicio"]. Al pulsarlo:
 *   1. Se cambia el estado visual al instante (respuesta optimista).
 *   2. Se envía a rutina/marcar con fetch().
 *   3. Con la respuesta se actualiza la barra de progreso semanal.
 *   4. Si falla, se revierte el cambio visual.
 * Usa window.FP_RUTINA = { urlMarcar, miembro } definido en la vista.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const cfg = window.FP_RUTINA;
  if (!cfg) return;

  document.querySelectorAll('[data-marcar]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const tarjeta = btn.closest('.fp-ejercicio');
      const antes = tarjeta.classList.contains('fp-hecho');
      pintar(tarjeta, btn, !antes);
      btn.disabled = true;
      try {
        const r = await FP.api(cfg.urlMarcar, { id: btn.dataset.marcar, miembro: cfg.miembro });
        pintar(tarjeta, btn, r.hecho);
        actualizarProgreso(r.hechos, r.total);
        if (r.hecho && r.total > 0 && r.hechos === r.total) {
          FP.toast('¡Completaste toda la rutina de la semana! 💪');
        }
      } catch (err) {
        pintar(tarjeta, btn, antes);
        FP.toast(err.message, 'danger');
      } finally {
        btn.disabled = false;
      }
    });
  });

  /** Aplica el estilo de "hecho" a la tarjeta. */
  function pintar(tarjeta, btn, hecho) {
    tarjeta.classList.toggle('fp-hecho', hecho);
    btn.setAttribute('aria-pressed', hecho ? 'true' : 'false');
  }

  /** Actualiza texto, porcentaje y barra del progreso semanal. */
  function actualizarProgreso(hechos, total) {
    const pct = total ? Math.round(hechos / total * 100) : 0;
    document.getElementById('progresoTexto').textContent = `${hechos}/${total}`;
    document.getElementById('progresoPct').textContent = `${pct}%`;
    document.getElementById('progresoBarra').style.width = `${pct}%`;
  }
});
