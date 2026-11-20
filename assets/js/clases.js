/**
 * assets/js/clases.js  -  Formulario de clases (Admin / Instructor)
 * ---------------------------------------------------------------------
 * - Limita el cupo a la capacidad de la instalación elegida.
 * - Para clases nuevas, la fecha mínima es "ahora".
 * (El servidor vuelve a validar todo; esto solo mejora la experiencia.)
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const sala = document.getElementById('selectSala');
  const cupo = document.getElementById('inputCupo');
  const feedback = document.getElementById('cupoFeedback');
  if (!sala || !cupo) return;
  const fecha = sala.form.elements['fecha_hora'];

  /** Ajusta el máximo del cupo según la capacidad de la sala. */
  function ajustarCupo() {
    const capacidad = sala.selectedOptions[0]?.dataset.capacidad;
    if (capacidad) {
      cupo.max = capacidad;
      feedback.textContent = `El cupo debe estar entre 1 y ${capacidad}.`;
      if (+cupo.value > +capacidad) cupo.value = capacidad;
    } else {
      cupo.removeAttribute('max');
    }
  }

  sala.addEventListener('change', ajustarCupo);
  sala.form.addEventListener('fp:modal-llenado', e => {
    ajustarCupo();
    // Fecha mínima (formato yyyy-mm-ddThh:mm en hora local) solo al crear.
    const ahora = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    if (e.detail.editando) fecha.removeAttribute('min'); else fecha.min = ahora;
  });
});
