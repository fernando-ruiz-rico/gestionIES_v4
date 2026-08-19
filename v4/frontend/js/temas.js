// Funciones para gestión de temas/unidades asociadas a una programación didáctica

let selMateria = 0;
let idCiclo = 0;
let idTema = 0;

// Carga el acordeón de RA y CE
function cargarAccordionRAyCE()
{ 
    $("#seccion_ra_ce").load("ajax/temas/cargar_accordion_ra_ce.php", {idMateria: selMateria, idCiclo: idCiclo}, function() {
        actualizarCheckboxes(idTema);
    });
}

// Carga el acordeón de RA y CE
async function calcularPorcentajesRAyCE()
{
    if (await confirmar("¿Deseas recalcular y actualizar los porcentajes de evaluación de los RA asociados a esta materia? Se sobreescribirán los valores actuales.")) {
        var formData = new FormData(document.forms.formeditar);
        $.ajax({
            url: "ajax/temas/actualizar_tema.php",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(res){
            fetch("ajax/temas/recalcular_porcentajes_ra_ce.php?" + new URLSearchParams(idMateria: selMateria)).then(response => response.text()).then(res => {
                cargarAccordionRAyCE();
            });
        });
    }
}

// Muestra el modal para añadir una nueva unidad/tema
function nuevoTema()
{
    limpiarFormularioNuevo();
    $("#formnuevotema").show();
}

// Limpia los datos del formulario para nuevo tema
function limpiarFormularioNuevo()
{
    document.getElementById('ordenNuevo').value = "";
    document.getElementById('tituloNuevo').value = "";
}

// Borra el tema indicado, previa confirmación
async function borrarTema(id, titulo)
{
    if (await confirmar(`¿Confirmas el borrado del tema '${titulo}'?`))
    {
        $.post("ajax/temas/borrar_tema.php", {id:id}, function(res)
        {
            location.reload();
        });            
    }
}

// Repite el campo "evaluacion" en los demás temas de la misma materia
async function repetirEvaluacion()
{
    if (await confirmar("Al copiar el campo 'Evaluación' en todos los temas de la materia, se sobreescribirá el contenido actual de los demás temas."))
    {
        tinymce.get('evaluacion').save();
        let evaluacion = tinymce.get('evaluacion').getContent();
        $.post("ajax/temas/repetir_evaluacion_temas.php", {idMateria:selMateria, evaluacion: evaluacion}, function(res)
        {
            if (res.trim() == 'si')
                mostrarMensaje("No se han actualizado los temas de la materia", 0);
            else
                mostrarMensaje("El campo 'Evaluación' se ha copiado en todos los temas de la materia", 1);

        });
    }            
}

// Función auxiliar para actualizar los checkboxes en el formulario de edición de temas
function actualizarCheckboxes(id)
{
    document.querySelectorAll('.check_ce').prop('checked', false);
    document.querySelectorAll('.check_com').prop('checked', false);

    fetch("ajax/temas/cargar_checkboxes.php?" + new URLSearchParams(idTema: id)).then(response => response.text()).then(res => {
        $.each(res.criterios, function(i, val) {
            $('#' + val).prop("checked", true);
        });        
        $.each(res.competencias, function(i, val) {
            $('#' + val).prop("checked", true);
        });        
    });
}

// Marca/Desmarca todos los criterios asociados al RA indicado
function marcarDesmarcar(id)
{
    let resultado = $('#ra' + id).prop("checked");
    $('.ra' + id).prop("checked", resultado);
}

// Evento de envío del formulario modal para inserción
$("#formnuevo").addEventListener('submit', function(e) { 
    e.preventDefault();
    var formData = new FormData(document.forms.formnuevo);
    $.ajax({
        url: "ajax/temas/insertar_tema.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        location.reload();
    });
});

function enviarDatosAccordionRAyCE() {
    var formData = new FormData(document.forms.formeditar);
    $.ajax({
        url: "ajax/temas/actualizar_tema.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        cargarAccordionRAyCE();
    });
}

// Evento de envío del formulario modal para edición
$("#formeditar").addEventListener('submit', function(e) {
    tinymce.get('descripcion').save();
    tinymce.get('justificacion').save();
    tinymce.get('contexto').save();
    tinymce.get('contenidos').save();
    tinymce.get('secuenciacion').save();
    tinymce.get('recursos').save();
    tinymce.get('evaluacion').save();
    tinymce.get('metodologia').save();
    e.preventDefault();
    var formData = new FormData(document.forms.formeditar);
    $.ajax({
        url: "ajax/temas/actualizar_tema.php",
        type: "post",
        dataType: "json",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    }).done(function(res){
        if(!res.errorTema && !res.errorCriterios && !res.errorCompetencias)
        {
            location.href = 'temas.php?idMateria=' + selMateria;
        }
        else
        {
            let mensaje = "";
            if(res.errorTema)
                mensaje += "Los datos generales del tema no se guardaron correctamente<br>";
            if(res.errorCriterios)
                mensaje += "Los criterios de evaluación no se guardaron correctamente<br>";
            if(res.errorCompetencias)
                mensaje += "Las competencias no se guardaron correctamente<br>";
            mostrarMensaje(mensaje, 0);
        }
    });
});

// Muestra los datos del resultado indicado en el formulario modal, para su edición
function cargarModalActualizarRaTemas(id)
{
    fetch("ajax/resultados_aprendizaje/cargar_resultado_aprendizaje.php?" + new URLSearchParams(idResultado:id)).then(response => response.text()).then(res => {
        document.getElementById('idResultado').value = id;
        document.getElementById('spanOrden').textContent = res.orden;
        document.getElementById('spanTexto').textContent = res.texto;
        document.getElementById('porcentajeEvaluacion').value = res.porcentaje_evaluacion;
        document.getElementById('esClave').prop('checked', res.es_clave == 1).trigger('change'); 
        $("#formresultado_ra").show();
    });    
}

