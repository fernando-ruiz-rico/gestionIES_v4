// Funciones útiles para la gestión de los departamentos en v4

// Carga en el div con id "listadepartamentos" el listado de departamentos obtenido
function cargarDepartamentos() {
    fetch('../backend/api/departamentos/listar.php', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        const contenedor = document.getElementById('listadepartamentos');
        if (!contenedor) return;
        
        if (data.error) {
            contenedor.innerHTML = '<div class="alert alert-danger m-3">' + escapeHtml(data.error) + '</div>';
            return;
        }
        
        if (data.length === 0) {
            contenedor.innerHTML = '<div class="alert alert-info m-3">No hay departamentos registrados</div>';
            return;
        }
        
        let html = '';
        data.forEach(depto => {
            html += '<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">';
            html += '<div class="d-flex align-items-center">';
            html += '<i class="bi bi-building me-3 text-primary fs-5"></i>';
            html += '<span class="fw-medium">' + escapeHtml(depto.nombre) + '</span>';
            html += '</div>';
            html += '<div class="btn-group" role="group">';
            html += '<button class="btn btn-outline-primary btn-sm" onclick="cargarDepartamentoModal(' + depto.id + ')" title="Editar">';
            html += '<i class="bi bi-pencil-square"></i>';
            html += '</button>';
            html += '<button class="btn btn-outline-danger btn-sm" onclick="borrarDepartamento(' + depto.id + ", '" + escapeHtml(depto.nombre) + ')" title="Eliminar">';
            html += '<i class="bi bi-trash"></i>';
            html += '</button>';
            html += '</div></div>';
        });
        
        contenedor.innerHTML = html;
    })
    .catch(error => {
        console.error('Error al cargar departamentos:', error);
        document.getElementById('listadepartamentos').innerHTML = '<div class="alert alert-danger m-3">Error al cargar los departamentos</div>';
    });
}

// Carga en el formulario modal de departamentos los datos del departamento con el ID proporcionado
function cargarDepartamentoModal(id) {
    fetch('../backend/api/departamentos/obtener.php?id=' + id, {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            mostrarMensaje('Error al cargar el departamento: ' + data.error, 0);
            return;
        }
        
        document.getElementById('idDepartamento').value = data.id || '';
        document.getElementById('nombre').value = data.nombre || '';
        
        // Mostrar el modal usando Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('formdepartamento'));
        modal.show();
    })
    .catch(error => {
        console.error('Error al cargar departamento:', error);
        mostrarMensaje('Error al cargar el departamento', 0);
    });
}

// Abre el formulario modal de departamentos para dar de alta uno nuevo
function nuevoDepartamento() {
    limpiarFormularioDepartamentos();
    const modal = new bootstrap.Modal(document.getElementById('formdepartamento'));
    modal.show();
}

// Borra el departamento con el ID indicado, previa confirmación
function borrarDepartamento(id, nombre) {
    Swal.fire({
        title: '¿Confirmar borrado?',
        text: "¿Estás seguro de eliminar el departamento '" + nombre + "'? Sólo se podrá eliminar si no tiene profesores u otros recursos asociados.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-2"></i>Sí, eliminar',
        cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('../backend/api/departamentos/eliminar.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje('Departamento eliminado correctamente', 1);
                    cargarDepartamentos();
                } else {
                    mostrarMensaje(data.mensaje || 'Error al eliminar el departamento', 0);
                }
            })
            .catch(error => {
                console.error('Error al eliminar departamento:', error);
                mostrarMensaje('Error al eliminar el departamento', 0);
            });
        }
    });
}

// Borra los datos del formulario modal de departamentos
function limpiarFormularioDepartamentos() {
    document.getElementById('idDepartamento').value = '';
    document.getElementById('nombre').value = '';
}

// Evento de envío del formulario modal de departamentos
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formdep');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch('../backend/api/departamentos/guardar.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje(data.mensaje || 'Departamento guardado correctamente', 1);
                    limpiarFormularioDepartamentos();
                    
                    // Ocultar modal
                    const modalEl = document.getElementById('formdepartamento');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    
                    // Recargar lista
                    cargarDepartamentos();
                } else {
                    mostrarMensaje(data.error || 'Error al guardar el departamento', 0);
                }
            })
            .catch(error => {
                console.error('Error al guardar departamento:', error);
                mostrarMensaje('Error al guardar el departamento', 0);
            });
        });
    }
});

// Función auxiliar para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Función auxiliar para mostrar mensajes con SweetAlert2
function mostrarMensaje(mensaje, tipo) {
    // tipo: 0 = error, 1 = éxito
    const icono = tipo === 1 ? 'success' : 'error';
    const titulo = tipo === 1 ? '¡Éxito!' : 'Error';
    
    Swal.fire({
        icon: icono,
        title: titulo,
        text: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
