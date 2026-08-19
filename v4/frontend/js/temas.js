// Funciones para gestión de temas/unidades asociadas a una programación didáctica

let selMateria = 0;
let idCiclo = 0;
let idTema = 0;

// Carga el acordeón de RA y CE
function cargarAccordionRAyCE()
{ 
    dom("#seccion_ra_ce").load("ajax/temas/cargar_accordion_ra_ce.php", {idMateria: selMateria, idCiclo: idCiclo}, function() {
        actualizarCheckboxes(idTema);
    });
}

// Carga el acordeón de RA y CE
async function calcularPorcentajesRAyCE()
{
    if (await confirmar("¿Deseas recalcular y actualizar los porcentajes de evaluación de los RA asociados a esta materia? Se sobreescribirán los valores actuales.")) {
        var formData = new FormData(document.forms.formeditar);
        http.ajax({
            url: "ajax/temas/actualizar_tema.php",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(res){
            http.get("ajax/temas/recalcular_porcentajes_ra_ce.php", {idMateria: selMateria}, function(res)
            {
                cargarAccordionRAyCE();
            });
        });
    }
}

// Muestra el modal para añadir una nueva unidad/tema
function nuevoTema()
{
    limpiarFormularioNuevo();
    dom("#formnuevotema").modal('show');
}

// Limpia los datos del formulario para nuevo tema
function limpiarFormularioNuevo()
{
    dom('#ordenNuevo').val("");
    dom('#tituloNuevo').val("");
}

// Borra el tema indicado, previa confirmación
async function borrarTema(id, titulo)
{
    if (await confirmar(`¿Confirmas el borrado del tema '${titulo}'?`))
    {
        http.post("ajax/temas/borrar_tema.php", {id:id}, function(res)
        {
            GestionIES.reloadPage();
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
        http.post("ajax/temas/repetir_evaluacion_temas.php", {idMateria:selMateria, evaluacion: evaluacion}, function(res)
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
    dom('.check_ce').prop('checked', false);
    dom('.check_com').prop('checked', false);

    http.get("ajax/temas/cargar_checkboxes.php", {idTema: id}, function(res)
    {
        http.each(res.criterios, function(i, val) {
            dom('#' + val).prop("checked", true);
        });        
        http.each(res.competencias, function(i, val) {
            dom('#' + val).prop("checked", true);
        });        
    });
}

// Marca/Desmarca todos los criterios asociados al RA indicado
function marcarDesmarcar(id)
{
    let resultado = dom('#ra' + id).prop("checked");
    dom('.ra' + id).prop("checked", resultado);
}

// Evento de envío del formulario modal para inserción
dom("#formnuevo").on("submit", function(e)
{ 
    e.preventDefault();
    var formData = new FormData(document.forms.formnuevo);
    http.ajax({
        url: "ajax/temas/insertar_tema.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        GestionIES.reloadPage();
    });
});

function enviarDatosAccordionRAyCE() {
    var formData = new FormData(document.forms.formeditar);
    http.ajax({
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
dom("#formeditar").on("submit", function(e)
{
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
    http.ajax({
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
            GestionIES.navigate('temas.php?idMateria=' + selMateria);
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
    http.get("ajax/resultados_aprendizaje/cargar_resultado_aprendizaje.php", {idResultado:id}, function(res)
    {
        dom('#idResultado').val(id);
        dom('#spanOrden').text(res.orden);
        dom('#spanTexto').text(res.texto);
        dom('#porcentajeEvaluacion').val(res.porcentaje_evaluacion);
        dom('#esClave').prop('checked', res.es_clave == 1).trigger('change'); 
        dom("#formresultado_ra").modal('show');
    });    
}

