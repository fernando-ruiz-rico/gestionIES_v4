// Avisos basados en SweetAlert2.
//
// Centraliza los avisos que se repiten en todas las vistas para no volver a
// escribir la configuración (icono, temporizador, botones...) en cada
// lugar. Cada función reproduce el aspecto de su llamada original, de modo
// que el cambio es puramente de simplificación, no de comportamiento.
//
// Convenciones:
//   - Avisos.exito:  se cierra solo (toast, sin botón).
//   - Avisos.error:  diálogo con botón de cierre.
//   - Avisos.aviso:  aviso de validación / advertencia.
//   - Avisos.confirmar: devuelve la promesa para inspeccionar isConfirmed.

const Avisos = {
    // Aviso de error. Mismo aspecto que el anterior Swal.fire('Error', ...).
    error(texto) {
        return Swal.fire('Error', texto, 'error');
    },

    // Aviso de validación o advertencia.
    aviso(texto) {
        return Swal.fire('Error', texto, 'warning');
    },

    // Aviso de éxito: se cierra solo, sin botón de confirmación.
    exito(titulo, texto) {
        return Swal.fire({
            icon: 'success',
            title: titulo,
            text: texto,
            timer: 1500,
            showConfirmButton: false
        });
    },

    // Confirmación. Devuelve la promesa para que el llamador la use con
    // .then(...) o con await e inspeccione isConfirmed.
    // `opciones` admite { boton, icono } y cualquier otra opción extra de
    // SweetAlert2 (por ejemplo confirmButtonColor).
    confirmar(titulo, texto, { boton = 'Sí, eliminar', icono = 'warning', ...extra } = {}) {
        return Swal.fire({
            title: titulo,
            text: texto,
            icon: icono,
            showCancelButton: true,
            confirmButtonText: boton,
            cancelButtonText: 'Cancelar',
            ...extra
        });
    }
};
