// Funciones para gestión de materias desde la vista "materias.php"

// Variable donde almacenamos el curso actualmente seleccionado
var selCurso = 0;

// Cambia la selección de curso actual
function seleccionarCursoMateria()
{
    selCurso  = dom('#cursosmaterias').val();
    dom('#idCurso').val(selCurso);
    cargarMaterias();
}

// Carga el listado de materias del curso indicado en el "div" habilitado para ello
function cargarMaterias()
{
    dom("#listamaterias").load("ajax/materias/cargar_materias.php", {idCurso: selCurso});
}

// Carga los datos de la materia indicada en el formulario modal
function cargarMateriaModal(id)
{
    http.get("ajax/materias/cargar_materia.php", {idMateria:id}, function(res)
    {
        dom('#idMateria').val(id);
        dom('#idCurso').val(res.idCurso);
        dom('#nombre').val(res.nombre);
        dom('#codigoOficial').val(res.codigo_oficial);
        dom('#nombreOficial').val(res.nombre_oficial);
        dom('#creditosECTS').val(res.creditos_ects);
        dom('#horasAnuales').val(res.horas_anuales);
        dom('#cantidad').val(res.cantidad);
        dom('#horas').val(res.horas);
        dom('#horasComplementarias').val(res.horas_complementarias);
        dom('#tipo').val(res.tipo);
        dom('#departamento').val(res.idDepartamento);
        cargarEspecialidades(res.idEspecialidad);
        if (res.computables_horas_grupo == 1)
            dom('#computablesHorasGrupo').prop('checked', true);
        else
            dom('#computablesHorasGrupo').prop('checked', false);
        if (res.asignada_directiva == 1)
            dom('#asignadaDirectiva').prop('checked', true);
        else
            dom('#asignadaDirectiva').prop('checked', false);
        dom('#minNumProfesores').val(res.min_num_profesores);
        dom('#maxGruposProfesor').val(res.max_grupos_profesor);
        if (res.tiene_programacion == 1)
            dom('#tieneProgramacion').prop('checked', true);
        else
            dom('#tieneProgramacion').prop('checked', false);
        if (res.divisible == 1)
            dom('#divisible').prop('checked', true);
        else
            dom('#divisible').prop('checked', false);

        dom("#formmateria").modal('show');
    });    
}

// Carga las especialidades del departamento seleccionado
function cargarEspecialidades(idEspecialidad)
{
    var selDepartamento = dom('#departamento').val();
    if(selDepartamento != "")
    {
        // Primero cargamos las especialidades del departamento asociado
        http.get("ajax/especialidades/cargar_especialidades_json.php", {idDepartamento:selDepartamento}, function(resEsp)
        {
            let resultado = JSON.parse(resEsp);
            // Accedemos al "select" de especialidad del formulario y rellenamos las opciones
            dom('#especialidad').empty();
            // Añadimos una opción vacía inicial
            var $option = dom('<option></option>')
                .attr('value', '')
                .text('--Selecciona una especialidad--');
            dom('#especialidad').append($option);
            for(var i = 0; i < resultado.length; i++) {
                var $option = dom('<option></option>')
                    .attr('value', resultado[i].id)
                    .text(resultado[i].descripcion);
                dom('#especialidad').append($option);
            }

            if(idEspecialidad)
            {
                dom('#especialidad').val(idEspecialidad);
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
        dom('#formmateria').modal('show');
    }
}

// Borra la materia indicada, previa confirmación
function borrarMateria (id, nombre)
{
    if (confirm("Confirmas el borrado de la materia '" + nombre + "'?"))
    {
        http.post("ajax/materias/borrar_materia.php", {id:id}, function(res)
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
    dom('#idMateria').val("");
    dom('#nombre').val("");
    dom('#codigoOficial').val("");
    dom('#nombreOficial').val("");
    dom('#creditosECTS').val("");
    dom('#horasAnuales').val("");
    dom('#cantidad').val("1");
    dom('#horas').val("");
    dom('#horasComplementarias').val("");
    dom('#tipo').val("OTRAS");
    dom('#departamento').val("");
    dom('#especialidad').val("");
    dom('#computablesHorasGrupo').prop('checked', true);
    dom('#tieneProgramacion').prop('checked', true);
    dom('#divisible').prop('checked', true);
    dom('#asignadaDirectiva').prop('checked', false);
    dom('#minNumProfesores').val("0");
    dom('#maxGruposProfesor').val("0");    
}

// Carga el formulario modal para editar los datos de la materia indicada para los distintos grupos
function cargarMateriasGrupos(idMateria, idCurso)
{
    dom('#formsgrupos').load("ajax/materias/cargar_forms_materias_grupos.php", {idMateria:idMateria, idCurso: idCurso, importar: 0}, function()
    {
        dom('#formmateriagrupo').modal('show');
    });
}

// Importa en cada grupo los datos generales de la materia (cantidad de unidades, número de horas...)
// Es útil si queremos que todos los grupos tengan las mismas condiciones, o como punto de partida para
// luego editar un grupo en particular
function importarDatos(idMateria, idCurso)
{
    dom('#formsgrupos').load("ajax/materias/cargar_forms_materias_grupos.php", {idMateria:idMateria, idCurso: idCurso, importar: 1});
}

// Carga el modal para asociar competencias (profesionales, etc) a la materia
function asociarCompetencias(idMateria)
{
    http.get("ajax/materias/cargar_competencias_materia.php", {idMateria: idMateria}, function(res)
    {
        dom("#competenciasMateria").html(res);
        dom("#formcommat").modal('show');
    });    
}

// Añade una nueva competencia a la materia indicada
function asociarCompetencia(idMateria)
{
    let idCompetencia = dom('#idCompetencia').val();
    http.get("ajax/materias/nueva_competencia_materia.php", {idMateria: idMateria, idCompetencia: idCompetencia}, function(res)
    {
        asociarCompetencias(idMateria);
    });    
}

// Quita una competencia de la materia indicada
function borrarCompetencia(idMateria, idCompetencia)
{
    http.get("ajax/materias/borrar_competencia_materia.php", {idMateria: idMateria, idCompetencia: idCompetencia}, function(res)
    {
        asociarCompetencias(idMateria);
    });    
}

// Evento de envío del formulario para inserción/modificación

dom("#formmat").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formmat);
    http.ajax({
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
        dom('#formmateria').modal('hide');
        cargarMaterias();
    });
});
