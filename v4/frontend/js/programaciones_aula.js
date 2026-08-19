// Funciones para la gestión de las programaciones de aula

let selMateria = 0;  // Materia seleccionada
let selGrupo = 0;    // Grupo seleccionado
let selTema = 0;     // Tema seleccionado

// Función para recargar la página con el profesor seleccionado en el desplegable (si lo hay)
function seleccionarProfesor()
{
    let nuevoProfesor = dom('#seleccionProfesor').val();
    dom('#idProfesor').val(nuevoProfesor);
    if (nuevoProfesor != "")
    {
        GestionIES.navigate("programaciones_aula.php?idProfesor=" + nuevoProfesor);
    }
}

// Cambia la materia seleccionada
function cambiarMateria()
{    
    selMateria = dom('#materia').val();
    dom('#grupo').val("");
    selGrupo = 0;
    dom('#idMateria').val(selMateria);
    dom('#idGrupo').val(selGrupo);
    dom('#ediciontema').hide();
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");

    // Actualizamos los grupos según la materia elegida
    dom('#grupo').prop('disabled', true).html('<option value="0">Cargando…</option>');
    http.ajax({ url: 'ajax/programaciones_aula/cargar_grupos.php', method: 'POST', dataType: 'json',
        data: { idMateria: selMateria, idProfesor: selProfesor }})
    .done(function (res) {
        let opciones = '<option value="0">--Selecciona un grupo--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id}">${a.nombre}</option>`;
        });
        dom('#grupo').html(opciones).prop('disabled', false);   
    });
    http.ajax({ url: 'ajax/programaciones_aula/cargar_temas.php', method: 'POST', dataType: 'json',
        data: { idMateria: selMateria }})
    .done(function (res) {
        let opciones = '<option value="0">--Selecciona una unidad o tema--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id}">${a.orden}. ${a.titulo}</option>`;
        });
        dom('#tema').html(opciones).prop('disabled', false);   
    });
}

// Cambiar el grupo seleccionado
function cambiarGrupo()
{
    selGrupo = dom('#grupo').val();
    dom('#idGrupo').val(selGrupo);
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");
    cargarContenido();
}

// Cambiar el tema seleccionado
function cambiarTema()
{
    selTema = dom('#tema').val();
    dom('#idTema').val(selTema);
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");
    cargarContenido();
}

// Comprueba si debe cargar contenido en el editor TinyMCE
function cargarContenido()
{
    if(selMateria > 0 && selGrupo > 0 /*&& selTema >= 0*/)
    {
        http.post('ajax/programaciones_aula/cargar_contenido_programacion.php', {idTema: selTema, idGrupo: selGrupo, idProfesor: selProfesor}, function(res)
        {
            dom('#ediciontema').show();
            if (tinymce.get('texto'))
                tinymce.get('texto').setContent(res);
        });
    }
    else
    {
        dom('#ediciontema').hide();
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
        GestionIES.open('pdf_separata_ce.php?idMateria=' + selMateria + "&idGrupo=" + selGrupo + "&idProfesor=" + selProfesor);
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
        GestionIES.open('pdf_programaciones_aula.php?idMateria=' + selMateria + "&idGrupo=" + selGrupo + "&idProfesor=" + selProfesor);
    }
}

// Guardar cambios al contenido editado
dom("#formprogramacionaula").on("submit", function(e)
{
    tinymce.get('texto').save();
    e.preventDefault();
    if (selProfesor <= 0 || selMateria <= 0 || selGrupo <= 0 /*|| selTema <= 0*/)
        mostrarMensaje("Debes seleccionar una materia y un grupo", 2);
    else
    {
        var formData = new FormData(document.forms.formprogramacionaula);
        http.ajax({
            url: "ajax/programaciones_aula/insertar_contenido_programacion.php",
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(res){
            if (res.trim() == 'si')
                mostrarMensaje("Error al realizar la operación indicada. Si no has hecho cambios respecto al contenido previamente guardado, ignora este mensaje", 0);
            else
                mostrarMensaje("Datos guardados correctamente", 1);
        });
    }
});

// Configuración de TinyMCE si procede
if(dom('#ediciontema').length > 0)
{
    initTinyMCE('progeditar');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    dom('#ediciontema').hide();
}
