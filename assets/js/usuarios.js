/**
 * assets/js/usuarios.js  -  Página Usuarios (Administrador)
 * ---------------------------------------------------------------------
 * 1. Muestra en el modal solo los campos del rol elegido
 *    (especialidad -> instructor; nivel/objetivo -> miembro).
 * 2. Activa / desactiva usuarios con el interruptor, sin recargar,
 *    mediante fetch() a usuarios/estado.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const selectRol = document.getElementById('selectRol');
  const form = selectRol?.form;

  /** Oculta los bloques [data-solo-rol] que no corresponden al rol elegido. */
  function mostrarCamposDelRol() {
    form.querySelectorAll('[data-solo-rol]').forEach(bloque => {
      bloque.classList.toggle('d-none', bloque.dataset.soloRol !== selectRol.value);
    });
  }

  if (selectRol) {
    selectRol.addEventListener('change', mostrarCamposDelRol);
    form.addEventListener('fp:modal-llenado', mostrarCamposDelRol); // tras llenar/limpiar el modal
    mostrarCamposDelRol();
  }

  // Interruptores de "Activo".
  document.querySelectorAll('[data-estado-usuario]').forEach(sw => {
    sw.addEventListener('change', async () => {
      sw.disabled = true;
      try {
        const r = await FP.api('index.php?r=usuarios/estado', { id: sw.dataset.estadoUsuario });
        sw.checked = !!r.activo;
        FP.toast(r.activo ? 'Usuario activado.' : 'Usuario desactivado: ya no podrá iniciar sesión.', r.activo ? 'success' : 'warning');
      } catch (err) {
        sw.checked = !sw.checked; // revertir
        FP.toast(err.message, 'danger');
      } finally {
        sw.disabled = false;
      }
    });
  });
});
