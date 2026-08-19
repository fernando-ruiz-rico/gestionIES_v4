// Funciones para la gestión de las programaciones de aula

let selMateria = 0;  // Materia seleccionada
let selGrupo = 0;    // Grupo seleccionado
let selTema = 0;     // Tema seleccionado

// Función para recargar la página con el profesor seleccionado en el desplegable (si lo hay)
function seleccionarProfesor()
{
    let nuevoProfesor = document.getElementById('seleccionProfesor').value;
    document.getElementById('idProfesor').value = nuevoProfesor;
    if (nuevoProfesor != "")
    {
        window.location.href = "programaciones_aula.php?idProfesor=" + nuevoProfesor;
    }
}

// Cambia la materia seleccionada
function cambiarMateria()
{    
    selMateria = document.getElementById('materia').value;
    document.getElementById('grupo').value = '';
    selGrupo = 0;
    document.getElementById('idMateria').value = selMateria;
    document.getElementById('idGrupo').value = selGrupo;
    document.getElementById('ediciontema').style.display = 'none';
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");

    // Actualizamos los grupos según la materia elegida
    document.getElementById('grupo').prop('disabled', true).innerHTML = '<option value="0">Cargando…</option>';
    fetch("ajax/programaciones_aula/cargar_grupos.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" } }).then(r => r.json())
        data: { idMateria: selMateria, idProfesor: selProfesor }})
    .then(function(res) {
        let opciones = '<option value="0">--Selecciona un grupo--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id}">${a.nombre}</option>`;
        });
        document.getElementById('grupo').innerHTML = opciones.prop('disabled', false);   
    });
    fetch("ajax/programaciones_aula/cargar_temas.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" } }).then(r => r.json())
        data: { idMateria: selMateria }})
    .then(function(res) {
        let opciones = '<option value="0">--Selecciona una unidad o tema--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id}">${a.orden}. ${a.titulo}</option>`;
        });
        document.getElementById('tema').innerHTML = opciones.prop('disabled', false);   
    });
}

// Cambiar el grupo seleccionado
function cambiarGrupo()
{
    selGrupo = document.getElementById('grupo').value;
    document.getElementById('idGrupo').value = selGrupo;
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");
    cargarContenido();
}

// Cambiar el tema seleccionado
function cambiarTema()
{
    selTema = document.getElementById('tema').value;
    document.getElementById('idTema').value = selTema;
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");
    cargarContenido();
}

// Comprueba si debe cargar contenido en el editor TinyMCE
function cargarContenido()
{
    if(selMateria > 0 && selGrupo > 0 /*&& selTema >= 0*/)
    {
        fetch("ajax/programaciones_aula/cargar_contenido_programacion.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idTema: selTema, idGrupo: selGrupo, idProfesor: selProfesor}).toString() }).then(r => r.text()).then(res => {
            document.getElementById('ediciontema').style.display = 'block';
            if (tinymce.get('texto'))
                tinymce.get('texto').setContent(res);
        });
    }
    else
    {
        document.getElementById('ediciontema').style.display = 'none';
    }
}

// Genera el PDF con la separata de criterios de evaluación
function generarPDFSeparataCE()
{
    if(selMateria <= 0 || selGrupo <= 0)
    {
        mostrarMensaje("Debes seleccionar una materia y grupo", 2);
    }
    else
    {
        window.open('pdf_separata_ce.php?idMateria=' + selMateria + "&idGrupo=" + selGrupo + "&idProfesor=" + selProfesor);
    }
}

// Genera el PDF con la programación de aula
function generarPDF()
{
    if(selMateria <= 0 || selGrupo <= 0)
    {
        mostrarMensaje("Debes seleccionar una materia y grupo", 2);
    }
    else
    {
        window.open('pdf_programaciones_aula.php?idMateria=' + selMateria + "&idGrupo=" + selGrupo + "&idProfesor=" + selProfesor);
    }
}

// Guardar cambios al contenido editado
document.getElementById("formprogramacionaula").addEventListener("submit", function(e)
{
    tinymce.get('texto').save();
    e.preventDefault();
    if (selProfesor <= 0 || selMateria <= 0 || selGrupo <= 0 /*|| selTema <= 0*/)
        mostrarMensaje("Debes seleccionar una materia y un grupo", 2);
    else
    {
        var formData = new FormData(document.forms.formprogramacionaula);
        fetch("ajax/programaciones_aula/insertar_contenido_programacion.php", { method: "POST", body: formData })
        .then(function(res) {
            if (res.trim() == 'si')
                mostrarMensaje("Error al realizar la operación indicada. Si no has hecho cambios respecto al contenido previamente guardado, ignora este mensaje", 0);
            else
                mostrarMensaje("Datos guardados correctamente", 1);
        });
    }
});

// Configuración de TinyMCE si procede
if(document.getElementById('ediciontema').length > 0)
{
    initTinyMCE('progeditar');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    document.getElementById('ediciontema').style.display = 'none';
}
