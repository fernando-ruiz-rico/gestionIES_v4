// Funciones para gestión de escenarios de desideratas

// Lista los escenarios posibles en el "div" habilitado para ello
function cargarEscenarios()
{
    $("#escenariosdesid").load("ajax/escenarios/cargar_escenarios.php");
}

// Muestra los datos del escenario indicado en el formulario modal, para su edición
function cargarEscenarioModal(id)
{
    fetch("ajax/escenarios/cargar_escenario.php?" + new URLSearchParams(idEscenario:id)).then(response => response.text()).then(res => {
        document.getElementById('idEscenario').value = id;
        document.getElementById('nombre').value = res.nombre;
        document.getElementById('listadoDepartamentosEscenario').load('ajax/escenarios/cargar_departamentos_escenario.php', {idEscenario:id}, function(res)
        {
            $("#formescenario").show();
        });
    });    
}

// Muestra el formulario vacío para crear un nuevo escenario
function nuevoEscenario()
{
    limpiarFormularioEscenarios();
    document.getElementById('listadoDepartamentosEscenario').load('ajax/escenarios/cargar_departamentos_escenario.php', function(res)
    {
        $("#formescenario").show();
    });
}

// Borra el escenario indicado, previa confirmación.
// También se borran las selecciones de materias hechas para ese escenario
function borrarEscenario (id, nombre)
{
    if (confirm("Confirmas el borrado del escenario '" + nombre + "'? Este borrado también afectará a las selecciones que los profesores hayan hecho sobre él."))
    {
        $.post("ajax/escenarios/borrar_escenario.php", {id:id}, function(res)
        {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar el escenario", 0);
            else
                cargarEscenarios();
        });            
    }
}

// Borra el contenido del formulario modal
function limpiarFormularioEscenarios()
{
    document.getElementById('idEscenario').value = "";
    document.getElementById('nombreEscenario').value = "";
}

// Establece el escenario indicado como el actualmente vigente
function marcarEscenarioActual (id, actual)
{
    $.post("ajax/escenarios/actualizar_escenario_actual.php", {idEscenario: id, valorActual: actual}, function()
    {
        cargarEscenarios();
    });    
    
}

// Habilita/Deshabilita el modo rueda en el escenario indicado
// actual será "si" para modo rueda y "no" para deshabilitarlo
function modoRueda (id, actual)
{
    $.post("ajax/escenarios/actualizar_modo_rueda.php", {idEscenario: id, valorActual: actual}, function()
    {
        cargarEscenarios();
    });    
    
}

// Establece el escenario indicado como actualmente activo para elegir materias en él
// Sólo se pueden elegir materias sobre los escenarios actualmente activos.
// El resto forma parte del histórico
function marcarEscenarioActivoDesideratas(id, activo)
{
    $.post("ajax/escenarios/actualizar_escenario_activo_desideratas.php", {idEscenario: id, valorActual: activo}, function()
    {
        cargarEscenarios();
    });        
}

// Duplica el escenario indicado, creando otro con nombre similar y mismos departamentos asociados
function duplicarEscenario(id)
{
    $.post("ajax/escenarios/duplicar_escenario.php", {idEscenario: id}, function(res)
    {
        cargarEscenarios();
    });            
}

// Evento de envío del formulario modal para insertar/modificar escenarios
$("#formesc").addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(document.forms.formesc);
    $.ajax({
        url: "ajax/escenarios/insertar_escenario.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioEscenarios();
        $("#formescenario").hide();
        cargarEscenarios();
    });
});

cargarEscenarios();