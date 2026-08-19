// Funciones para gestión de materias desde la vista "materias.php"

// Variable donde almacenamos el curso actualmente seleccionado
var selCurso = 0;

// Cambia la selección de curso actual
function seleccionarCursoMateria()
{
    selCurso  = $('#cursosmaterias').value;
    $('#idCurso').value = selCurso;
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
    fetch('ajax/materias/cargar_materia.php?' + new URLSearchParams({idMateria:id})).then(r => r.json()).then(res =>
        $('#idMateria').value = id;
        $('#idCurso').value = res.idCurso;
        $('#nombre').value = res.nombre;
        $('#codigoOficial').value = res.codigo_oficial;
        $('#nombreOficial').value = res.nombre_oficial;
        $('#creditosECTS').value = res.creditos_ects;
        $('#horasAnuales').value = res.horas_anuales;
        $('#cantidad').value = res.cantidad;
        $('#horas').value = res.horas;
        $('#horasComplementarias').value = res.horas_complementarias;
        $('#tipo').value = res.tipo;
        $('#departamento').value = res.idDepartamento;
        cargarEspecialidades(res.idEspecialidad);
        if (res.computables_horas_grupo == 1)
            $('#computablesHorasGrupo').prop('checked', true);
        else
            $('#computablesHorasGrupo').prop('checked', false);
        if (res.asignada_directiva == 1)
            $('#asignadaDirectiva').prop('checked', true);
        else
            $('#asignadaDirectiva').prop('checked', false);
        $('#minNumProfesores').value = res.min_num_profesores;
        $('#maxGruposProfesor').value = res.max_grupos_profesor;
        if (res.tiene_programacion == 1)
            $('#tieneProgramacion').prop('checked', true);
        else
            $('#tieneProgramacion').prop('checked', false);
        if (res.divisible == 1)
            $('#divisible').prop('checked', true);
        else
            $('#divisible').prop('checked', false);

        document.getElementById("formmateria").modal('show');
    });    
}

// Carga las especialidades del departamento seleccionado
function cargarEspecialidades(idEspecialidad)
{
    var selDepartamento = $('#departamento').value;
    if(selDepartamento != "")
    {
        // Primero cargamos las especialidades del departamento asociado
        fetch('ajax/especialidades/cargar_especialidades_json.php?' + new URLSearchParams({idDepartamento:selDepartamento})).then(r => r.json()).then(resEsp =>
            let resultado = JSON.parse(resEsp);
            // Accedemos al "select" de especialidad del formulario y rellenamos las opciones
            $('#especialidad').innerHTML = "";
            // Añadimos una opción vacía inicial
            var $option = $('<option></option>')
                .attr('value', '')
                .textContent = '--Selecciona una especialidad--';
            $('#especialidad').appendChild($option);
            for(var i = 0; i < resultado.length; i++) {
                var $option = $('<option></option>')
                    .attr('value', resultado[i].id)
                    .textContent = resultado[i].descripcion;
                $('#especialidad').appendChild($option);
            }

            if(idEspecialidad)
            {
                $('#especialidad').value = idEspecialidad;
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
        fetch('ajax/materias/borrar_materia.php', {method: 'POST', body: new URLSearchParams({id:id})}).then(r => r.text()).then(res =>
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar la materia", 0);
            cargarMaterias();
        });            
    }
}

// Limpia los datos del formulario modal de materias
function limpiarFormularioMaterias()
{
    $('#idMateria').value = "";
    $('#nombre').value = "";
    $('#codigoOficial').value = "";
    $('#nombreOficial').value = "";
    $('#creditosECTS').value = "";
    $('#horasAnuales').value = "";
    $('#cantidad').value = "1";
    $('#horas').value = "";
    $('#horasComplementarias').value = "";
    $('#tipo').value = "OTRAS";
    $('#departamento').value = "";
    $('#especialidad').value = "";
    $('#computablesHorasGrupo').prop('checked', true);
    $('#tieneProgramacion').prop('checked', true);
    $('#divisible').prop('checked', true);
    $('#asignadaDirectiva').prop('checked', false);
    $('#minNumProfesores').value = "0";
    $('#maxGruposProfesor').value = "0";    
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
    fetch('ajax/materias/cargar_competencias_materia.php?' + new URLSearchParams({idMateria: idMateria})).then(r => r.json()).then(res =>
        document.getElementById("competenciasMateria").innerHTML = res;
        document.getElementById("formcommat").modal('show');
    });    
}

// Añade una nueva competencia a la materia indicada
function asociarCompetencia(idMateria)
{
    let idCompetencia = $('#idCompetencia').value;
    fetch('ajax/materias/nueva_competencia_materia.php?' + new URLSearchParams({idMateria: idMateria, idCompetencia: idCompetencia})).then(r => r.json()).then(res =>
        asociarCompetencias(idMateria);
    });    
}

// Quita una competencia de la materia indicada
function borrarCompetencia(idMateria, idCompetencia)
{
    fetch('ajax/materias/borrar_competencia_materia.php?' + new URLSearchParams({idMateria: idMateria, idCompetencia: idCompetencia})).then(r => r.json()).then(res =>
        asociarCompetencias(idMateria);
    });    
}

// Evento de envío del formulario para inserción/modificación

document.getElementById("formmat").on("submit", function(e)
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
