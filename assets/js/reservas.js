/**
 * assets/js/reservas.js  -  Reservar / cancelar clases (Miembro)
 * ---------------------------------------------------------------------
 * Cada tarjeta de clase tiene data-clase="id" y un botón
 * .fp-btn-reserva con data-accion="crear" | "cancelar".
 * Al hacer clic se llama con fetch() a reservas/crear o
 * reservas/cancelar y se actualizan en pantalla los cupos, la barra
 * de ocupación, la insignia "Reservada" y el propio botón.
 * Si el botón tiene data-quitar (página Mis reservas), la tarjeta se
 * elimina con una animación al cancelar.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {

  document.querySelectorAll('.fp-btn-reserva').forEach(btn => {
    btn.addEventListener('click', async () => {
      const tarjeta = btn.closest('[data-clase]');
      const accion = btn.dataset.accion;

      if (accion === 'cancelar' && !(await FP.confirmar('¿Cancelar tu reserva en esta clase?'))) return;

      const textoOriginal = btn.textContent;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      try {
        const r = await FP.api(`index.php?r=reservas/${accion}`, { clase: tarjeta.dataset.clase });
        FP.toast(r.mensaje);

        // Página "Mis reservas": quitar la tarjeta.
        if (btn.dataset.quitar) {
          const col = tarjeta.parentElement;
          col.classList.add('fp-salida');
          setTimeout(() => col.remove(), 300);
          return;
        }
        actualizarTarjeta(tarjeta, accion === 'crear', r.ocupados, r.cupo);
      } catch (err) {
        FP.toast(err.message, 'danger');
        btn.textContent = textoOriginal;
        btn.disabled = false;
      }
    });
  });

  /** Refleja el nuevo estado de la reserva en la tarjeta. */
  function actualizarTarjeta(tarjeta, reservada, ocupados, cupo) {
    const btn = tarjeta.querySelector('.fp-btn-reserva');
    tarjeta.classList.toggle('fp-clase-reservada', reservada);
    tarjeta.querySelector('.fp-badge-reservada')?.classList.toggle('d-none', !reservada);
    tarjeta.querySelector('.fp-cupos').textContent = `${ocupados}/${cupo}`;
    tarjeta.querySelector('.fp-cupos-barra').style.width = Math.min(100, Math.round(ocupados / cupo * 100)) + '%';

    const llena = ocupados >= cupo;
    btn.dataset.accion = reservada ? 'cancelar' : 'crear';
    btn.className = `btn w-100 fp-btn-reserva ${reservada ? 'btn-outline-light' : 'btn-danger'}`;
    btn.textContent = reservada ? 'Cancelar reserva' : (llena ? 'Clase llena' : 'Reservar');
    btn.disabled = !reservada && llena;
  }

  /* Búsqueda instantánea por nombre, tipo o instructor. */
  const buscador = document.getElementById('buscarClase');
  buscador?.addEventListener('input', () => {
    const q = buscador.value.trim().toLowerCase();
    let visibles = 0;
    document.querySelectorAll('#listaClases [data-buscar]').forEach(col => {
      const ok = col.dataset.buscar.includes(q);
      col.classList.toggle('d-none', !ok);
      if (ok) visibles++;
    });
    document.getElementById('sinResultados').classList.toggle('d-none', visibles > 0);
  });
});
