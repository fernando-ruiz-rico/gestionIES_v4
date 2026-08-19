// Funciones para gestión de materias desde la vista "materias.php"

// Variable donde almacenamos el curso actualmente seleccionado
var selCurso = 0;

// Cambia la selección de curso actual
function seleccionarCursoMateria()
{
    selCurso  = $('#cursosmaterias').val();
    $('#idCurso').val(selCurso);
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
    $.get("ajax/materias/cargar_materia.php", {idMateria:id}, function(res)
    {
        $('#idMateria').val(id);
        $('#idCurso').val(res.idCurso);
        $('#nombre').val(res.nombre);
        $('#codigoOficial').val(res.codigo_oficial);
        $('#nombreOficial').val(res.nombre_oficial);
        $('#creditosECTS').val(res.creditos_ects);
        $('#horasAnuales').val(res.horas_anuales);
        $('#cantidad').val(res.cantidad);
        $('#horas').val(res.horas);
        $('#horasComplementarias').val(res.horas_complementarias);
        $('#tipo').val(res.tipo);
        $('#departamento').val(res.idDepartamento);
        cargarEspecialidades(res.idEspecialidad);
        if (res.computables_horas_grupo == 1)
            $('#computablesHorasGrupo').prop('checked', true);
        else
            $('#computablesHorasGrupo').prop('checked', false);
        if (res.asignada_directiva == 1)
            $('#asignadaDirectiva').prop('checked', true);
        else
            $('#asignadaDirectiva').prop('checked', false);
        $('#minNumProfesores').val(res.min_num_profesores);
        $('#maxGruposProfesor').val(res.max_grupos_profesor);
        if (res.tiene_programacion == 1)
            $('#tieneProgramacion').prop('checked', true);
        else
            $('#tieneProgramacion').prop('checked', false);
        if (res.divisible == 1)
            $('#divisible').prop('checked', true);
        else
            $('#divisible').prop('checked', false);

        $("#formmateria").modal('show');
    });    
}

// Carga las especialidades del departamento seleccionado
function cargarEspecialidades(idEspecialidad)
{
    var selDepartamento = $('#departamento').val();
    if(selDepartamento != "")
    {
        // Primero cargamos las especialidades del departamento asociado
        $.get("ajax/especialidades/cargar_especialidades_json.php", {idDepartamento:selDepartamento}, function(resEsp)
        {
            let resultado = JSON.parse(resEsp);
            // Accedemos al "select" de especialidad del formulario y rellenamos las opciones
            $('#especialidad').empty();
            // Añadimos una opción vacía inicial
            var $option = $('<option></option>')
                .attr('value', '')
                .text('--Selecciona una especialidad--');
            $('#especialidad').append($option);
            for(var i = 0; i < resultado.length; i++) {
                var $option = $('<option></option>')
                    .attr('value', resultado[i].id)
                    .text(resultado[i].descripcion);
                $('#especialidad').append($option);
            }

            if(idEspecialidad)
            {
                $('#especialidad').val(idEspecialidad);
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
        $('#formmateria').modal('show');
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
    $('#idMateria').val("");
    $('#nombre').val("");
    $('#codigoOficial').val("");
    $('#nombreOficial').val("");
    $('#creditosECTS').val("");
    $('#horasAnuales').val("");
    $('#cantidad').val("1");
    $('#horas').val("");
    $('#horasComplementarias').val("");
    $('#tipo').val("OTRAS");
    $('#departamento').val("");
    $('#especialidad').val("");
    $('#computablesHorasGrupo').prop('checked', true);
    $('#tieneProgramacion').prop('checked', true);
    $('#divisible').prop('checked', true);
    $('#asignadaDirectiva').prop('checked', false);
    $('#minNumProfesores').val("0");
    $('#maxGruposProfesor').val("0");    
}

// Carga el formulario modal para editar los datos de la materia indicada para los distintos grupos
function cargarMateriasGrupos(idMateria, idCurso)
{
    $('#formsgrupos').load("ajax/materias/cargar_forms_materias_grupos.php", {idMateria:idMateria, idCurso: idCurso, importar: 0}, function()
    {
        $('#formmateriagrupo').modal('show');
    });
}

// Importa en cada grupo los datos generales de la materia (cantidad de unidades, número de horas...)
// Es útil si queremos que todos los grupos tengan las mismas condiciones, o como punto de partida para
// luego editar un grupo en particular
function importarDatos(idMateria, idCurso)
{
    $('#formsgrupos').load("ajax/materias/cargar_forms_materias_grupos.php", {idMateria:idMateria, idCurso: idCurso, importar: 1});
}

// Carga el modal para asociar competencias (profesionales, etc) a la materia
function asociarCompetencias(idMateria)
{
    $.get("ajax/materias/cargar_competencias_materia.php", {idMateria: idMateria}, function(res)
    {
        $("#competenciasMateria").html(res);
        $("#formcommat").modal('show');
    });    
}

// Añade una nueva competencia a la materia indicada
function asociarCompetencia(idMateria)
{
    let idCompetencia = $('#idCompetencia').val();
    $.get("ajax/materias/nueva_competencia_materia.php", {idMateria: idMateria, idCompetencia: idCompetencia}, function(res)
    {
        asociarCompetencias(idMateria);
    });    
}

// Quita una competencia de la materia indicada
function borrarCompetencia(idMateria, idCompetencia)
{
    $.get("ajax/materias/borrar_competencia_materia.php", {idMateria: idMateria, idCompetencia: idCompetencia}, function(res)
    {
        asociarCompetencias(idMateria);
    });    
}

// Evento de envío del formulario para inserción/modificación

$("#formmat").on("submit", function(e)
{
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
        $('#formmateria').modal('hide');
        cargarMaterias();
    });
});
