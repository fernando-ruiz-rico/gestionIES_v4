// Funciones para la gestión de los apartados de las programaciones didácticas

// Carga los apartados en el "div" habilitado para ello
function cargarApartados()
{
    $("#apartadosprog").load("ajax/programaciones_apartados/cargar_apartados.php");
}

// Muestra los datos de un apartado en el formulario modal
function cargarApartadoModal(id)
{
    $.get("ajax/programaciones_apartados/cargar_apartado.php", {idApartado:id}, function(res)
    {
        $('#idApartado').val(id);
        $('#titulo').val(res.titulo);
        $('#categoria').val(res.categoria);
        $('#tipo').val(res.tipo);
        if (res.subapartado == 1)
            $('#subapartado').prop('checked', true);
        else
            $('#subapartado').prop('checked', false);
        if (res.requerido == 1)
            $('#requerido').prop('checked', true);
        else
            $('#requerido').prop('checked', false);
        if (res.contenido_defecto == 1)
            $('#contenidoDefecto').prop('checked', true);
        else
            $('#contenidoDefecto').prop('checked', false);
        $("#formapartadoprogramacion").modal('show');
    });    
}

// Muestra el formulario modal para crear un nuevo apartado
function nuevoApartado()
{
    limpiarFormularioApartados();
    $('#formapartadoprogramacion').modal('show');
}

// Borra un apartado, previa confirmación
function borrarApartado (id, titulo)
{
    if (confirm("Confirmas el borrado del apartado '" + titulo + "'? Se eliminarán todos los contenidos de las programaciones relativos a dicho apartado."))
    {
        $.post("ajax/programaciones_apartados/borrar_apartado.php", {id:id}, function(res)
        {
            cargarApartados();
        });            
    }
}

// Limpia los campos del formulario modal
function limpiarFormularioApartados()
{
    $('#idApartado').val("");
    $('#titulo').val("");
    $('#categoria').val("");
    $('#tipo').val("");
    $('#subapartado').removeAttr("checked");
    $('#requerido').attr("checked", "checked");    
    $('#contenidoDefecto').removeAttr("checked");
}

// Evento de ordenación de los apartados
$('#apartadosprog').sortable({ items: '.apartado', update: function()
    {
        var elementos = $(this).sortable("toArray").toString();
        $.get("ajax/programaciones_apartados/ordenar_apartados.php", {orden: elementos}, function()
        {
            cargarApartados();
        });
    }
});

// Evento de envío del formulario modal para crear/modificar apartados
$("#formapartado").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formapartado);
    $.ajax({
	url: "ajax/programaciones_apartados/insertar_apartado.php",
	type: "post",
	dataType: "html",
	data: formData,
	cache: false,
	contentType: false,
	processData: false
    })
    .done(function(res){
        limpiarFormularioApartados();
	    $("#formapartadoprogramacion").modal('hide');
        cargarApartados();
    });
});

cargarApartados();