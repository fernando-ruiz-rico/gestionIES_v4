// Funciones para la gestión de los apartados de los PCCF

// Carga los apartados en el "div" habilitado para ello
function cargarApartados()
{
    dom("#apartadospccf").load("ajax/pccf_apartados/cargar_apartados.php");
}

// Muestra los datos de un apartado en el formulario modal
function cargarApartadoModal(id)
{
    http.get("ajax/pccf_apartados/cargar_apartado.php", {idApartado:id}, function(res)
    {
        dom('#idApartado').val(id);
        dom('#titulo').val(res.titulo);
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
        dom("#formapartadopccf").modal('show');
    });    
}

// Muestra el formulario modal para crear un nuevo apartado
function nuevoApartado()
{
    limpiarFormularioApartados();
    dom('#formapartadopccf').modal('show');
}

// Borra un apartado, previa confirmación
function borrarApartado (id, titulo)
{
    if (confirm("Confirmas el borrado del apartado '" + titulo + "'? Se eliminarán todos los contenidos relativos a dicho apartado."))
    {
        http.post("ajax/pccf_apartados/borrar_apartado.php", {id:id}, function(res)
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
    dom('#tipo').val("");
    dom('#subapartado').removeAttr("checked");
    dom('#requerido').attr("checked", "checked");    
    dom('#contenidoDefecto').removeAttr("checked");
}

// Evento de ordenación de los apartados
dom('#apartadospccf').sortable({ items: '.apartado', update: function()
    {
        var elementos = dom(this).sortable("toArray").toString();
        http.get("ajax/pccf_apartados/ordenar_apartados.php", {orden: elementos}, function()
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
	url: "ajax/pccf_apartados/insertar_apartado.php",
	type: "post",
	dataType: "html",
	data: formData,
	cache: false,
	contentType: false,
	processData: false
    })
    .done(function(res){
        limpiarFormularioApartados();
	    dom("#formapartadopccf").modal('hide');
        cargarApartados();
    });
});

cargarApartados();