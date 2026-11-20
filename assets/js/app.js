/**
 * =====================================================================
 * assets/js/app.js  -  JavaScript común a todas las páginas
 * =====================================================================
 * Expone el objeto global FP con utilidades:
 *   FP.api(url, datos)   POST con fetch() + token CSRF; devuelve el JSON
 *   FP.toast(msg, tipo)  notificación flotante de Bootstrap
 *   FP.confirmar(msg)    ventana de confirmación (Promise<boolean>)
 *
 * Y activa comportamientos declarativos mediante atributos data-*:
 *   form.needs-validation   validación de Bootstrap antes de enviar
 *   [data-igual-a="id"]     el campo debe coincidir con otro (contraseñas)
 *   [data-medidor="id"]     medidor de seguridad de contraseña
 *   [data-toggle-pass="id"] mostrar / ocultar contraseña
 *   form[data-confirmar]    pide confirmación antes de enviar (eliminar…)
 *   [data-editar='{json}']  llena el formulario del modal para editar
 *   [data-filtrar-tabla] / [data-filtrar-lista]  búsqueda instantánea
 *   [data-contador]         animación de números en las métricas
 * =====================================================================
 */
'use strict';

const FP = (() => {
  /** Token CSRF publicado por el servidor en <meta name="csrf-token">. */
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  /**
   * Envía datos por POST y devuelve la respuesta JSON.
   * Lanza un Error con el mensaje del servidor si algo falla.
   */
  async function api(url, datos = {}) {
    const cuerpo = new FormData();
    Object.entries(datos).forEach(([k, v]) => { if (v !== null && v !== undefined) cuerpo.append(k, v); });
    let respuesta;
    try {
      respuesta = await fetch(url, {
        method: 'POST',
        body: cuerpo,
        headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrf },
        credentials: 'same-origin',
      });
    } catch {
      throw new Error('Sin conexión con el servidor.');
    }
    const json = await respuesta.json().catch(() => ({}));
    if (!respuesta.ok || !json.ok) {
      if (respuesta.status === 401) setTimeout(() => location.reload(), 1200); // sesión expirada
      throw new Error(json.error || 'Ocurrió un error inesperado.');
    }
    return json;
  }

  /** Muestra una notificación (toast) en la esquina inferior derecha. */
  function toast(mensaje, tipo = 'success') {
    const iconos = { success: 'check-circle', danger: 'exclamation-triangle', info: 'info-circle', warning: 'exclamation-circle' };
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${tipo} border-0`;
    el.setAttribute('role', 'status');
    el.innerHTML = `<div class="d-flex"><div class="toast-body"><i class="bi bi-${iconos[tipo] || 'info-circle'} me-1"></i></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button></div>`;
    el.querySelector('.toast-body').append(mensaje); // append = texto seguro (sin HTML)
    document.getElementById('toasts').append(el);
    const t = new bootstrap.Toast(el, { delay: 3500 });
    el.addEventListener('hidden.bs.toast', () => el.remove());
    t.show();
  }

  /** Ventana de confirmación con estilo Bootstrap. Resuelve true/false. */
  function confirmar(mensaje) {
    return new Promise(resolver => {
      const el = document.createElement('div');
      el.className = 'modal fade';
      el.tabIndex = -1;
      el.innerHTML = `<div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content">
          <div class="modal-body py-4"><i class="bi bi-question-circle text-danger fs-3 d-block mb-2"></i><p class="mb-0"></p></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" data-ok>Confirmar</button>
          </div></div></div>`;
      el.querySelector('p').textContent = mensaje;
      document.body.append(el);
      const modal = new bootstrap.Modal(el);
      let aceptado = false;
      el.querySelector('[data-ok]').addEventListener('click', () => { aceptado = true; modal.hide(); });
      el.addEventListener('hidden.bs.modal', () => { el.remove(); resolver(aceptado); });
      modal.show();
    });
  }

  return { api, toast, confirmar, csrf };
})();

document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Validación de formularios (Bootstrap) ---------- */
  document.querySelectorAll('form.needs-validation').forEach(form => {
    form.addEventListener('submit', e => {
      // Campos que deben ser iguales a otro (ej. repetir contraseña).
      form.querySelectorAll('[data-igual-a]').forEach(campo => {
        const otro = document.getElementById(campo.dataset.igualA);
        campo.setCustomValidity(otro && campo.value !== otro.value ? 'No coincide' : '');
      });
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.querySelector(':invalid')?.focus();
      }
      form.classList.add('was-validated');
    });
  });

  /* ---------- Medidor de seguridad de contraseña ---------- */
  document.querySelectorAll('[data-medidor]').forEach(input => {
    const barra = document.getElementById(input.dataset.medidor);
    input.addEventListener('input', () => {
      const v = input.value;
      let puntos = 0;
      if (v.length >= 8) puntos++;
      if (/[A-Z]/.test(v) && /[a-z]/.test(v)) puntos++;
      if (/\d/.test(v)) puntos++;
      if (/[^A-Za-z0-9]/.test(v)) puntos++;
      const colores = ['bg-danger', 'bg-danger', 'bg-warning', 'bg-info', 'bg-success'];
      barra.className = 'progress-bar ' + colores[puntos];
      barra.style.width = (v ? Math.max(10, puntos * 25) : 0) + '%';
    });
  });

  /* ---------- Mostrar / ocultar contraseña ---------- */
  document.querySelectorAll('[data-toggle-pass]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.togglePass);
      const mostrar = input.type === 'password';
      input.type = mostrar ? 'text' : 'password';
      btn.innerHTML = `<i class="bi bi-eye${mostrar ? '-slash' : ''}"></i>`;
    });
  });

  /* ---------- Confirmación antes de enviar (eliminar, cancelar…) ---------- */
  document.querySelectorAll('form[data-confirmar]').forEach(form => {
    form.addEventListener('submit', async e => {
      if (form.dataset.confirmado) return; // ya confirmado: se envía normal
      e.preventDefault();
      if (await FP.confirmar(form.dataset.confirmar)) {
        form.dataset.confirmado = '1';
        form.submit();
      }
    });
  });

  /* ---------- Modales de crear / editar ----------
   * Si el botón que abre el modal tiene data-editar='{json}', se llenan
   * los campos por su atributo name. Si no, el formulario se limpia
   * para crear un registro nuevo. */
  document.querySelectorAll('.modal').forEach(modal => {
    const form = modal.querySelector('form[data-titulo-nuevo]');
    if (!form) return;
    modal.addEventListener('show.bs.modal', e => {
      const datos = e.relatedTarget?.dataset.editar ? JSON.parse(e.relatedTarget.dataset.editar) : null;
      const editando = !!datos;

      // Limpiar todo menos el token CSRF y los campos fijos (miembro).
      form.classList.remove('was-validated');
      form.querySelectorAll('input, select, textarea').forEach(campo => {
        if (campo.name === 'csrf' || campo.hasAttribute('data-fijo')) return;
        if (campo.tagName === 'SELECT') campo.selectedIndex = 0;
        else if (campo.type !== 'checkbox') campo.value = campo.defaultValue;
      });

      if (editando) {
        Object.entries(datos).forEach(([nombre, valor]) => {
          const campo = form.elements[nombre];
          if (campo && !campo.hasAttribute?.('data-fijo')) campo.value = valor ?? '';
        });
      } else {
        // Valores sugeridos para un registro nuevo.
        form.querySelectorAll('[data-hoy]').forEach(c => { c.value = new Date().toLocaleDateString('en-CA'); });
        form.querySelectorAll('[data-defecto]').forEach(c => { c.value = c.dataset.defecto; });
      }

      form.querySelectorAll('[data-requerido-al-crear]').forEach(c => { c.required = !editando; });
      form.querySelectorAll('[data-solo-edicion]').forEach(c => c.classList.toggle('d-none', !editando));
      modal.querySelector('.modal-title').textContent = editando ? form.dataset.tituloEditar : form.dataset.tituloNuevo;

      // Aviso para que los scripts de cada página reaccionen (usuarios.js, etc.).
      form.dispatchEvent(new CustomEvent('fp:modal-llenado', { detail: { editando, datos } }));
    });
    // Foco en el primer campo de texto visible al abrir (o en el primer select).
    modal.addEventListener('shown.bs.modal', () => {
      const visible = el => el.offsetParent !== null;
      const campos = [...form.querySelectorAll('input:not([type=hidden]):not([readonly]), textarea')].filter(visible);
      (campos[0] || form.querySelector('select'))?.focus();
    });
  });

  /* ---------- Búsqueda instantánea en tablas y listas ---------- */
  document.querySelectorAll('[data-filtrar-tabla], [data-filtrar-lista]').forEach(input => {
    const id = input.dataset.filtrarTabla || input.dataset.filtrarLista;
    const items = input.dataset.filtrarTabla
      ? document.querySelectorAll(`#${id} tbody tr`)
      : document.querySelectorAll(`#${id} > *`);
    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      items.forEach(el => { el.classList.toggle('d-none', q !== '' && !el.textContent.toLowerCase().includes(q)); });
    });
  });

  /* ---------- Animación de contadores en las métricas ---------- */
  document.querySelectorAll('[data-contador]').forEach(el => {
    const final = parseInt(el.dataset.contador, 10) || 0;
    if (final <= 0) return;
    const inicio = performance.now();
    const duracion = 700;
    const paso = ahora => {
      const p = Math.min(1, (ahora - inicio) / duracion);
      el.textContent = Math.round(final * (1 - Math.pow(1 - p, 3))); // easing suave
      if (p < 1) requestAnimationFrame(paso);
    };
    requestAnimationFrame(paso);
  });

  /* ---------- Las alertas flash se cierran solas ---------- */
  document.querySelectorAll('.fp-flash').forEach(alerta => {
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 5000);
  });
});
