// Funciones para gestión de materias desde la vista "materias.php"

// Variable donde almacenamos el curso actualmente seleccionado
var selCurso = 0;

// Cambia la selección de curso actual
function seleccionarCursoMateria()
{
    const cursosmateriasSelect = document.getElementById('cursosmaterias');
    const idCursoInput = document.getElementById('idCurso');
    
    if (cursosmateriasSelect) {
        selCurso = cursosmateriasSelect.value;
        if (idCursoInput) {
            idCursoInput.value = selCurso;
        }
        cargarMaterias();
    }
}

// Carga el listado de materias del curso indicado en el "div" habilitado para ello
function cargarMaterias()
{
    fetch('ajax/materias/cargar_materias.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idCurso=' + encodeURIComponent(selCurso)
    })
    .then(response => response.text())
    .then(res => {
        const listamateriasDiv = document.getElementById('listamaterias');
        if (listamateriasDiv) {
            listamateriasDiv.innerHTML = res;
        }
    })
    .catch(error => {
        console.error('Error al cargar materias:', error);
        mostrarMensaje("Error al cargar las materias", 0);
    });
}

// Carga los datos de la materia indicada en el formulario modal
function cargarMateriaModal(id)
{
    fetch('ajax/materias/cargar_materia.php?idMateria=' + encodeURIComponent(id))
    .then(response => response.json())
    .then(res => {
        const idMateriaInput = document.getElementById('idMateria');
        const idCursoInput = document.getElementById('idCurso');
        const nombreInput = document.getElementById('nombre');
        const codigoOficialInput = document.getElementById('codigoOficial');
        const nombreOficialInput = document.getElementById('nombreOficial');
        const creditosECTSInput = document.getElementById('creditosECTS');
        const horasAnualesInput = document.getElementById('horasAnuales');
        const cantidadInput = document.getElementById('cantidad');
        const horasInput = document.getElementById('horas');
        const horasComplementariasInput = document.getElementById('horasComplementarias');
        const tipoSelect = document.getElementById('tipo');
        const departamentoSelect = document.getElementById('departamento');
        const computablesHorasGrupoCheckbox = document.getElementById('computablesHorasGrupo');
        const asignadaDirectivaCheckbox = document.getElementById('asignadaDirectiva');
        const minNumProfesoresInput = document.getElementById('minNumProfesores');
        const maxGruposProfesorInput = document.getElementById('maxGruposProfesor');
        const tieneProgramacionCheckbox = document.getElementById('tieneProgramacion');
        const divisibleCheckbox = document.getElementById('divisible');
        
        if (idMateriaInput) idMateriaInput.value = id;
        if (idCursoInput) idCursoInput.value = res.idCurso || '';
        if (nombreInput) nombreInput.value = res.nombre || '';
        if (codigoOficialInput) codigoOficialInput.value = res.codigo_oficial || '';
        if (nombreOficialInput) nombreOficialInput.value = res.nombre_oficial || '';
        if (creditosECTSInput) creditosECTSInput.value = res.creditos_ects || '';
        if (horasAnualesInput) horasAnualesInput.value = res.horas_anuales || '';
        if (cantidadInput) cantidadInput.value = res.cantidad || '';
        if (horasInput) horasInput.value = res.horas || '';
        if (horasComplementariasInput) horasComplementariasInput.value = res.horas_complementarias || '';
        if (tipoSelect) tipoSelect.value = res.tipo || '';
        if (departamentoSelect) {
            departamentoSelect.value = res.idDepartamento || '';
            cargarEspecialidades(res.idEspecialidad);
        }
        if (computablesHorasGrupoCheckbox) computablesHorasGrupoCheckbox.checked = (res.computables_horas_grupo == 1);
        if (asignadaDirectivaCheckbox) asignadaDirectivaCheckbox.checked = (res.asignada_directiva == 1);
        if (minNumProfesoresInput) minNumProfesoresInput.value = res.min_num_profesores || '';
        if (maxGruposProfesorInput) maxGruposProfesorInput.value = res.max_grupos_profesor || '';
        if (tieneProgramacionCheckbox) tieneProgramacionCheckbox.checked = (res.tiene_programacion == 1);
        if (divisibleCheckbox) divisibleCheckbox.checked = (res.divisible == 1);

        const formmateriaModal = document.getElementById('formmateria');
        if (formmateriaModal) {
            const modal = new bootstrap.Modal(formmateriaModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar materia:', error);
        mostrarMensaje("Error al cargar los datos de la materia", 0);
    });
}

// Carga las especialidades del departamento seleccionado
function cargarEspecialidades(idEspecialidad)
{
    const departamentoSelect = document.getElementById('departamento');
    if (!departamentoSelect) return;
    
    const selDepartamento = departamentoSelect.value;
    if (selDepartamento != "")
    {
        // Primero cargamos las especialidades del departamento asociado
        fetch('ajax/especialidades/cargar_especialidades_json.php?idDepartamento=' + encodeURIComponent(selDepartamento))
        .then(response => response.json())
        .then(resultado => {
            // Accedemos al "select" de especialidad del formulario y rellenamos las opciones
            const especialidadSelect = document.getElementById('especialidad');
            if (!especialidadSelect) return;
            
            especialidadSelect.innerHTML = '';
            // Añadimos una opción vacía inicial
            const $option = document.createElement('option');
            $option.value = '';
            $option.textContent = '--Selecciona una especialidad--';
            especialidadSelect.appendChild($option);
            
            for (let i = 0; i < resultado.length; i++) {
                const option = document.createElement('option');
                option.value = resultado[i].id;
                option.textContent = resultado[i].descripcion;
                especialidadSelect.appendChild(option);
            }

            if (idEspecialidad)
            {
                especialidadSelect.value = idEspecialidad;
            }
        })
        .catch(error => {
            console.error('Error al cargar especialidades:', error);
        });
    }
}

// Limpia el formulario modal para dar de alta una nueva materia
function nuevaMateria()
{
    if (selCurso <= 0)
    {
        mostrarMensaje("Debes seleccionar un curso primero", 2);
    } else {
        limpiarFormularioMaterias();
        const formmateriaModal = document.getElementById('formmateria');
        if (formmateriaModal) {
            const modal = new bootstrap.Modal(formmateriaModal);
            modal.show();
        }
    }
}

// Borra la materia indicada, previa confirmación
function borrarMateria(id, nombre)
{
    if (confirm("Confirmas el borrado de la materia '" + nombre + "'?"))
    {
        fetch('ajax/materias/borrar_materia.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.text())
        .then(res => {
            if (res.trim() == 'si') {
                mostrarMensaje("Error al borrar la materia", 0);
            }
            cargarMaterias();
        })
        .catch(error => {
            console.error('Error al borrar materia:', error);
            mostrarMensaje("Error al borrar la materia", 0);
        });
    }
}

// Limpia los datos del formulario modal de materias
function limpiarFormularioMaterias()
{
    const idMateriaInput = document.getElementById('idMateria');
    const nombreInput = document.getElementById('nombre');
    const codigoOficialInput = document.getElementById('codigoOficial');
    const nombreOficialInput = document.getElementById('nombreOficial');
    const creditosECTSInput = document.getElementById('creditosECTS');
    const horasAnualesInput = document.getElementById('horasAnuales');
    const cantidadInput = document.getElementById('cantidad');
    const horasInput = document.getElementById('horas');
    const horasComplementariasInput = document.getElementById('horasComplementarias');
    const tipoSelect = document.getElementById('tipo');
    const departamentoSelect = document.getElementById('departamento');
    const especialidadSelect = document.getElementById('especialidad');
    const computablesHorasGrupoCheckbox = document.getElementById('computablesHorasGrupo');
    const tieneProgramacionCheckbox = document.getElementById('tieneProgramacion');
    const divisibleCheckbox = document.getElementById('divisible');
    const asignadaDirectivaCheckbox = document.getElementById('asignadaDirectiva');
    const minNumProfesoresInput = document.getElementById('minNumProfesores');
    const maxGruposProfesorInput = document.getElementById('maxGruposProfesor');
    
    if (idMateriaInput) idMateriaInput.value = "";
    if (nombreInput) nombreInput.value = "";
    if (codigoOficialInput) codigoOficialInput.value = "";
    if (nombreOficialInput) nombreOficialInput.value = "";
    if (creditosECTSInput) creditosECTSInput.value = "";
    if (horasAnualesInput) horasAnualesInput.value = "";
    if (cantidadInput) cantidadInput.value = "1";
    if (horasInput) horasInput.value = "";
    if (horasComplementariasInput) horasComplementariasInput.value = "";
    if (tipoSelect) tipoSelect.value = "OTRAS";
    if (departamentoSelect) departamentoSelect.value = "";
    if (especialidadSelect) especialidadSelect.value = "";
    if (computablesHorasGrupoCheckbox) computablesHorasGrupoCheckbox.checked = true;
    if (tieneProgramacionCheckbox) tieneProgramacionCheckbox.checked = true;
    if (divisibleCheckbox) divisibleCheckbox.checked = true;
    if (asignadaDirectivaCheckbox) asignadaDirectivaCheckbox.checked = false;
    if (minNumProfesoresInput) minNumProfesoresInput.value = "0";
    if (maxGruposProfesorInput) maxGruposProfesorInput.value = "0";
}

// Carga el formulario modal para editar los datos de la materia indicada para los distintos grupos
function cargarMateriasGrupos(idMateria, idCurso)
{
    fetch('ajax/materias/cargar_forms_materias_grupos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idMateria=' + encodeURIComponent(idMateria) + '&idCurso=' + encodeURIComponent(idCurso) + '&importar=0'
    })
    .then(response => response.text())
    .then(res => {
        const formsgruposDiv = document.getElementById('formsgrupos');
        if (formsgruposDiv) {
            formsgruposDiv.innerHTML = res;
        }
        const formmateriagrupoModal = document.getElementById('formmateriagrupo');
        if (formmateriagrupoModal) {
            const modal = new bootstrap.Modal(formmateriagrupoModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar formularios de grupos:', error);
        mostrarMensaje("Error al cargar los formularios de grupos", 0);
    });
}

// Importa en cada grupo los datos generales de la materia (cantidad de unidades, número de horas...)
// Es útil si queremos que todos los grupos tengan las mismas condiciones, o como punto de partida para
// luego editar un grupo en particular
function importarDatos(idMateria, idCurso)
{
    fetch('ajax/materias/cargar_forms_materias_grupos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'idMateria=' + encodeURIComponent(idMateria) + '&idCurso=' + encodeURIComponent(idCurso) + '&importar=1'
    })
    .then(response => response.text())
    .then(res => {
        const formsgruposDiv = document.getElementById('formsgrupos');
        if (formsgruposDiv) {
            formsgruposDiv.innerHTML = res;
        }
    })
    .catch(error => {
        console.error('Error al importar datos:', error);
    });
}

// Carga el modal para asociar competencias (profesionales, etc) a la materia
function asociarCompetencias(idMateria)
{
    fetch('ajax/materias/cargar_competencias_materia.php?idMateria=' + encodeURIComponent(idMateria))
    .then(response => response.text())
    .then(res => {
        const competenciasMateriaDiv = document.getElementById('competenciasMateria');
        if (competenciasMateriaDiv) {
            competenciasMateriaDiv.innerHTML = res;
        }
        const formcommatModal = document.getElementById('formcommat');
        if (formcommatModal) {
            const modal = new bootstrap.Modal(formcommatModal);
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al cargar competencias:', error);
        mostrarMensaje("Error al cargar las competencias", 0);
    });
}

// Añade una nueva competencia a la materia indicada
function asociarCompetencia(idMateria)
{
    const idCompetenciaInput = document.getElementById('idCompetencia');
    if (!idCompetenciaInput) return;
    
    const idCompetencia = idCompetenciaInput.value;
    fetch('ajax/materias/nueva_competencia_materia.php?idMateria=' + encodeURIComponent(idMateria) + '&idCompetencia=' + encodeURIComponent(idCompetencia))
    .then(response => response.text())
    .then(res => {
        asociarCompetencias(idMateria);
    })
    .catch(error => {
        console.error('Error al añadir competencia:', error);
        mostrarMensaje("Error al añadir la competencia", 0);
    });
}

// Quita una competencia de la materia indicada
function borrarCompetencia(idMateria, idCompetencia)
{
    fetch('ajax/materias/borrar_competencia_materia.php?idMateria=' + encodeURIComponent(idMateria) + '&idCompetencia=' + encodeURIComponent(idCompetencia))
    .then(response => response.text())
    .then(res => {
        asociarCompetencias(idMateria);
    })
    .catch(error => {
        console.error('Error al borrar competencia:', error);
        mostrarMensaje("Error al borrar la competencia", 0);
    });
}

// Evento de envío del formulario para inserción/modificación
const formmatForm = document.getElementById('formmat');
if (formmatForm) {
    formmatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formmatForm);
        
        fetch('ajax/materias/insertar_materia.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(res => {
            limpiarFormularioMaterias();
            const formmateriaModal = document.getElementById('formmateria');
            if (formmateriaModal) {
                const modal = bootstrap.Modal.getInstance(formmateriaModal);
                if (modal) {
                    modal.hide();
                }
            }
            cargarMaterias();
        })
        .catch(error => {
            console.error('Error al guardar materia:', error);
            mostrarMensaje("Error al guardar la materia", 0);
        });
    });
}
