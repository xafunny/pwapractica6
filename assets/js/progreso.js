/**
 * assets/js/progreso.js  -  Gráfica de composición corporal (Chart.js)
 * ---------------------------------------------------------------------
 * - Lee window.FP_MEDICIONES (JSON generado por PHP en la vista).
 * - Dibuja una gráfica de línea; los botones [data-metrica] cambian
 *   la métrica mostrada (peso, grasa, músculo o IMC).
 * - En el modal calcula el IMC en vivo mientras se escribe.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const datos = window.FP_MEDICIONES || [];
  const canvas = document.getElementById('graficaProgreso');
  let grafica = null;

  /** Crea o actualiza la gráfica para una métrica. */
  function dibujar(metrica, unidad) {
    const puntos = datos.filter(d => d[metrica] !== null);
    const config = {
      labels: puntos.map(d => d.fecha),
      valores: puntos.map(d => d[metrica]),
    };
    if (grafica) {
      grafica.data.labels = config.labels;
      grafica.data.datasets[0].data = config.valores;
      grafica.data.datasets[0].label = unidad ? `${metrica} (${unidad})` : metrica;
      grafica.update();
      return;
    }
    const ctx = canvas.getContext('2d');
    const degradado = ctx.createLinearGradient(0, 0, 0, 280);
    degradado.addColorStop(0, 'rgba(220, 38, 38, .35)');
    degradado.addColorStop(1, 'rgba(220, 38, 38, 0)');

    grafica = new Chart(ctx, {
      type: 'line',
      data: {
        labels: config.labels,
        datasets: [{
          label: unidad ? `${metrica} (${unidad})` : metrica,
          data: config.valores,
          borderColor: '#dc2626',
          backgroundColor: degradado,
          fill: true,
          tension: .35,
          pointRadius: 4,
          pointBackgroundColor: '#dc2626',
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: '#a1a1aa' }, grid: { color: 'rgba(255,255,255,.05)' } },
          y: { ticks: { color: '#a1a1aa' }, grid: { color: 'rgba(255,255,255,.05)' } },
        },
      },
    });
  }

  if (canvas && datos.length && window.Chart) {
    dibujar('peso', 'kg');
    document.querySelectorAll('[data-metrica]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('[data-metrica]').forEach(b => b.classList.toggle('active', b === btn));
        dibujar(btn.dataset.metrica, btn.dataset.unidad);
      });
    });
  }

  /* ---- Cálculo del IMC en vivo dentro del modal ---- */
  const peso = document.getElementById('inputPeso');
  const estatura = document.getElementById('inputEstatura');
  const imc = document.getElementById('imcPrevio');

  function calcularImc() {
    const p = parseFloat(peso.value);
    const e = parseFloat(estatura.value) / 100;
    if (!p || !e) { imc.value = ''; return; }
    const valor = p / (e * e);
    const categoria = valor < 18.5 ? 'Bajo peso' : valor < 25 ? 'Normal' : valor < 30 ? 'Sobrepeso' : 'Obesidad';
    imc.value = `${valor.toFixed(1)} · ${categoria}`;
  }

  if (peso && estatura) {
    peso.addEventListener('input', calcularImc);
    estatura.addEventListener('input', calcularImc);
    peso.form.addEventListener('fp:modal-llenado', calcularImc);
  }
});
