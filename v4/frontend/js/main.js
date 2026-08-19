// Funciones JavaScript globales para la aplicación

// Función para mostrar mensajes en modal usando Bootstrap 5
function mostrarMensaje(texto, tipo) {
    document.getElementById('textoMensaje').textContent = texto;
    var modalElement = document.getElementById('mensajeModal');
    var modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Función para limpiar formularios modales genéricos
function limpiarFormulario(formId) {
    var form = document.querySelector(formId);
    if (form) {
        var inputs = form.querySelectorAll('input[type="text"]');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].value = '';
        }
        var hiddenInputs = form.querySelectorAll('input[type="hidden"]');
        for (var i = 0; i < hiddenInputs.length; i++) {
            hiddenInputs[i].value = '';
        }
        var textareas = form.querySelectorAll('textarea');
        for (var i = 0; i < textareas.length; i++) {
            textareas[i].value = '';
        }
    }
}
