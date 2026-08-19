// Funciones para gestión de materias desde la vista "materias.php"

// Variable donde almacenamos el curso actualmente seleccionado
var selCurso = 0;

// Cambia la selección de curso actual
function seleccionarCursoMateria()
{
    selCurso  = document.getElementById('cursosmaterias').value;
    document.getElementById('idCurso').value = selCurso;
    cargarMaterias();
}

// Carga el listado de materias del curso indicado en el "div" habilitado para ello
function cargarMaterias()
{
    document.getElementById("listamaterias").load("ajax/materias/cargar_materias.php", {idCurso: selCurso});
}

// Carga los datos de la materia indicada en el formulario modal
function cargarMateriaModal(id)
{
    fetch("ajax/materias/cargar_materia.php?" + new URLSearchParams({idMateria:id}).toString()).then(r => r.json()).then(res => {
        document.getElementById('idMateria').value = id;
        document.getElementById('idCurso').value = res.idCurso;
        document.getElementById('nombre').value = res.nombre;
        document.getElementById('codigoOficial').value = res.codigo_oficial;
        document.getElementById('nombreOficial').value = res.nombre_oficial;
        document.getElementById('creditosECTS').value = res.creditos_ects;
        document.getElementById('horasAnuales').value = res.horas_anuales;
        document.getElementById('cantidad').value = res.cantidad;
        document.getElementById('horas').value = res.horas;
        document.getElementById('horasComplementarias').value = res.horas_complementarias;
        document.getElementById('tipo').value = res.tipo;
        document.getElementById('departamento').value = res.idDepartamento;
        cargarEspecialidades(res.idEspecialidad);
        if (res.computables_horas_grupo == 1)
            document.getElementById('computablesHorasGrupo').checked = true;
        else
            document.getElementById('computablesHorasGrupo').checked = false;
        if (res.asignada_directiva == 1)
            document.getElementById('asignadaDirectiva').checked = true;
        else
            document.getElementById('asignadaDirectiva').checked = false;
        document.getElementById('minNumProfesores').value = res.min_num_profesores;
        document.getElementById('maxGruposProfesor').value = res.max_grupos_profesor;
        if (res.tiene_programacion == 1)
            document.getElementById('tieneProgramacion').checked = true;
        else
            document.getElementById('tieneProgramacion').checked = false;
        if (res.divisible == 1)
            document.getElementById('divisible').checked = true;
        else
            document.getElementById('divisible').checked = false;

        (() => { const el = document.getElementById("formmateria"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Carga las especialidades del departamento seleccionado
function cargarEspecialidades(idEspecialidad)
{
    var selDepartamento = document.getElementById('departamento').value;
    if(selDepartamento != "")
    {
        // Primero cargamos las especialidades del departamento asociado
        fetch("ajax/especialidades/cargar_especialidades_json.php?" + new URLSearchParams({idDepartamento:selDepartamento}).toString()).then(r => r.json()).then(resEsp => {
            let resultado = JSON.parse(resEsp);
            // Accedemos al "select" de especialidad del formulario y rellenamos las opciones
            document.getElementById('especialidad').innerHTML = '';
            // Añadimos una opción vacía inicial
            var option = document.createElement('option')
                .attr('value', '')
                .textContent = '--Selecciona una especialidad--';
            document.getElementById('especialidad').append($option);
            for(var i = 0; i < resultado.length; i++) {
                var option = document.createElement('option')
                    .attr('value', resultado[i].id)
                    .textContent = resultado[i].descripcion;
                document.getElementById('especialidad').append($option);
            }

            if(idEspecialidad)
            {
                document.getElementById('especialidad').value = idEspecialidad;
            }
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
        (() => { const el = document.getElementById("formmateria"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    }
}

// Borra la materia indicada, previa confirmación
function borrarMateria (id, nombre)
{
    if (confirm("Confirmas el borrado de la materia '" + nombre + "'?"))
    {
        fetch("ajax/materias/borrar_materia.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({id:id}).toString() }).then(r => r.text()).then(res => {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar la materia", 0);
            cargarMaterias();
        });            
    }
}

// Limpia los datos del formulario modal de materias
function limpiarFormularioMaterias()
{
    document.getElementById('idMateria').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('codigoOficial').value = '';
    document.getElementById('nombreOficial').value = '';
    document.getElementById('creditosECTS').value = '';
    document.getElementById('horasAnuales').value = '';
    document.getElementById('cantidad').value = "1";
    document.getElementById('horas').value = '';
    document.getElementById('horasComplementarias').value = '';
    document.getElementById('tipo').value = "OTRAS";
    document.getElementById('departamento').value = '';
    document.getElementById('especialidad').value = '';
    document.getElementById('computablesHorasGrupo').checked = true;
    document.getElementById('tieneProgramacion').checked = true;
    document.getElementById('divisible').checked = true;
    document.getElementById('asignadaDirectiva').checked = false;
    document.getElementById('minNumProfesores').value = "0";
    document.getElementById('maxGruposProfesor').value = "0";    
}

// Carga el formulario modal para editar los datos de la materia indicada para los distintos grupos
function cargarMateriasGrupos(idMateria, idCurso)
{
    document.getElementById('formsgrupos').load("ajax/materias/cargar_forms_materias_grupos.php", {idMateria:idMateria, idCurso: idCurso, importar: 0}, function()
    {
        (() => { const el = document.getElementById("formmateriagrupo"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });
}

// Importa en cada grupo los datos generales de la materia (cantidad de unidades, número de horas...)
// Es útil si queremos que todos los grupos tengan las mismas condiciones, o como punto de partida para
// luego editar un grupo en particular
function importarDatos(idMateria, idCurso)
{
    document.getElementById('formsgrupos').load("ajax/materias/cargar_forms_materias_grupos.php", {idMateria:idMateria, idCurso: idCurso, importar: 1});
}

// Carga el modal para asociar competencias (profesionales, etc) a la materia
function asociarCompetencias(idMateria)
{
    fetch("ajax/materias/cargar_competencias_materia.php?" + new URLSearchParams({idMateria: idMateria}).toString()).then(r => r.json()).then(res => {
        document.getElementById("competenciasMateria").innerHTML = res;
        (() => { const el = document.getElementById("formcommat"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Añade una nueva competencia a la materia indicada
function asociarCompetencia(idMateria)
{
    let idCompetencia = document.getElementById('idCompetencia').value;
    fetch("ajax/materias/nueva_competencia_materia.php?" + new URLSearchParams({idMateria: idMateria, idCompetencia: idCompetencia}).toString()).then(r => r.json()).then(res => {
        asociarCompetencias(idMateria);
    });    
}

// Quita una competencia de la materia indicada
function borrarCompetencia(idMateria, idCompetencia)
{
    fetch("ajax/materias/borrar_competencia_materia.php?" + new URLSearchParams({idMateria: idMateria, idCompetencia: idCompetencia}).toString()).then(r => r.json()).then(res => {
        asociarCompetencias(idMateria);
    });    
}

// Evento de envío del formulario para inserción/modificación

document.getElementById("formmat").addEventListener("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formmat);
    fetch("ajax/materias/insertar_materia.php", { method: "POST", body: formData })
    .then(function(res) {
        limpiarFormularioMaterias();
        (() => { const el = document.getElementById("formmateria"); const modal = bootstrap.Modal.getInstance(el); if(modal) modal.hide(); })();
        cargarMaterias();
    });
});
