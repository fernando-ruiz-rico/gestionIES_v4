// Funciones para la gestión de las programaciones de aula

let selMateria = 0;  // Materia seleccionada
let selGrupo = 0;    // Grupo seleccionado
let selEvaluacion = 0;     // Evaluación seleccionada
let selDepartamento = $('#idDepartamento').val(); // Departamento seleccionado
let selProfesor = $('#idProfesor').val(); // Profesor seleccionado
let selCurso = $('#curso').val(); // Curso seleccionado

const camposTexto = ["temporalizacion", "resultados", "inclusion"];
const camposNumero = ["num_aprobados", "num_suspensos", "num_otros"];

// Función para recargar la página con el profesor seleccionado en el desplegable (si lo hay)
function seleccionarProfesor()
{
    selProfesor = $('#seleccionProfesor').val();
    $('#idProfesor').val(selProfesor);
    if (selProfesor) {
        window.location.href = "programaciones_seguimiento_aula.php?idProfesor=" + selProfesor;
    }
}

// Cambia la materia seleccionada
function cambiarMateria()
{    
    selMateria = $('#idMateria').val();
    selGrupo = 0;
    $('#idGrupo').val(selGrupo);

    // Actualizamos los grupos según la materia elegida
    $('#idGrupo').prop('disabled', true).html('<option value="0">Cargando…</option>');
    $.ajax({ url: 'ajax/programaciones_aula/cargar_grupos.php', method: 'POST', dataType: 'json',
        data: { idMateria: selMateria, idProfesor: selProfesor }})
    .done(function (res) {
        let opciones = '<option value="0">--Selecciona un grupo--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id}">${a.nombre}</option>`;
        });
        $('#idGrupo').html(opciones).prop('disabled', false);   
    });

    cargarContenido();
}

// Cambiar el grupo seleccionado
function cambiarGrupo()
{
    selGrupo = $('#idGrupo').val();
    cargarContenido();
}

// Cambiar el grupo seleccionado
function cambiarEvaluacion()
{
    selEvaluacion = $('#idEvaluacion').val();
    cargarContenido();
}

// Calcula el total de alumnos
function calcularTotalAlumnos()
{
    let total = 0;
    camposNumero.forEach(function(idCampo) {
        total += parseInt($('#' + idCampo).val());
    });
    $('#alumnostotal').val(total);
}    

// Comprueba si debe cargar contenido en el editor TinyMCE
function cargarContenido()
{
    if(selMateria > 0 && selGrupo > 0 && selEvaluacion > 0)
    {
        $.post('ajax/programaciones_seguimiento/cargar_datos_seguimiento_aula.php', 
               {idMateria: selMateria, idGrupo: selGrupo, idProfesor: selProfesor, curso: selCurso, idEvaluacion: selEvaluacion}, function(res) 
        {
            $('#edicionseguimientoaula').show();
            camposTexto.forEach(function(idCampo) {
                if (tinymce.get(idCampo)) {
                    tinymce.get(idCampo).setContent(res[idCampo] ? res[idCampo] : '');
                }
            });
            camposNumero.forEach(function(idCampo) {
                $('#' + idCampo).val(res[idCampo] ? res[idCampo] : 0);
            });
            calcularTotalAlumnos();
        });
    }
    else
    {
        $('#edicionseguimientoaula').hide();
    }
}

// Genera un PDF con el seguimiento de todas las programaciones
function generarPDFSeguimientoAula(categoria)
{
    if (selEvaluacion)
    {
        window.open('pdf_programaciones_seguimiento.php?departamento=' + selDepartamento + '&curso=' + selCurso + '&evaluacion=' + selEvaluacion + '&categoria=' + categoria);
    } else {
        mostrarMensaje("Debes seleccionar una evaluación", 2);        
    }
}

// Guardar cambios al contenido editado
$("#formSeguimientoAula").on("submit", function(e)
{
    e.preventDefault();
    calcularTotalAlumnos();
    camposTexto.forEach(function(idCampo) {
        if (tinymce.get(idCampo)) {
            tinymce.get(idCampo).save();
        }
    });
    const formData = new FormData(document.forms.formSeguimientoAula);
    $.ajax({
        url: "ajax/programaciones_seguimiento/insertar_seguimiento_programacion_aula.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        if (res.trim() == 'si')
            mostrarMensaje("Error al realizar la operación indicada. Si no has hecho cambios respecto al contenido previamente guardado, ignora este mensaje", 0);
        else
            mostrarMensaje("Datos guardados correctamente", 1);
    });
});

// Configuración de TinyMCE si procede
if($('#temporalizacion').length > 0 && $('#resultados').length > 0 && $('#inclusion').length > 0)
{
    initTinyMCE('seguimientoeditar', 200);

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    $('#edicionseguimientoaula').hide();
}