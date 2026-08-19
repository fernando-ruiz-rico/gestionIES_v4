// Funciones para gestionar los contenidos por defecto de los apartados de programaciones

// Cargamos en el campo "hidden" del formulario el departamento seleccionado
if(selDepartamento !== undefined)
    dom('#idDepartamento').val(selDepartamento);
// Variable para guardar el apartado elegido
var selApartado = 0;

// Cambia el apartado seleccionado
function cambiarApartado()
{
    selApartado = dom('#seleccionApartado').val();
    if (selApartado > 0)
    {
        dom('#edicionapartado').show();
        if (tinymce.get('texto'))
            tinymce.get('texto').setContent("");
        dom('#idApartado').val(selApartado);
        http.post('ajax/programaciones_contenidos_defecto/cargar_contenido_defecto_programacion.php', {idDepartamento: selDepartamento, idApartado: selApartado}, function(res)
        {
            if (tinymce.get('texto'))
                tinymce.get('texto').setContent(res);
        });
    }
    else
    {
        dom('#edicionapartado').hide();
    }
}

// Evento de envío del formulario para guardar los cambios
dom("#formprogramaciondefault").on("submit", function(e)
{
    tinymce.get('texto').save();
    e.preventDefault();
    if (selApartado <= 0)
        mostrarMensaje("Debes seleccionar un apartado", 2);
    else
    {
        var formData = new FormData(document.forms.formprogramaciondefault);
        http.ajax({
            url: "ajax/programaciones_contenidos_defecto/insertar_contenido_defecto_programacion.php",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(res){
            if (res.trim() == 'si')
                mostrarMensaje("Error al realizar la operación indicada", 0);
            else
                mostrarMensaje("Datos guardados correctamente", 1);
        });
    }
});

// Configuración de TinyMCE si procede
if(dom('#edicionapartado').length > 0)
{
    initTinyMCE('progeditar');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    dom('#edicionapartado').hide();
}