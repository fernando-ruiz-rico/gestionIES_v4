// Funciones para gestión de especialidades desde la vista "especialidades.php"
// IMPORTANTE: la función "mostrarMensaje" que se usa en algunas funciones viene incorporada desde
// el fichero js/main.js

// Variable donde guardamos el departamento elegido en el desplegable superior
var selDepartamento = 0;

// Función para guardar en la variable anterior el departamento actualmente seleccionado y
// cargar las especialidades asociadas a ese departamento
function seleccionarDepartamento()
{
    const seleccionDepartamentoSelect = document.getElementById('seleccionDepartamento');
    const idDepartamentoInput = document.getElementById('idDepartamento');
    
    if (seleccionDepartamentoSelect) {
        selDepartamento = seleccionDepartamentoSelect.value;
        if (idDepartamentoInput) {
            idDepartamentoInput.value = selDepartamento;
        }
        cargarEspecialidades();
    }
}

// Carga las especialidades del departamento actualmente seleccionado
function cargarEspecialidades()
{
    fetch('ajax/especialidades/cargar_especialidades.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idDepartamento=' + encodeURIComponent(selDepartamento)
    })
    .then(response => response.text())
    .then(res => {
        const listaespecialidadesDiv = document.getElementById('listaespecialidades');
        if (listaespecialidadesDiv) {
            listaespecialidadesDiv.innerHTML = res;
        }
    })
    .catch(error => {
        console.error('Error al cargar especialidades:', error);
        mostrarMensaje("Error al cargar las especialidades", 0);
    });
}

// Abre el diálogo modal con los datos de la especialidad indicada por su "id"
function cargarEspecialidadModal(id)
{
    fetch('ajax/especialidades/cargar_especialidad.php?idEspecialidad=' + encodeURIComponent(id))
    .then(response => response.json())
    .then(res => {
        const idAntiguoInput = document.getElementById('idAntiguo');
        const idEspecialidadInput = document.getElementById('idEspecialidad');
        const idDepartamentoInput = document.getElementById('idDepartamento');
        const descripcionTextarea = document.getElementById('descripcion');
        const horasTutoriaInput = document.getElementById('horasTutoria');
        const horasInglesInput = document.getElementById('horasIngles');
        const profesoresTextarea = document.getElementById('profesores');
        
        if (idAntiguoInput) idAntiguoInput.value = res.id || '';
        if (idEspecialidadInput) idEspecialidadInput.value = res.id || '';
        if (idDepartamentoInput) idDepartamentoInput.value = res.idDepartamento || '';
        if (descripcionTextarea) descripcionTextarea.value = res.descripcion || '';
        if (horasTutoriaInput) horasTutoriaInput.value = res.horasTutoria || '';
        if (horasInglesInput) horasInglesInput.value = res.horasIngles || '';
        if (profesoresTextarea) profesoresTextarea.value = res.profesores || '';
        
        const formespecialidadModal = document.getElementById('formespecialidad');
        if (formespecialidadModal) {
            const modal = new bootstrap.Modal(formespecialidadModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar especialidad:', error);
        mostrarMensaje("Error al cargar los datos de la especialidad", 0);
    });
}

// Abre el diálogo modal para insertar una nueva especialidad
function nuevaEspecialidad()
{
    if (selDepartamento > 0)
    {
        limpiarFormularioEspecialidades();
        const formespecialidadModal = document.getElementById('formespecialidad');
        if (formespecialidadModal) {
            const modal = new bootstrap.Modal(formespecialidadModal);
            modal.show();
        }
    } else {
        mostrarMensaje("Debes seleccionar un departamento", 0);
    }
}

// Borra la especialidad especificada
function borrarEspecialidad(id)
{
    if (confirm("Confirmas el borrado de la especialidad '" + id + "'?"))
    {
        fetch('ajax/especialidades/borrar_especialidad.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.text())
        .then(res => {
            if (res.trim() == 'si') {
                mostrarMensaje("Error al borrar la especialidad", 0);
            }
            cargarEspecialidades();
        })
        .catch(error => {
            console.error('Error al borrar especialidad:', error);
            mostrarMensaje("Error al borrar la especialidad", 0);
        });
    }
}

// Borra los datos del formulario modal de alta/edición de especialidad
function limpiarFormularioEspecialidades()
{
    const idAntiguoInput = document.getElementById('idAntiguo');
    const idEspecialidadInput = document.getElementById('idEspecialidad');
    const descripcionTextarea = document.getElementById('descripcion');
    const horasTutoriaInput = document.getElementById('horasTutoria');
    const horasInglesInput = document.getElementById('horasIngles');
    const profesoresTextarea = document.getElementById('profesores');
    
    if (idAntiguoInput) idAntiguoInput.value = "";
    if (idEspecialidadInput) idEspecialidadInput.value = "";
    if (descripcionTextarea) descripcionTextarea.value = "";
    if (horasTutoriaInput) horasTutoriaInput.value = "";
    if (horasInglesInput) horasInglesInput.value = "";
    if (profesoresTextarea) profesoresTextarea.value = "";
}

// Evento de envío del formulario modal de especialidad
const formespForm = document.getElementById('formesp');
if (formespForm) {
    formespForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formespForm);
        
        fetch('ajax/especialidades/insertar_especialidad.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(res => {
            // Recogemos respuesta y mostramos resultado en ventana modal de mensaje
            limpiarFormularioEspecialidades();
            const formespecialidadModal = document.getElementById('formespecialidad');
            if (formespecialidadModal) {
                const modal = bootstrap.Modal.getInstance(formespecialidadModal);
                if (modal) {
                    modal.hide();
                }
            }
            if (res.trim().startsWith('si'))
                mostrarMensaje("Error al realizar la operación solicitada: " + res.trim().substring(2), 0);
            else
                mostrarMensaje("Operación realizada correctamente", 1);
            // Actualizamos las especialidades
            cargarEspecialidades();
        })
        .catch(error => {
            console.error('Error al guardar especialidad:', error);
            mostrarMensaje("Error al guardar la especialidad", 0);
        });
    });
}
