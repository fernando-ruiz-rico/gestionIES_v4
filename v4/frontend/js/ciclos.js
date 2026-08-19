// Funciones para gestión de ciclos desde la vista "ciclos.php"

// Carga el listado de ciclos en el "div" habilitado para ello
function cargarCiclos()
{
    fetch('ajax/ciclos/cargar_ciclos.php')
    .then(response => response.text())
    .then(res => {
        const listaciclos = document.getElementById('listaciclos');
        if (listaciclos) {
            listaciclos.innerHTML = res;
        }
    })
    .catch(error => {
        console.error('Error al cargar ciclos:', error);
        mostrarMensaje("Error al cargar los ciclos", 0);
    });
}

// Muestra los datos del ciclo indicado en el formulario modal, para su edición
function cargarCicloModal(id)
{
    fetch('ajax/ciclos/cargar_ciclo.php?idCiclo=' + encodeURIComponent(id))
    .then(response => response.json())
    .then(res => {
        const idCicloInput = document.getElementById('idCiclo');
        const nombreInput = document.getElementById('nombre');
        const familiaInput = document.getElementById('familia');
        const nivelSelect = document.getElementById('nivel');
        
        if (idCicloInput) idCicloInput.value = id;
        if (nombreInput) nombreInput.value = res.nombre || '';
        if (familiaInput) familiaInput.value = res.familia || '';
        if (nivelSelect) nivelSelect.value = res.nivel || '';
        
        const formcicloModal = document.getElementById('formciclo');
        if (formcicloModal) {
            const modal = new bootstrap.Modal(formcicloModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar ciclo:', error);
        mostrarMensaje("Error al cargar los datos del ciclo", 0);
    });
}

// Muestra el formulario modal limpio para insertar un nuevo ciclo
function nuevoCiclo()
{
    limpiarFormularioCiclos();
    const formcicloModal = document.getElementById('formciclo');
    if (formcicloModal) {
        const modal = new bootstrap.Modal(formcicloModal);
        modal.show();
    }
}

// Borra el ciclo indicado, previa confirmación
// El ciclo sólo podrá borrarse si no tiene vinculaciones con otras tablas importantes
function borrarCiclo(id, nombre)
{
    if (confirm("Confirmas el borrado del ciclo '" + nombre + "'? Sólo se podrá eliminar si no tiene cursos asociados. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch('ajax/ciclos/borrar_ciclo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.text())
        .then(res => {
            if (res.trim() == 'si') {
                mostrarMensaje("Error al borrar el ciclo. Asegúrate de que no tenga cursos asociados", 0);
            } else {
                cargarCiclos();
            }
        })
        .catch(error => {
            console.error('Error al borrar ciclo:', error);
            mostrarMensaje("Error al borrar el ciclo", 0);
        });
    }
}

// Borra el contenido de los campos del formulario modal de alta/edición de ciclos
function limpiarFormularioCiclos()
{
    const idCicloInput = document.getElementById('idCiclo');
    const nombreInput = document.getElementById('nombre');
    const familiaInput = document.getElementById('familia');
    const nivelSelect = document.getElementById('nivel');
    
    if (idCicloInput) idCicloInput.value = "";
    if (nombreInput) nombreInput.value = "";
    if (familiaInput) familiaInput.value = "";
    if (nivelSelect) nivelSelect.value = "";
}

// Asocia unidades de competencia a un ciclo
function asociarUnidades(idCiclo)
{
    fetch('ajax/ciclos/cargar_asociaciones_unidades.php?idCiclo=' + encodeURIComponent(idCiclo))
    .then(response => response.text())
    .then(res => {
        const asociacionesDiv = document.getElementById('asociaciones');
        if (asociacionesDiv) {
            asociacionesDiv.innerHTML = res;
        }
        const formunicicModal = document.getElementById('formunicic');
        if (formunicicModal) {
            const modal = new bootstrap.Modal(formunicicModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar asociaciones:', error);
        mostrarMensaje("Error al cargar las asociaciones", 0);
    });
}

// Añade una nueva asociación de unidad de competencia a un ciclo
function nuevaAsociacion(idCiclo)
{
    const codigoAsociacionInput = document.getElementById('codigoAsociacion');
    if (!codigoAsociacionInput) return;
    
    const codigoUnidad = codigoAsociacionInput.value;
    if (codigoUnidad != "")
    {
        fetch('ajax/ciclos/nueva_asociacion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'idCiclo=' + encodeURIComponent(idCiclo) + '&codigoUnidad=' + encodeURIComponent(codigoUnidad)
        })
        .then(response => response.text())
        .then(res => {
            asociarUnidades(idCiclo);
        })
        .catch(error => {
            console.error('Error al añadir asociación:', error);
            mostrarMensaje("Error al añadir la asociación", 0);
        });
    }
}

// Elimina una asociación de unidad de competencia a ciclo
function borrarAsociacion(idCiclo, codigoUnidad)
{
    fetch('ajax/ciclos/borrar_asociacion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idCiclo=' + encodeURIComponent(idCiclo) + '&codigoUnidad=' + encodeURIComponent(codigoUnidad)
    })
    .then(response => response.text())
    .then(res => {
        asociarUnidades(idCiclo);
    })
    .catch(error => {
        console.error('Error al borrar asociación:', error);
        mostrarMensaje("Error al borrar la asociación", 0);
    });
}

// Asocia cursos a un ciclo
function asociarCursos(idCiclo)
{
    fetch('ajax/ciclos/cargar_asociaciones_cursos.php?idCiclo=' + encodeURIComponent(idCiclo))
    .then(response => response.text())
    .then(res => {
        const asociacionesCursosDiv = document.getElementById('asociacionesCursos');
        if (asociacionesCursosDiv) {
            asociacionesCursosDiv.innerHTML = res;
        }
        const formcurcicModal = document.getElementById('formcurcic');
        if (formcurcicModal) {
            const modal = new bootstrap.Modal(formcurcicModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar cursos:', error);
        mostrarMensaje("Error al cargar los cursos", 0);
    });
}

// Borra una asociación de curso con ciclo
function borrarCurso(idCiclo, idCurso)
{
    fetch('ajax/ciclos/borrar_curso_ciclo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idCiclo=' + encodeURIComponent(idCiclo) + '&idCurso=' + encodeURIComponent(idCurso)
    })
    .then(response => response.text())
    .then(res => {
        asociarCursos(idCiclo);
    })
    .catch(error => {
        console.error('Error al borrar curso:', error);
        mostrarMensaje("Error al borrar el curso", 0);
    });
}

// Actualiza los datos de un curso en el ciclo
function actualizarCurso(idCiclo, idCurso)
{
    const ordenInput = document.getElementById('orden' + idCurso);
    if (!ordenInput) return;
    
    const orden = ordenInput.value;

    fetch('ajax/ciclos/actualizar_curso_ciclo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idCiclo=' + encodeURIComponent(idCiclo) + '&idCurso=' + encodeURIComponent(idCurso) + '&orden=' + encodeURIComponent(orden)
    })
    .then(response => response.text())
    .then(res => {
        asociarCursos(idCiclo);
    })
    .catch(error => {
        console.error('Error al actualizar curso:', error);
        mostrarMensaje("Error al actualizar el curso", 0);
    });
}

// Añade un nuevo curso al ciclo
function nuevoCurso(idCiclo)
{
    const codigoAsociacionCursoInput = document.getElementById('codigoAsociacionCurso');
    const ordenInput = document.getElementById('orden');
    
    if (!codigoAsociacionCursoInput || !ordenInput) return;
    
    const idCurso = codigoAsociacionCursoInput.value;
    const orden = ordenInput.value;

    fetch('ajax/ciclos/insertar_curso_ciclo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idCiclo=' + encodeURIComponent(idCiclo) + '&idCurso=' + encodeURIComponent(idCurso) + '&orden=' + encodeURIComponent(orden)
    })
    .then(response => response.text())
    .then(res => {
        asociarCursos(idCiclo);
    })
    .catch(error => {
        console.error('Error al insertar curso:', error);
        mostrarMensaje("Error al insertar el curso", 0);
    });
}

// Evento de envío del formulario modal para inserción/modificación
const formcicForm = document.getElementById('formcic');
if (formcicForm) {
    formcicForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formcicForm);
        
        fetch('ajax/ciclos/insertar_ciclo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(res => {
            limpiarFormularioCiclos();
            const formcicloModal = document.getElementById('formciclo');
            if (formcicloModal) {
                const modal = bootstrap.Modal.getInstance(formcicloModal);
                if (modal) {
                    modal.hide();
                }
            }
            cargarCiclos();
        })
        .catch(error => {
            console.error('Error al guardar ciclo:', error);
            mostrarMensaje("Error al guardar el ciclo", 0);
        });
    });
}
