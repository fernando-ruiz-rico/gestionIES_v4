// Funciones útiles para la gestión de los departamentos

// Carga en el div con id "listadepartamentos" el listado de departamentos obtenido
function cargarDepartamentos()
{
    fetch('ajax/departamentos/cargar_departamentos.php')
    .then(response => response.text())
    .then(res => {
        const listadepartamentos = document.getElementById('listadepartamentos');
        if (listadepartamentos) {
            listadepartamentos.innerHTML = res;
        }
    })
    .catch(error => {
        console.error('Error al cargar departamentos:', error);
        mostrarMensaje("Error al cargar los departamentos", 0);
    });
}

// Carga en el formulario modal de departamentos modales/departamentos.php los datos
// del departamento con el "id" proporcionado (se reciben en formato JSON)
function cargarDepartamentoModal(id)
{
    fetch('ajax/departamentos/cargar_departamento.php?idDepartamento=' + encodeURIComponent(id))
    .then(response => response.json())
    .then(res => {
        const idDepartamentoInput = document.getElementById('idDepartamento');
        const nombreInput = document.getElementById('nombre');
        
        if (idDepartamentoInput) idDepartamentoInput.value = id;
        if (nombreInput) nombreInput.value = res.nombre || '';
        
        const formdepartamentoModal = document.getElementById('formdepartamento');
        if (formdepartamentoModal) {
            const modal = new bootstrap.Modal(formdepartamentoModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar departamento:', error);
        mostrarMensaje("Error al cargar los datos del departamento", 0);
    });
}

// Abre el formulario modal de departamentos para dar de alta uno nuevo (borra sus campos antes)
function nuevoDepartamento()
{
    limpiarFormularioDepartamentos();
    const formdepartamentoModal = document.getElementById('formdepartamento');
    if (formdepartamentoModal) {
        const modal = new bootstrap.Modal(formdepartamentoModal);
        modal.show();
    }
}

// Borra el departamento con el "id" indicado, previa confirmación
// La llamada AJAX devuelve "si" si ha habido algún error en el proceso
function borrarDepartamento(id, nombre)
{
    if (confirm("Confirmas el borrado del departamento '" + nombre + "'? Sólo se podrá eliminar si no tiene profesores asociados. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch('ajax/departamentos/borrar_departamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.text())
        .then(res => {
            if (res.trim() == 'si') {
                mostrarMensaje("Error al borrar el departamento. Puede que tenga profesores u otros recursos asociados que se deban borrar antes", 0);
            } else {
                window.location.href = "departamentos.php";
            }
        })
        .catch(error => {
            console.error('Error al borrar departamento:', error);
            mostrarMensaje("Error al borrar el departamento", 0);
        });
    }
}

// Borra los datos del formulario modal de departamentos
function limpiarFormularioDepartamentos()
{
    const idDepartamentoInput = document.getElementById('idDepartamento');
    const nombreInput = document.getElementById('nombre');
    
    if (idDepartamentoInput) idDepartamentoInput.value = "";
    if (nombreInput) nombreInput.value = "";
}

// Evento de envío del formulario modal de departamentos
const formdepForm = document.getElementById('formdep');
if (formdepForm) {
    formdepForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formdepForm);
        
        fetch('ajax/departamentos/insertar_departamento.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(res => {
            // Al recibir la respuesta, vaciamos formulario y recargamos la página
            // En este caso no se controlan errores porque los datos son simples
            limpiarFormularioDepartamentos();
            const formdepartamentoModal = document.getElementById('formdepartamento');
            if (formdepartamentoModal) {
                const modal = bootstrap.Modal.getInstance(formdepartamentoModal);
                if (modal) {
                    modal.hide();
                }
            }
            window.location.href = "departamentos.php";
        })
        .catch(error => {
            console.error('Error al guardar departamento:', error);
            mostrarMensaje("Error al guardar el departamento", 0);
        });
    });
}
