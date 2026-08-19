// Funciones para la gestión de resultados de aprendizaje

// Variable para almacenar la materia seleccionada
let selMateria = 0;

// Función para cambiar la materia seleccionada
function cambiarMateria()
{
    selMateria = document.getElementById('seleccionMateria').value;
    if(selMateria > 0)
    {
        document.getElementById('idMateria').value = selMateria;
        cargarResultados();
    }
}

// Carga los resultados de aprendizaje para la materia seleccionada
function cargarResultados()
{
    if(selMateria > 0)
    {
        document.getElementById('resultados').load("ajax/resultados_aprendizaje/cargar_resultados_aprendizaje.php", {idMateria: selMateria});
    }
}

// Muestra los datos del resultado indicado en el formulario modal, para su edición
function cargarResultadoModal(id)
{
    fetch("ajax/resultados_aprendizaje/cargar_resultado_aprendizaje.php?" + new URLSearchParams(idResultado:id)).then(response => response.text()).then(res => {
        document.getElementById('idResultado').value = id;
        document.getElementById('texto').value = res.texto;
        document.getElementById('orden').value = res.orden;
        document.getElementById('porcentajeEmpresa').value = res.porcentaje_empresa;
        $("#formresultado").show();
    });    
}

// Muestra el formulario modal limpio para insertar un nuevo resultado
function nuevoResultado()
{
    if(selMateria > 0)
    {
        limpiarFormularioResultados();
        document.getElementById('formresultado').show();
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
    document.getElementById('idResultado').value = "";
    document.getElementById('texto').value = "";
    document.getElementById('orden').value = "";
    document.getElementById('porcentajeEmpresa').value = "0";    
}

// Actualiza las horas asignadas de la materia indicada para impartir en la empresa
function actualizarHorasEmpresa(idMateria)
{
    var horas = document.getElementById('horasEmpresa').value;
    $.post("ajax/materias/actualizar_horas_empresa.php", {idMateria:idMateria, horas: horas});
    mostrarMensaje("Datos actualizados", 1);
}

// Carga el modal para asociar criterios de evaluación al RA indicado
function asociarCriterios(id)
{
    fetch("ajax/resultados_aprendizaje/cargar_criterios_evaluacion.php?" + new URLSearchParams(idResultado:id)).then(response => response.text()).then(res => {
        document.getElementById('listadocriterios').innerHTML = res;
        document.getElementById('formcriterio').show();
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
    let nuevoCodigo = $('#cce' + codigo).value;
    let nuevoTexto = $('#tce' + codigo).value;
    $.post("ajax/resultados_aprendizaje/actualizar_criterio_evaluacion.php", {idResultado:idRA, codigo: codigo, nuevoCodigo: nuevoCodigo, nuevoTexto: nuevoTexto}, function(res)
    {
        asociarCriterios(idRA);
    });
}

// Inserta un nuevo criterio para el RA indicado
function insertarCriterio(idRA)
{
    let nuevoCodigo = document.getElementById('codigoCE').value;
    let nuevoTexto = document.getElementById('textoCE').value;

    $.post("ajax/resultados_aprendizaje/insertar_criterio_evaluacion.php", {idResultado:idRA, nuevoCodigo: nuevoCodigo, nuevoTexto: nuevoTexto}, function(res)
    {
        asociarCriterios(idRA);
    });
}

// Evento de envío del formulario modal para inserción/modificación
$("#formres").addEventListener('submit', function(e) {
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
        $("#formresultado").hide();
        cargarResultados();
    });
});
