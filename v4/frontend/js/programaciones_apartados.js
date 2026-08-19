// Funciones para la gestión de los apartados de las programaciones didácticas

// Carga los apartados en el "div" habilitado para ello
function cargarApartados()
{
    dom("#apartadosprog").load("ajax/programaciones_apartados/cargar_apartados.php");
}

// Muestra los datos de un apartado en el formulario modal
function cargarApartadoModal(id)
{
    http.get("ajax/programaciones_apartados/cargar_apartado.php", {idApartado:id}, function(res)
    {
        dom('#idApartado').val(id);
        dom('#titulo').val(res.titulo);
        dom('#categoria').val(res.categoria);
        dom('#tipo').val(res.tipo);
        if (res.subapartado == 1)
            dom('#subapartado').prop('checked', true);
        else
            dom('#subapartado').prop('checked', false);
        if (res.requerido == 1)
            dom('#requerido').prop('checked', true);
        else
            dom('#requerido').prop('checked', false);
        if (res.contenido_defecto == 1)
            dom('#contenidoDefecto').prop('checked', true);
        else
            dom('#contenidoDefecto').prop('checked', false);
        dom("#formapartadoprogramacion").modal('show');
    });    
}

// Muestra el formulario modal para crear un nuevo apartado
function nuevoApartado()
{
    limpiarFormularioApartados();
    dom('#formapartadoprogramacion').modal('show');
}

// Borra un apartado, previa confirmación
function borrarApartado (id, titulo)
{
    if (confirm("Confirmas el borrado del apartado '" + titulo + "'? Se eliminarán todos los contenidos de las programaciones relativos a dicho apartado."))
    {
        http.post("ajax/programaciones_apartados/borrar_apartado.php", {id:id}, function(res)
        {
            cargarApartados();
        });            
    }
}

// Limpia los campos del formulario modal
function limpiarFormularioApartados()
{
    dom('#idApartado').val("");
    dom('#titulo').val("");
    dom('#categoria').val("");
    dom('#tipo').val("");
    dom('#subapartado').removeAttr("checked");
    dom('#requerido').attr("checked", "checked");    
    dom('#contenidoDefecto').removeAttr("checked");
}

// Evento de ordenación de los apartados
dom('#apartadosprog').sortable({ items: '.apartado', update: function()
    {
        var elementos = dom(this).sortable("toArray").toString();
        http.get("ajax/programaciones_apartados/ordenar_apartados.php", {orden: elementos}, function()
        {
            cargarApartados();
        });
    }
});

// Evento de envío del formulario modal para crear/modificar apartados
dom("#formapartado").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formapartado);
    http.ajax({
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
	    dom("#formapartadoprogramacion").modal('hide');
        cargarApartados();
    });
});

cargarApartados();