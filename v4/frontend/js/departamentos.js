// Funciones útiles para la gestión de los departamentos en v4

// Carga en el div con id "listadepartamentos" el listado de departamentos obtenido
function cargarDepartamentos() {
    DepartamentosAPI.listar()
    .then(filas => {
        const contenedor = document.getElementById('listadepartamentos');
        if (!contenedor) return;
        
        if (filas.length === 0) {
            contenedor.innerHTML = '<div class="alert alert-info m-3">No hay departamentos registrados</div>';
            return;
        }
        
        let html = '';
        filas.forEach(depto => {
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
    DepartamentosAPI.obtener(id)
    .then(depto => {
        const d = depto || {};
        document.getElementById('idDepartamento').value = d.id || '';
        document.getElementById('nombre').value = d.nombre || '';
        
        // Mostrar el modal usando Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('formdepartamento'));
        modal.show();
    })
    .catch(error => {
        console.error('Error al cargar departamento:', error);
        Avisos.error('Error al cargar el departamento');
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
    Avisos.confirmar(
        '¿Confirmar borrado?',
        "¿Estás seguro de eliminar el departamento '" + nombre + "'? Sólo se podrá eliminar si no tiene profesores u otros recursos asociados.",
        {
            boton: '<i class="bi bi-trash me-2"></i>Sí, eliminar',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }
    ).then((result) => {
        if (result.isConfirmed) {
            DepartamentosAPI.eliminar(id)
            .then(() => {
                Avisos.exito('¡Éxito!', 'Departamento eliminado correctamente');
                cargarDepartamentos();
            })
            .catch(error => {
                console.error('Error al eliminar departamento:', error);
                Avisos.error(error.message || 'Error al eliminar el departamento');
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
            
            DepartamentosAPI.guardar({
                idDepartamento: document.getElementById('idDepartamento').value,
                nombre: document.getElementById('nombre').value
            })
            .then(data => {
                Avisos.exito('¡Éxito!', data.message || 'Departamento guardado correctamente');
                limpiarFormularioDepartamentos();
                
                // Ocultar modal
                const modalEl = document.getElementById('formdepartamento');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                
                // Recargar lista
                cargarDepartamentos();
            })
            .catch(error => {
                console.error('Error al guardar departamento:', error);
                Avisos.error(error.message || 'Error al guardar el departamento');
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
