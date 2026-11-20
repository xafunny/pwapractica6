/**
 * assets/js/dias.js  -  Pestañas por día (Rutina y Dieta)
 * ---------------------------------------------------------------------
 * - Al abrir el modal para AGREGAR, preselecciona el día de la pestaña
 *   que el usuario está viendo.
 * - Guarda el día activo en la URL (?dia=) para que, al recargar o
 *   volver después de guardar, se mantenga la misma pestaña.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const selectDia = document.getElementById('selectDia');

  /** Día de la pestaña activa. */
  const diaActivo = () => document.querySelector('.fp-dias .nav-link.active')?.dataset.dia;

  selectDia?.form.addEventListener('fp:modal-llenado', e => {
    if (!e.detail.editando && diaActivo()) selectDia.value = diaActivo();
  });

  document.querySelectorAll('.fp-dias [data-dia]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', () => {
      const url = new URL(location.href);
      url.searchParams.set('dia', tab.dataset.dia);
      history.replaceState(null, '', url);
    });
  });
});
