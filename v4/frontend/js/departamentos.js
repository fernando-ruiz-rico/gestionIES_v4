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
            contenedor.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }
        
        if (data.length === 0) {
            contenedor.innerHTML = '<p>No hay departamentos registrados</p>';
            return;
        }
        
        let html = '';
        data.forEach(depto => {
            html += '<div class="listado claro izquierda">';
            html += '<div class="izquierda">' + escapeHtml(depto.nombre) + '</div>';
            html += '<div class="derecha">';
            html += '<button class="btn btn-light" onclick="borrarDepartamento(' + depto.id + ", '" + escapeHtml(depto.nombre) + "')\"><img src=\"img/delete.png\" alt=\"Borrar\"></button>";
            html += '<button class="btn btn-light" onclick="cargarDepartamentoModal(' + depto.id + ')\"><img src=\"img/edit.png\" alt=\"Editar\"></button>';
            html += '</div></div>';
        });
        
        contenedor.innerHTML = html;
    })
    .catch(error => {
        console.error('Error al cargar departamentos:', error);
        document.getElementById('listadepartamentos').innerHTML = '<div class="alert alert-danger">Error al cargar los departamentos</div>';
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
    if (confirm("Confirmas el borrado del departamento '" + nombre + "'? Sólo se podrá eliminar si no tiene profesores u otros recursos asociados que se deban borrar antes.")) {
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

// Función auxiliar para mostrar mensajes (compatible con v3)
function mostrarMensaje(mensaje, tipo) {
    // tipo: 0 = error, 1 = éxito
    const clase = tipo === 1 ? 'alert-success' : 'alert-danger';
    const html = '<div class="alert ' + clase + ' alert-dismissible fade show" role="alert">' + 
                 escapeHtml(mensaje) + 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                 '</div>';
    
    // Buscar un contenedor de mensajes o crearlo
    let contenedor = document.getElementById('mensajes');
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'mensajes';
        contenedor.className = 'container mt-3';
        const panel = document.querySelector('.panelcentral') || document.body;
        panel.insertBefore(contenedor, panel.firstChild);
    }
    
    contenedor.innerHTML = html;
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        const alert = contenedor.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}
