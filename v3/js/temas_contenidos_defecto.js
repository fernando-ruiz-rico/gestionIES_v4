// Funciones para gestionar los contenidos por defecto de los temas o unidades

// Cargamos en el campo "hidden" del formulario el departamento seleccionado
if(selDepartamento !== undefined)
    $('#idDepartamento').val(selDepartamento);

// Evento de envío del formulario para guardar los cambios
$("#formtemadefault").on("submit", function(e)
{
    tinymce.get('contexto').save();
    tinymce.get('recursos').save();
    tinymce.get('metodologia').save();
    tinymce.get('adaptaciones').save();
    e.preventDefault();
    if (selDepartamento == undefined)
        mostrarMensaje("Debes seleccionar un departamento", 2);
    else
    {
        var formData = new FormData(document.forms.formtemadefault);
        $.ajax({
            url: "ajax/temas_contenidos_defecto/insertar_contenido_defecto_tema.php",
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

initTinyMCE('datostema');
