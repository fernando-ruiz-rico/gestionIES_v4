// Funciones para gestión de escenarios de desideratas

// Lista los escenarios posibles en el "div" habilitado para ello
function cargarEscenarios()
{
    document.getElementById("escenariosdesid").load("ajax/escenarios/cargar_escenarios.php");
}

// Muestra los datos del escenario indicado en el formulario modal, para su edición
function cargarEscenarioModal(id)
{
    fetch('ajax/escenarios/cargar_escenario.php?' + new URLSearchParams({idEscenario:id})).then(r => r.json()).then(res =>
        $('#idEscenario').value = id;
        $('#nombre').value = res.nombre;
        $('#listadoDepartamentosEscenario').load('ajax/escenarios/cargar_departamentos_escenario.php', {idEscenario:id}, function(res)
        {
            document.getElementById("formescenario").modal('show');
        });
    });    
}

// Muestra el formulario vacío para crear un nuevo escenario
function nuevoEscenario()
{
    limpiarFormularioEscenarios();
    $('#listadoDepartamentosEscenario').load('ajax/escenarios/cargar_departamentos_escenario.php', function(res)
    {
        document.getElementById("formescenario").modal('show');
    });
}

// Borra el escenario indicado, previa confirmación.
// También se borran las selecciones de materias hechas para ese escenario
function borrarEscenario (id, nombre)
{
    if (confirm("Confirmas el borrado del escenario '" + nombre + "'? Este borrado también afectará a las selecciones que los profesores hayan hecho sobre él."))
    {
        fetch('ajax/escenarios/borrar_escenario.php', {method: 'POST', body: new URLSearchParams({id:id})}).then(r => r.text()).then(res =>
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
    $('#idEscenario').value = "";
    $('#nombreEscenario').value = "";
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
    fetch('ajax/escenarios/duplicar_escenario.php', {method: 'POST', body: new URLSearchParams({idEscenario: id})}).then(r => r.text()).then(res =>
        cargarEscenarios();
    });            
}

// Evento de envío del formulario modal para insertar/modificar escenarios
document.getElementById("formesc").on("submit", function(e)
{
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
        document.getElementById("formescenario").modal('hide');
        cargarEscenarios();
    });
});

cargarEscenarios();