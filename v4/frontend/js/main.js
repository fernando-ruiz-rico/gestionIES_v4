// Funciones JavaScript globales para la aplicación

// Función para mostrar mensajes en modal
function mostrarMensaje(texto, tipo) {
    $('#textoMensaje').text(texto);
    $('#mensajeModal').modal('show');
}

// Función para limpiar formularios modales genéricos
function limpiarFormulario(formId) {
    $(formId + ' input[type="text"]').val('');
    $(formId + ' input[type="hidden"]').val('');
    $(formId + ' textarea').val('');
}
