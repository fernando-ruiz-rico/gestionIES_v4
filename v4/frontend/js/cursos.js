// Funciones para gestión de cursos desde la vista "cursos.php"

// Carga el listado de cursos en el "div" habilitado para ello
function cargarCursos()
{
    fetch('ajax/cursos/cargar_cursos.php')
    .then(response => response.text())
    .then(res => {
        const listacursos = document.getElementById('listacursos');
        if (listacursos) {
            listacursos.innerHTML = res;
        }
    })
    .catch(error => {
        console.error('Error al cargar cursos:', error);
        mostrarMensaje("Error al cargar los cursos", 0);
    });
}

// Muestra los datos del curso indicado en el formulario modal, para su edición
function cargarCursoModal(id)
{
    fetch('ajax/cursos/cargar_curso.php?idCurso=' + encodeURIComponent(id))
    .then(response => response.json())
    .then(res => {
        const idCursoInput = document.getElementById('idCurso');
        const nombreInput = document.getElementById('nombre');
        const abreviaturaInput = document.getElementById('abreviatura');
        const horasSemanaInput = document.getElementById('horasSemana');
        const categoriaSelect = document.getElementById('categoria');
        
        if (idCursoInput) idCursoInput.value = id;
        if (nombreInput) nombreInput.value = res.nombre || '';
        if (abreviaturaInput) abreviaturaInput.value = res.abreviatura || '';
        if (horasSemanaInput) horasSemanaInput.value = res.horas_semana || '';
        if (categoriaSelect) categoriaSelect.value = res.categoria || '';
        
        const formcursoModal = document.getElementById('formcurso');
        if (formcursoModal) {
            const modal = new bootstrap.Modal(formcursoModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar curso:', error);
        mostrarMensaje("Error al cargar los datos del curso", 0);
    });
}

// Muestra el formulario modal limpio para insertar un nuevo curso
function nuevoCurso()
{
    limpiarFormularioCursos();
    const formcursoModal = document.getElementById('formcurso');
    if (formcursoModal) {
        const modal = new bootstrap.Modal(formcursoModal);
        modal.show();
    }
}

// Borra el curso indicado, previa confirmación
// El curso sólo podrá borrarse si no tiene vinculaciones con otras tablas importantes
function borrarCurso(id, nombre)
{
    if (confirm("Confirmas el borrado del curso '" + nombre + "'? Sólo se podrá eliminar si no tiene grupos ni materias asociadas. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch('ajax/cursos/borrar_curso.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.text())
        .then(res => {
            if (res.trim() == 'si') {
                mostrarMensaje("Error al borrar el curso. Asegúrate de que no tenga grupos o materias asociados", 0);
            } else {
                cargarCursos();
            }
        })
        .catch(error => {
            console.error('Error al borrar curso:', error);
            mostrarMensaje("Error al borrar el curso", 0);
        });
    }
}

// Borra el contenido de los campos del formulario modal de alta/edición de cursos
function limpiarFormularioCursos()
{
    const idCursoInput = document.getElementById('idCurso');
    const nombreInput = document.getElementById('nombre');
    const abreviaturaInput = document.getElementById('abreviatura');
    const horasSemanaInput = document.getElementById('horasSemana');
    const categoriaSelect = document.getElementById('categoria');
    
    if (idCursoInput) idCursoInput.value = "";
    if (nombreInput) nombreInput.value = "";
    if (abreviaturaInput) abreviaturaInput.value = "";
    if (horasSemanaInput) horasSemanaInput.value = "";
    if (categoriaSelect) categoriaSelect.value = "";
}

// Evento de auto-ordenación sobre los items de la lista de cursos
const listacursosDiv = document.getElementById('listacursos');
if (listacursosDiv && typeof Sortable !== 'undefined') {
    new Sortable(listacursosDiv, {
        animation: 150,
        onEnd: function(evt) {
            // Recoge los elementos contenidos en el "div"
            const items = listacursosDiv.querySelectorAll('.curso');
            let elementos = [];
            items.forEach(item => {
                if (item.id) {
                    elementos.push(item.id);
                }
            });
            const ordenStr = elementos.join(',');
            
            // Invoca por AJAX al código PHP que ordena los cursos, pasándole los elementos a ordenar
            // Cada elemento se compone del prefijo "cu" seguido del código del curso, y los elementos
            // se envían separados por comas. La página PHP los recibe, trocea y procesa
            fetch('ajax/cursos/ordenar_cursos.php?orden=' + encodeURIComponent(ordenStr))
            .then(response => response.text())
            .then(res => {
                cargarCursos();
            })
            .catch(error => {
                console.error('Error al ordenar cursos:', error);
            });
        }
    });
}

// Evento de envío del formulario modal para inserción/modificación
const formcurForm = document.getElementById('formcur');
if (formcurForm) {
    formcurForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formcurForm);
        
        fetch('ajax/cursos/insertar_curso.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(res => {
            limpiarFormularioCursos();
            const formcursoModal = document.getElementById('formcurso');
            if (formcursoModal) {
                const modal = bootstrap.Modal.getInstance(formcursoModal);
                if (modal) {
                    modal.hide();
                }
            }
            cargarCursos();
        })
        .catch(error => {
            console.error('Error al guardar curso:', error);
            mostrarMensaje("Error al guardar el curso", 0);
        });
    });
}
