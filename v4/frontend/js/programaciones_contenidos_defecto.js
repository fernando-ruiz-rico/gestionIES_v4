// Funciones para gestionar los contenidos por defecto de los apartados de programaciones

// Cargamos en el campo "hidden" del formulario el departamento seleccionado
if(selDepartamento !== undefined)
    document.getElementById('idDepartamento').value = selDepartamento;
// Variable para guardar el apartado elegido
var selApartado = 0;

// Cambia el apartado seleccionado
function cambiarApartado()
{
    selApartado = document.getElementById('seleccionApartado').value;
    if (selApartado > 0)
    {
        document.getElementById('edicionapartado').style.display = 'block';
        if (tinymce.get('texto'))
            tinymce.get('texto').setContent("");
        document.getElementById('idApartado').value = selApartado;
        fetch("ajax/programaciones_contenidos_defecto/cargar_contenido_defecto_programacion.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idDepartamento: selDepartamento, idApartado: selApartado}).toString() }).then(r => r.text()).then(res => {
            if (tinymce.get('texto'))
                tinymce.get('texto').setContent(res);
        });
    }
    else
    {
        document.getElementById('edicionapartado').style.display = 'none';
    }
}

// Evento de envío del formulario para guardar los cambios
document.getElementById("formprogramaciondefault").addEventListener("submit", function(e)
{
    tinymce.get('texto').save();
    e.preventDefault();
    if (selApartado <= 0)
        mostrarMensaje("Debes seleccionar un apartado", 2);
    else
    {
        var formData = new FormData(document.forms.formprogramaciondefault);
        fetch("ajax/programaciones_contenidos_defecto/insertar_contenido_defecto_programacion.php", { method: "POST", body: formData })
        .then(function(res) {
            if (res.trim() == 'si')
                mostrarMensaje("Error al realizar la operación indicada", 0);
            else
                mostrarMensaje("Datos guardados correctamente", 1);
        });
    }
});

// Configuración de TinyMCE si procede
if(document.getElementById('edicionapartado').length > 0)
{
    initTinyMCE('progeditar');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    document.getElementById('edicionapartado').style.display = 'none';
}