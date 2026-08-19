// Funciones para gestión de grupos desde la vista "grupos.php"

// Variable donde almacenamos el curso actualmente seleccionado
var selCurso = 0;

// Se activa al cambiar el curso seleccionado
function seleccionarCursoGrupo()
{
    const cursosgruposSelect = document.getElementById('cursosgrupos');
    const idCursoInput = document.getElementById('idCurso');
    
    if (cursosgruposSelect) {
        selCurso = cursosgruposSelect.value;
        if (idCursoInput) {
            idCursoInput.value = selCurso;
        }
        cargarGrupos();
    }
}

// Carga los grupos del curso seleccionado actualmente
function cargarGrupos()
{
    fetch('ajax/grupos/cargar_grupos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idCurso=' + encodeURIComponent(selCurso)
    })
    .then(response => response.text())
    .then(res => {
        const listagruposDiv = document.getElementById('listagrupos');
        if (listagruposDiv) {
            listagruposDiv.innerHTML = res;
        }
    })
    .catch(error => {
        console.error('Error al cargar grupos:', error);
        mostrarMensaje("Error al cargar los grupos", 0);
    });
}

// Carga en el formulario modal los datos del grupo indicado
function cargarGrupoModal(id)
{
    fetch('ajax/grupos/cargar_grupo.php?idGrupo=' + encodeURIComponent(id))
    .then(response => response.json())
    .then(res => {
        const idGrupoInput = document.getElementById('idGrupo');
        const idCursoInput = document.getElementById('idCurso');
        const nombreInput = document.getElementById('nombre');
        const abreviaturaInput = document.getElementById('abreviatura');
        const mostrarCheckbox = document.getElementById('mostrar');
        const horasComplementariasDualInput = document.getElementById('horasComplementariasDual');
        
        if (idGrupoInput) idGrupoInput.value = id;
        if (idCursoInput) idCursoInput.value = res.idCurso || '';
        if (nombreInput) nombreInput.value = res.nombre || '';
        if (abreviaturaInput) abreviaturaInput.value = res.abreviatura || '';
        if (mostrarCheckbox) mostrarCheckbox.checked = (res.mostrar == 1);
        if (horasComplementariasDualInput) horasComplementariasDualInput.value = res.horas_complementarias_dual || '';

        const formgrupoModal = document.getElementById('formgrupo');
        if (formgrupoModal) {
            const modal = new bootstrap.Modal(formgrupoModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar grupo:', error);
        mostrarMensaje("Error al cargar los datos del grupo", 0);
    });
}

// Limpia el formulario modal para crear un nuevo grupo
function nuevoGrupo()
{
    if (selCurso <= 0)
    {
        mostrarMensaje("Debes seleccionar un curso primero", 0);
    } else {
        limpiarFormularioGrupos();
        const formgrupoModal = document.getElementById('formgrupo');
        if (formgrupoModal) {
            const modal = new bootstrap.Modal(formgrupoModal);
            modal.show();
        }
    }
}

// Elimina el grupo indicado, previa confirmación
function borrarGrupo(id, nombre)
{
    if (confirm("Confirmas el borrado del grupo '" + nombre + "'?"))
    {
        fetch('ajax/grupos/borrar_grupo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.text())
        .then(res => {
            if (res.trim() == 'si') {
                mostrarMensaje("Error al borrar el grupo", 0);
            }
            cargarGrupos();
        })
        .catch(error => {
            console.error('Error al borrar grupo:', error);
            mostrarMensaje("Error al borrar el grupo", 0);
        });
    }
}

// Borra los campos del formulario de grupos
function limpiarFormularioGrupos()
{
    const idGrupoInput = document.getElementById('idGrupo');
    const nombreInput = document.getElementById('nombre');
    const abreviaturaInput = document.getElementById('abreviatura');
    const horasComplementariasDualInput = document.getElementById('horasComplementariasDual');
    const mostrarCheckbox = document.getElementById('mostrar');
    
    if (idGrupoInput) idGrupoInput.value = "";
    if (nombreInput) nombreInput.value = "";
    if (abreviaturaInput) abreviaturaInput.value = "";
    if (horasComplementariasDualInput) horasComplementariasDualInput.value = "";
    if (mostrarCheckbox) mostrarCheckbox.checked = false;
}

// Evento para auto-ordenar los grupos
const listagruposDiv = document.getElementById('listagrupos');
if (listagruposDiv && typeof Sortable !== 'undefined') {
    new Sortable(listagruposDiv, {
        animation: 150,
        onEnd: function(evt) {
            // Se envían los datos en un string. Cada grupo con el prefijo "gr" y su código, separados por comas
            // En el servidor se procesa esa cadena, se parte y se le asigna un número de orden a cada grupo
            const items = listagruposDiv.querySelectorAll('.grupo');
            let elementos = [];
            items.forEach(item => {
                if (item.id) {
                    elementos.push(item.id);
                }
            });
            const ordenStr = elementos.join(',');
            
            fetch('ajax/grupos/ordenar_grupos.php?orden=' + encodeURIComponent(ordenStr))
            .then(response => response.text())
            .then(res => {
                cargarGrupos();
            })
            .catch(error => {
                console.error('Error al ordenar grupos:', error);
            });
        }
    });
}

// Evento de envío del formulario modal para insertar/modificar grupos
const formgrupForm = document.getElementById('formgrup');
if (formgrupForm) {
    formgrupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formgrupForm);
        
        fetch('ajax/grupos/insertar_grupo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(res => {
            limpiarFormularioGrupos();
            const formgrupoModal = document.getElementById('formgrupo');
            if (formgrupoModal) {
                const modal = bootstrap.Modal.getInstance(formgrupoModal);
                if (modal) {
                    modal.hide();
                }
            }
            cargarGrupos();
        })
        .catch(error => {
            console.error('Error al guardar grupo:', error);
            mostrarMensaje("Error al guardar el grupo", 0);
        });
    });
}
