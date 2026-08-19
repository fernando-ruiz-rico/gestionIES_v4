// Funciones para la gestión de los apartados de los PCCF

// Carga los apartados en el "div" habilitado para ello
function cargarApartados()
{
    $("#apartadospccf").load("ajax/pccf_apartados/cargar_apartados.php");
}

// Muestra los datos de un apartado en el formulario modal
function cargarApartadoModal(id)
{
    fetch("ajax/pccf_apartados/cargar_apartado.php?" + new URLSearchParams(idApartado:id)).then(response => response.text()).then(res => {
        document.getElementById('idApartado').value = id;
        document.getElementById('titulo').value = res.titulo;
        document.getElementById('tipo').value = res.tipo;
        if (res.subapartado == 1)
            document.getElementById('subapartado').prop('checked', true);
        else
            document.getElementById('subapartado').prop('checked', false);
        if (res.requerido == 1)
            document.getElementById('requerido').prop('checked', true);
        else
            document.getElementById('requerido').prop('checked', false);
        if (res.contenido_defecto == 1)
            document.getElementById('contenidoDefecto').prop('checked', true);
        else
            document.getElementById('contenidoDefecto').prop('checked', false);
        $("#formapartadopccf").show();
    });    
}

// Muestra el formulario modal para crear un nuevo apartado
function nuevoApartado()
{
    limpiarFormularioApartados();
    document.getElementById('formapartadopccf').show();
}

// Borra un apartado, previa confirmación
function borrarApartado (id, titulo)
{
    if (confirm("Confirmas el borrado del apartado '" + titulo + "'? Se eliminarán todos los contenidos relativos a dicho apartado."))
    {
        $.post("ajax/pccf_apartados/borrar_apartado.php", {id:id}, function(res)
        {
            cargarApartados();
        });            
    }
}

// Limpia los campos del formulario modal
function limpiarFormularioApartados()
{
    document.getElementById('idApartado').value = "";
    document.getElementById('titulo').value = "";
    document.getElementById('tipo').value = "";
    document.getElementById('subapartado').removeAttr("checked");
    document.getElementById('requerido').attr("checked", "checked");    
    document.getElementById('contenidoDefecto').removeAttr("checked");
}

// Evento de ordenación de los apartados
document.getElementById('apartadospccf').sortable({ items: '.apartado', update: function()
    {
        var elementos = $(this).sortable("toArray").toString();
        $.get("ajax/pccf_apartados/ordenar_apartados.php", {orden: elementos}, function()
        {
            cargarApartados();
        });
    }
});

// Evento de envío del formulario modal para crear/modificar apartados
$("#formapartado").addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(document.forms.formapartado);
    $.ajax({
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
	    $("#formapartadopccf").hide();
        cargarApartados();
    });
});

cargarApartados();