/**
 * assets/js/asistencia.js  -  Toma de asistencia (Instructor / Admin)
 * ---------------------------------------------------------------------
 * Cada fila tiene un grupo [data-asistencia="idReserva"] con botones
 * Asistió / Faltó. Al pulsar se guarda con fetch() en
 * clases/asistencia. Pulsar el botón ya activo lo desmarca
 * (vuelve a "Reservada").
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-asistencia]').forEach(grupo => {
    grupo.addEventListener('click', async e => {
      const btn = e.target.closest('button[data-estado]');
      if (!btn) return;
      const estado = btn.classList.contains('active') ? 'Reservada' : btn.dataset.estado;
      const botones = grupo.querySelectorAll('button');
      botones.forEach(b => { b.disabled = true; });
      try {
        await FP.api('index.php?r=clases/asistencia', { id: grupo.dataset.asistencia, estado });
        botones.forEach(b => b.classList.toggle('active', b.dataset.estado === estado));
        FP.toast(estado === 'Reservada' ? 'Asistencia desmarcada.' : 'Asistencia guardada.', 'info');
      } catch (err) {
        FP.toast(err.message, 'danger');
      } finally {
        botones.forEach(b => { b.disabled = false; });
      }
    });
  });
});
