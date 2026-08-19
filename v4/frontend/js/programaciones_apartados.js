// Funciones para la gestión de los apartados de las programaciones didácticas

// Carga los apartados en el "div" habilitado para ello
function cargarApartados()
{
    document.getElementById("apartadosprog").load("ajax/programaciones_apartados/cargar_apartados.php");
}

// Muestra los datos de un apartado en el formulario modal
function cargarApartadoModal(id)
{
    fetch('ajax/programaciones_apartados/cargar_apartado.php?' + new URLSearchParams({idApartado:id})).then(r => r.json()).then(res =>
        $('#idApartado').value = id;
        $('#titulo').value = res.titulo;
        $('#categoria').value = res.categoria;
        $('#tipo').value = res.tipo;
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
        document.getElementById("formapartadoprogramacion").modal('show');
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
        fetch('ajax/programaciones_apartados/borrar_apartado.php', {method: 'POST', body: new URLSearchParams({id:id})}).then(r => r.text()).then(res =>
            cargarApartados();
        });            
    }
}

// Limpia los campos del formulario modal
function limpiarFormularioApartados()
{
    $('#idApartado').value = "";
    $('#titulo').value = "";
    $('#categoria').value = "";
    $('#tipo').value = "";
    $('#subapartado').removeAttr("checked");
    $('#requerido').setAttribute("checked", "checked");    
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
document.getElementById("formapartado").on("submit", function(e)
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
	    document.getElementById("formapartadoprogramacion").modal('hide');
        cargarApartados();
    });
});

cargarApartados();