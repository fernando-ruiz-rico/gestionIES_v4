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
    $("#listamaterias").load("ajax/materias/cargar_materias.php", {idCurso: selCurso});
}

// Carga los datos de la materia indicada en el formulario modal
function cargarMateriaModal(id)
{
    fetch("ajax/materias/cargar_materia.php?" + new URLSearchParams(idMateria:id)).then(response => response.text()).then(res => {
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
            document.getElementById('computablesHorasGrupo').prop('checked', true);
        else
            document.getElementById('computablesHorasGrupo').prop('checked', false);
        if (res.asignada_directiva == 1)
            document.getElementById('asignadaDirectiva').prop('checked', true);
        else
            document.getElementById('asignadaDirectiva').prop('checked', false);
        document.getElementById('minNumProfesores').value = res.min_num_profesores;
        document.getElementById('maxGruposProfesor').value = res.max_grupos_profesor;
        if (res.tiene_programacion == 1)
            document.getElementById('tieneProgramacion').prop('checked', true);
        else
            document.getElementById('tieneProgramacion').prop('checked', false);
        if (res.divisible == 1)
            document.getElementById('divisible').prop('checked', true);
        else
            document.getElementById('divisible').prop('checked', false);

        $("#formmateria").show();
    });    
}

// Carga las especialidades del departamento seleccionado
function cargarEspecialidades(idEspecialidad)
{
    var selDepartamento = document.getElementById('departamento').value;
    if(selDepartamento != "")
    {
        // Primero cargamos las especialidades del departamento asociado
        fetch("ajax/especialidades/cargar_especialidades_json.php?" + new URLSearchParams(idDepartamento:selDepartamento)).then(response => response.text()).then(resEsp => {
            let resultado = JSON.parse(resEsp);
            // Accedemos al "select" de especialidad del formulario y rellenamos las opciones
            document.getElementById('especialidad').innerHTML = '';
            // Añadimos una opción vacía inicial
            var $option = $('<option></option>')
                .attr('value', '')
                .textContent = '--Selecciona una especialidad--';
            document.getElementById('especialidad').append($option);
            for(var i = 0; i < resultado.length; i++) {
                var $option = $('<option></option>')
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
        document.getElementById('formmateria').show();
    }
}

// Borra la materia indicada, previa confirmación
function borrarMateria (id, nombre)
{
    if (confirm("Confirmas el borrado de la materia '" + nombre + "'?"))
    {
        $.post("ajax/materias/borrar_materia.php", {id:id}, function(res)
        {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar la materia", 0);
            cargarMaterias();
        });            
    }
}

// Limpia los datos del formulario modal de materias
function limpiarFormularioMaterias()
{
    document.getElementById('idMateria').value = "";
    document.getElementById('nombre').value = "";
    document.getElementById('codigoOficial').value = "";
    document.getElementById('nombreOficial').value = "";
    document.getElementById('creditosECTS').value = "";
    document.getElementById('horasAnuales').value = "";
    document.getElementById('cantidad').value = "1";
    document.getElementById('horas').value = "";
    document.getElementById('horasComplementarias').value = "";
    document.getElementById('tipo').value = "OTRAS";
    document.getElementById('departamento').value = "";
    document.getElementById('especialidad').value = "";
    document.getElementById('computablesHorasGrupo').prop('checked', true);
    document.getElementById('tieneProgramacion').prop('checked', true);
    document.getElementById('divisible').prop('checked', true);
    document.getElementById('asignadaDirectiva').prop('checked', false);
    document.getElementById('minNumProfesores').value = "0";
    document.getElementById('maxGruposProfesor').value = "0";    
}

// Carga el formulario modal para editar los datos de la materia indicada para los distintos grupos
function cargarMateriasGrupos(idMateria, idCurso)
{
    document.getElementById('formsgrupos').load("ajax/materias/cargar_forms_materias_grupos.php", {idMateria:idMateria, idCurso: idCurso, importar: 0}, function()
    {
        document.getElementById('formmateriagrupo').show();
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
    fetch("ajax/materias/cargar_competencias_materia.php?" + new URLSearchParams(idMateria: idMateria)).then(response => response.text()).then(res => {
        $("#competenciasMateria").innerHTML = res;
        $("#formcommat").show();
    });    
}

// Añade una nueva competencia a la materia indicada
function asociarCompetencia(idMateria)
{
    let idCompetencia = document.getElementById('idCompetencia').value;
    fetch("ajax/materias/nueva_competencia_materia.php?" + new URLSearchParams(idMateria: idMateria, idCompetencia: idCompetencia)).then(response => response.text()).then(res => {
        asociarCompetencias(idMateria);
    });    
}

// Quita una competencia de la materia indicada
function borrarCompetencia(idMateria, idCompetencia)
{
    fetch("ajax/materias/borrar_competencia_materia.php?" + new URLSearchParams(idMateria: idMateria, idCompetencia: idCompetencia)).then(response => response.text()).then(res => {
        asociarCompetencias(idMateria);
    });    
}

// Evento de envío del formulario para inserción/modificación

$("#formmat").addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(document.forms.formmat);
    $.ajax({
        url: "ajax/materias/insertar_materia.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioMaterias();
        document.getElementById('formmateria').hide();
        cargarMaterias();
    });
});
