// Funciones para gestión de actas desde la vista "actas.php"
// IMPORTANTE: la función "mostrarMensaje" que se usa en algunas funciones viene incorporada desde
// el fichero js/main.js

// Hacemos que el campo "fecha" tenga un "datepicker" para elegir la fecha
const fechaInput = document.getElementById('fecha');
if (fechaInput) {
    // Usamos flatpickr si está disponible, o fallback a input type="date" nativo
    if (typeof flatpickr !== 'undefined') {
        flatpickr(fechaInput, {
            dateFormat: "d/m/Y",
            locale: { firstDayOfWeek: 1 }
        });
    } else {
        fechaInput.type = 'date';
        fechaInput.setAttribute('data-date-format', 'dd/mm/yy');
    }
}

// Función para rellenar el desplegable de fechas de actas disponibles para el departamento actual
function cargarActas()
{
    fetch('ajax/actas/cargar_actas_departamento.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.text())
    .then(res => {
        const fechasActas = document.getElementById('fechasActas');
        if (fechasActas) {
            fechasActas.innerHTML = res;
        }
    })
    .catch(error => {
        console.error('Error al cargar actas:', error);
        mostrarMensaje("Error al cargar las actas", 0);
    });
}

// Función para cargar el acta seleccionada
function cambiarActa(edicion)
{
    const fechasActas = document.getElementById('fechasActas');
    const idActaInput = document.getElementById('idActa');
    const fechaInput = document.getElementById('fecha');
    const edicionacta = document.getElementById('edicionacta');
    
    if (!fechasActas || !idActaInput || !edicionacta) return;
    
    const selActa = fechasActas.value;
    idActaInput.value = selActa;
    
    if (selActa != "" && selActa != "0")
    {
        edicionacta.style.display = 'block';
        
        // Cargar fecha del acta
        fetch('ajax/actas/cargar_fecha_acta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'idActa=' + encodeURIComponent(selActa)
        })
        .then(response => response.text())
        .then(res => {
            if (fechaInput) {
                fechaInput.value = res.trim();
            }
        });
        
        // Cargar contenido del acta
        fetch('ajax/actas/cargar_contenido_acta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'idActa=' + encodeURIComponent(selActa)
        })
        .then(response => response.text())
        .then(res => {
            if (tinymce.get('texto')) {
                tinymce.get('texto').setContent(res);
            }
        });
    } else {
        edicionacta.style.display = 'none';
        if (fechaInput) {
            fechaInput.value = "";
        }
        if (tinymce.get('texto')) {
            tinymce.get('texto').setContent("");
        }
    }
}

// Función para preparar el formulario con datos de una nueva acta
function nuevaActa()
{
    const idActaInput = document.getElementById('idActa');
    const fechaInput = document.getElementById('fecha');
    const edicionacta = document.getElementById('edicionacta');
    
    fetch('ajax/actas/nueva_acta_departamento.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.text())
    .then(res => {
        if (edicionacta) {
            edicionacta.style.display = 'block';
        }
        if (idActaInput) {
            idActaInput.value = "";
        }
        if (fechaInput) {
            fechaInput.value = "";
        }
        if (tinymce.get('texto')) {
            tinymce.get('texto').setContent(res);
        }
    })
    .catch(error => {
        console.error('Error al crear nueva acta:', error);
        mostrarMensaje("Error al crear nueva acta", 0);
    });
}

// Función para generar el PDF con el contenido del acta
function generarPDFActa()
{
    const fechasActas = document.getElementById('fechasActas');
    if (!fechasActas) return;
    
    const selActa = fechasActas.value;
    if (selActa <= 0 || selActa == "") {
        mostrarMensaje("Debes seleccionar una fecha", 2);
    } else {
        window.open('pdf_acta.php?idActa=' + selActa);
    }
}

// Envío del formulario para el acta
const formActa = document.getElementById('formacta');
if (formActa) {
    formActa.addEventListener('submit', function(e) {
        e.preventDefault();

        if (tinymce.get('texto')) {
            tinymce.get('texto').save();
        }

        const fechaInput = document.getElementById('fecha');
        if (!fechaInput || fechaInput.value == "") {
            mostrarMensaje("Debes establecer una fecha para el acta", 2);
            return;
        }

        const formData = new FormData(formActa);
        
        fetch('ajax/actas/insertar_acta_departamento.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(res => {
            res = res.trim();
            if (res == 'si' || res == '0') {
                mostrarMensaje("Error al realizar la operación indicada", 0);
            } else {
                const idActaInput = document.getElementById('idActa');
                if (idActaInput) {
                    idActaInput.value = res;
                }
                mostrarMensaje("Datos guardados correctamente", 1);
            }
            cargarActas();
        })
        .catch(error => {
            console.error('Error al guardar acta:', error);
            mostrarMensaje("Error al guardar el acta", 0);
        });
    });
}

// Configuración de TinyMCE si procede
const edicionacta = document.getElementById('edicionacta');
if (edicionacta) {
    initTinyMCE('textoacta');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos una fecha de acta (y aparecerá cargado con los datos de ese acta)
    // o si elegimos crear nueva acta (y aparecerá con el contenido inicial por defecto de las actas)
    edicionacta.style.display = 'none';
}

cargarActas();