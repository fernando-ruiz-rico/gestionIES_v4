// Funciones para gestionar los contenidos por defecto de los temas o unidades

// Cargamos en el campo "hidden" del formulario el departamento seleccionado
if(selDepartamento !== undefined)
    document.getElementById('idDepartamento').value = selDepartamento;

// Evento de envío del formulario para guardar los cambios
document.getElementById("formtemadefault").addEventListener("submit", function(e)
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
        fetch("ajax/temas_contenidos_defecto/insertar_contenido_defecto_tema.php", { method: "POST", body: formData })
        .then(function(res) {
            if (res.trim() == 'si')
                mostrarMensaje("Error al realizar la operación indicada", 0);
            else
                mostrarMensaje("Datos guardados correctamente", 1);
        });
    }
});

initTinyMCE('datostema');
