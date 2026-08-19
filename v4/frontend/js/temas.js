// Funciones para gestión de temas/unidades asociadas a una programación didáctica

let selMateria = 0;
let idCiclo = 0;
let idTema = 0;

// Carga el acordeón de RA y CE
function cargarAccordionRAyCE()
{ 
    document.getElementById("seccion_ra_ce").load("ajax/temas/cargar_accordion_ra_ce.php", {idMateria: selMateria, idCiclo: idCiclo}, function() {
        actualizarCheckboxes(idTema);
    });
}

// Carga el acordeón de RA y CE
async function calcularPorcentajesRAyCE()
{
    if (await confirmar("¿Deseas recalcular y actualizar los porcentajes de evaluación de los RA asociados a esta materia? Se sobreescribirán los valores actuales.")) {
        var formData = new FormData(document.forms.formeditar);
        fetch("ajax/temas/actualizar_tema.php", { method: "POST", body: formData })
        .then(function(res) {
            fetch("ajax/temas/recalcular_porcentajes_ra_ce.php?" + new URLSearchParams({idMateria: selMateria}).toString()).then(r => r.json()).then(res => {
                cargarAccordionRAyCE();
            });
        });
    }
}

// Muestra el modal para añadir una nueva unidad/tema
function nuevoTema()
{
    limpiarFormularioNuevo();
    (() => { const el = document.getElementById("formnuevotema"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
}

// Limpia los datos del formulario para nuevo tema
function limpiarFormularioNuevo()
{
    document.getElementById('ordenNuevo').value = '';
    document.getElementById('tituloNuevo').value = '';
}

// Borra el tema indicado, previa confirmación
async function borrarTema(id, titulo)
{
    if (await confirmar(`¿Confirmas el borrado del tema '${titulo}'?`))
    {
        fetch("ajax/temas/borrar_tema.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({id:id}).toString() }).then(r => r.text()).then(res => {
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
        fetch("ajax/temas/repetir_evaluacion_temas.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idMateria:selMateria, evaluacion: evaluacion}).toString() }).then(r => r.text()).then(res => {
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
    document.querySelector(".check_ce").checked = false;
    document.querySelector(".check_com").checked = false;

    fetch("ajax/temas/cargar_checkboxes.php?" + new URLSearchParams({idTema: id}).toString()).then(r => r.json()).then(res => {
        http.each(res.criterios, function(i, val) {
            (() => { const el = document.getElementById(val); if(el) el.checked = true; })();
        });        
        http.each(res.competencias, function(i, val) {
            (() => { const el = document.getElementById(val); if(el) el.checked = true; })();
        });        
    });
}

// Marca/Desmarca todos los criterios asociados al RA indicado
function marcarDesmarcar(id)
{
    let resultado = document.getElementById('ra' + id).checked;
    document.querySelectorAll('.ra' + id).forEach(el => el.checked = resultado);
}

// Evento de envío del formulario modal para inserción
document.getElementById("formnuevo").addEventListener("submit", function(e)
{ 
    e.preventDefault();
    var formData = new FormData(document.forms.formnuevo);
    fetch("ajax/temas/insertar_tema.php", { method: "POST", body: formData })
    .then(function(res) {
        location.reload();
    });
});

function enviarDatosAccordionRAyCE() {
    var formData = new FormData(document.forms.formeditar);
    fetch("ajax/temas/actualizar_tema.php", { method: "POST", body: formData })
    .then(function(res) {
        cargarAccordionRAyCE();
    });
}

// Evento de envío del formulario modal para edición
document.getElementById("formeditar").addEventListener("submit", function(e)
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
    fetch("ajax/temas/actualizar_tema.php", { method: "POST", body: formData }).then(function(res) {
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
    fetch("ajax/resultados_aprendizaje/cargar_resultado_aprendizaje.php?" + new URLSearchParams({idResultado:id}).toString()).then(r => r.json()).then(res => {
        document.getElementById('idResultado').value = id;
        document.getElementById('spanOrden').textContent = res.orden;
        document.getElementById('spanTexto').textContent = res.texto;
        document.getElementById('porcentajeEvaluacion').value = res.porcentaje_evaluacion;
        dom('#esClave').prop('checked', res.es_clave == 1); 
        (() => { const el = document.getElementById("formresultado_ra"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

