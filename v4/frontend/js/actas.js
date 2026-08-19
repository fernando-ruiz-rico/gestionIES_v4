// Funciones para gestión de actas desde la vista "actas.php"
// IMPORTANTE: la función "mostrarMensaje" que se usa en algunas funciones viene incorporada desde
// el fichero js/main.js

// Hacemos que el campo "fecha" tenga un "datepicker" para elegir la fecha
const fechaInput = document.getElementById('fecha');
if(fechaInput) {
    // Usamos input type="date" nativo del navegador
    fechaInput.type = 'date';
    // Si queremos formato dd/mm/yyyy, podemos usar un patrón
    fechaInput.setAttribute('pattern', '\\d{2}/\\d{2}/\\d{4}');
}

// Función para rellenar el desplegable de fechas de actas disponibles para el departamento actual
function cargarActas()
{
    fetch('ajax/actas/cargar_actas_departamento.php')
    .then(res => res.text())
    .then(html => {
        document.getElementById('fechasActas').innerHTML = html;
    })
    .catch(err => console.error('Error al cargar actas:', err));
}

// Función para cargar el acta seleccionada
function cambiarActa(edicion)
{
    var selActa = document.getElementById('fechasActas').value;
    document.getElementById('idActa').value = selActa;
    if (selActa != "")
    {
        document.getElementById('edicionacta').style.display = "block";
        
        fetch('ajax/actas/cargar_fecha_acta.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'idActa=' + encodeURIComponent(selActa)
        })
        .then(res => res.text())
        .then(fecha => {
            document.getElementById('fecha').value = fecha;
        });
        
        fetch('ajax/actas/cargar_contenido_acta.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'idActa=' + encodeURIComponent(selActa)
        })
        .then(res => res.text())
        .then(contenido => {
            if (tinymce.get('texto'))
                tinymce.get('texto').setContent(contenido);
        });
    } else {
        document.getElementById('edicionacta').style.display = "none";
        document.getElementById('fecha').value = "";
        if (tinymce.get('texto'))
            tinymce.get('texto').setContent("");        
    }
}

// Función para preparar el formulario con datos de una nueva acta
function nuevaActa()
{
    fetch('ajax/actas/nueva_acta_departamento.php', {method: 'POST'})
    .then(res => res.text())
    .then(contenido => {
        document.getElementById('edicionacta').style.display = "block";
        document.getElementById('idActa').value = "";
        document.getElementById('fecha').value = "";
        if (tinymce.get('texto'))
            tinymce.get('texto').setContent(contenido);
    });
}

// Función para generar el PDF con el contenido del acta
function generarPDFActa()
{
    var selActa = document.getElementById('fechasActas').value;
    if (selActa <= 0 || selActa === "")
        mostrarMensaje("Debes seleccionar una fecha", 2);
    else
        window.open('pdf_acta.php?idActa=' + selActa);
}

// Envío del formulario para el acta
document.getElementById("formacta").addEventListener("submit", function(e)
{
    tinymce.get('texto').save();
    e.preventDefault();

    if(document.getElementById('fecha').value == "")
    {
        mostrarMensaje("Debes establecer una fecha para el acta", 2);
    }
    else
    {
        var formData = new FormData(document.forms.formacta);
        fetch("ajax/actas/insertar_acta_departamento.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(res => {
            if (res.trim() == 'si' || res.trim() == '0')
                mostrarMensaje("Error al realizar la operación indicada", 0);
            else
            {
                document.getElementById('idActa').value = res.trim();                
                mostrarMensaje("Datos guardados correctamente", 1);
            }
            cargarActas();
        })
        .catch(err => console.error('Error al guardar:', err));
    }
});

// Configuración de TinyMCE si procede
const edicionActaDiv = document.getElementById('edicionacta');
if(edicionActaDiv)
{
    initTinyMCE('textoacta');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos una fecha de acta (y aparecerá cargado con los datos de ese acta)
    // o si elegimos crear nueva acta (y aparecerá con el contenido inicial por defecto de las actas)
    edicionActaDiv.style.display = "none";
}

cargarActas();