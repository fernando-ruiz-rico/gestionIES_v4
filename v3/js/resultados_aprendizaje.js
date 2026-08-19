// Funciones para la gestión de resultados de aprendizaje

// Variable para almacenar la materia seleccionada
let selMateria = 0;

// Función para cambiar la materia seleccionada
function cambiarMateria()
{
    selMateria = $('#seleccionMateria').val();
    if(selMateria > 0)
    {
        $('#idMateria').val(selMateria);
        cargarResultados();
    }
}

// Carga los resultados de aprendizaje para la materia seleccionada
function cargarResultados()
{
    if(selMateria > 0)
    {
        $('#resultados').load("ajax/resultados_aprendizaje/cargar_resultados_aprendizaje.php", {idMateria: selMateria});
    }
}

// Muestra los datos del resultado indicado en el formulario modal, para su edición
function cargarResultadoModal(id)
{
    $.get("ajax/resultados_aprendizaje/cargar_resultado_aprendizaje.php", {idResultado:id}, function(res)
    {
        $('#idResultado').val(id);
        $('#texto').val(res.texto);
        $('#orden').val(res.orden);
        $('#porcentajeEmpresa').val(res.porcentaje_empresa);
        $("#formresultado").modal('show');
    });    
}

// Muestra el formulario modal limpio para insertar un nuevo resultado
function nuevoResultado()
{
    if(selMateria > 0)
    {
        limpiarFormularioResultados();
        $('#formresultado').modal('show');
    } else {
        mostrarMensaje("Debes elegir una materia primero", 2);
    }
}

// Borra el resultado indicado, previa confirmación
function borrarResultado (id, texto)
{
    if (confirm("Confirmas el borrado del resultado '" + texto + "'?"))
    {
        $.post("ajax/resultados_aprendizaje/borrar_resultado_aprendizaje.php", {id:id}, function(res)
        {
            cargarResultados();
        });            
    }
}

// Borra el contenido de los campos del formulario modal de alta/edición de cursos
function limpiarFormularioResultados()
{
    $('#idResultado').val("");
    $('#texto').val("");
    $('#orden').val("");
    $('#porcentajeEmpresa').val("0");    
}

// Actualiza las horas asignadas de la materia indicada para impartir en la empresa
function actualizarHorasEmpresa(idMateria)
{
    var horas = $('#horasEmpresa').val();
    $.post("ajax/materias/actualizar_horas_empresa.php", {idMateria:idMateria, horas: horas});
    mostrarMensaje("Datos actualizados", 1);
}

// Carga el modal para asociar criterios de evaluación al RA indicado
function asociarCriterios(id)
{
    $.get("ajax/resultados_aprendizaje/cargar_criterios_evaluacion.php", {idResultado:id}, function(res)
    {
        $('#listadocriterios').html(res);
        $('#formcriterio').modal('show');
    });
}

// Borra el criterio de evaluación indicado por el id del RA y el código de criterio
function borrarCriterio(idRA, codigo)
{
    $.post("ajax/resultados_aprendizaje/borrar_criterio_evaluacion.php", {idResultado:idRA, codigo: codigo}, function(res)
    {
        asociarCriterios(idRA);
    });
}

// Actualiza los datos del criterio de evaluación indicado
function actualizarCriterio(idRA, codigo)
{
    let nuevoCodigo = $('#cce' + codigo).val();
    let nuevoTexto = $('#tce' + codigo).val();
    $.post("ajax/resultados_aprendizaje/actualizar_criterio_evaluacion.php", {idResultado:idRA, codigo: codigo, nuevoCodigo: nuevoCodigo, nuevoTexto: nuevoTexto}, function(res)
    {
        asociarCriterios(idRA);
    });
}

// Inserta un nuevo criterio para el RA indicado
function insertarCriterio(idRA)
{
    let nuevoCodigo = $('#codigoCE').val();
    let nuevoTexto = $('#textoCE').val();

    $.post("ajax/resultados_aprendizaje/insertar_criterio_evaluacion.php", {idResultado:idRA, nuevoCodigo: nuevoCodigo, nuevoTexto: nuevoTexto}, function(res)
    {
        asociarCriterios(idRA);
    });
}

// Evento de envío del formulario modal para inserción/modificación
$("#formres").on("submit", function(e)
{
    e.preventDefault();
    let formData = new FormData(document.forms.formres);
    $.ajax({
        url: "ajax/resultados_aprendizaje/insertar_resultado_aprendizaje.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioResultados();
        $("#formresultado").modal('hide');
        cargarResultados();
    });
});
