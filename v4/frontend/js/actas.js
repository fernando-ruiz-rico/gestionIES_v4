// Funciones para gestión de actas desde la vista "actas.php"
// IMPORTANTE: la función "mostrarMensaje" que se usa en algunas funciones viene incorporada desde
// el fichero js/main.js

// Hacemos que el campo "fecha" tenga un "datepicker" para elegir la fecha
if(document.getElementById('fecha'))
    document.getElementById('fecha').datepicker({dateFormat: "dd/mm/yy"});

// Función para rellenar el desplegable de fechas de actas disponibles para el departamento actual
function cargarActas()
{
    $.post('ajax/actas/cargar_actas_departamento.php', function(res)
    {
        document.getElementById('fechasActas').innerHTML = res;
    });
}

// Función para cargar el acta seleccionada
function cambiarActa(edicion)
{
    var selActa = dom('#fechasActas').val();
    dom('#idActa').val(selActa);
    if (selActa != "")
    {
        document.getElementById('edicionacta').style.display = 'block';
        $.post('ajax/actas/cargar_fecha_acta.php', {idActa: selActa}, function(res)
        {
            document.getElementById('fecha').value = res;
        });
        $.post('ajax/actas/cargar_contenido_acta.php', {idActa: selActa}, function(res)
        {
            if (tinymce.get('texto'))
                tinymce.get('texto').setContent(res);
        });
    } else {
        document.getElementById('edicionacta').style.display = 'none';
        document.getElementById('fecha').value = "";
        if (tinymce.get('texto'))
            tinymce.get('texto').setContent("");        
    }
}

// Función para preparar el formulario con datos de una nueva acta
function nuevaActa()
{
    $.post('ajax/actas/nueva_acta_departamento.php', function(res)
    {
        document.getElementById('edicionacta').style.display = 'block';
        document.getElementById('idActa').value = "";
        document.getElementById('fecha').value = "";
        if (tinymce.get('texto'))
            tinymce.get('texto').setContent(res);
    });
}

// Función para generar el PDF con el contenido del acta
function generarPDFActa()
{
    var selActa = dom('#fechasActas').val();
    if (selActa <= 0)
        mostrarMensaje("Debes seleccionar una fecha", 2);
    else
        GestionIES.open('pdf_acta.php?idActa=' + selActa);
}

// Envío del formulario para el acta
$("#formacta").addEventListener('submit', function(e) {
    tinymce.get('texto').save();
    e.preventDefault();

    if(dom('#fecha').val() == "")
    {
        mostrarMensaje("Debes establecer una fecha para el acta", 2);
    }
    else
    {
        var formData = new FormData(document.forms.formacta);
        $.ajax({
            url: "ajax/actas/insertar_acta_departamento.php",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(res){            
            if (res.trim() == 'si' || res.trim() == '0')
                mostrarMensaje("Error al realizar la operación indicada", 0);
            else
            {
                dom('#idActa').val(res.trim());                
                mostrarMensaje("Datos guardados correctamente", 1);
            }
            cargarActas();
        });
    }
});

// Configuración de TinyMCE si procede
if(dom('#edicionacta').length > 0)
{
    initTinyMCE('textoacta');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos una fecha de acta (y aparecerá cargado con los datos de ese acta)
    // o si elegimos crear nueva acta (y aparecerá con el contenido inicial por defecto de las actas)
    dom('#edicionacta').hide();
}

cargarActas();