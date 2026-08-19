// Funciones para gestionar los contenidos de las programaciones

// Variables para almacenar materia y apartado a editar
let selMateria = 0;
let selApartado = 0;
let tipoApartado = 0;

// Cambia la materia seleccionada
function cambiarMateria()
{    
    selMateria = $('#materia').value;
    $('input[name="idMateria"]').value = selMateria;
    selApartado = 0;
    $('#apartado').value = selApartado;
    $('#edicionapartado').style.display = "none";
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");

    // Actualizamos los apartados según el tipo de materia elegida
    $('#apartado').prop('disabled', true).innerHTML = '<option value="0">Cargando…</option>';
    $.ajax({ url: 'ajax/programaciones/cargar_apartados.php', method: 'POST', dataType: 'json',
        data: { idMateria: selMateria }})
    .done(function (res) {
        let opciones = '<option value="0">--Selecciona un apartado--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id},${a.tipo}">${a.nombre}</option>`;
        });
        $('#apartado').innerHTML = opciones.prop('disabled', false);   
    });
}

// Cambia el apartado seleccionado
function cambiarApartado()
{
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");

    [selApartado, tipoApartado] = $('#apartado').value.split(',').map(x => +x);

    if (tipoApartado == 0) {
        $('#idApartado').value = selApartado;
        if (selMateria > 0 && selApartado > 0) {
            $.post('ajax/programaciones/cargar_contenido_programacion.php', {idMateria: selMateria, idApartado: selApartado}, function(res)
            {
                $('#edicionapartado').style.display = "block";
                $('#mensajeapartadoautomatico').style.display = "none";
                if (tinymce.get('texto'))
                    tinymce.get('texto').setContent(res);
            });
        }
    }
    else {
        $('#edicionapartado').style.display = "none";
        $('#mensajeapartadoautomatico').style.display = "block";
    }
}

// Genera una vista previa en HTML de la programación
function vistaPreviaProgramacion()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        window.open('programaciones_vista_previa.php?idMateria=' + selMateria);
}

// Genera un PDF con el contenido de la programación
function generarPDF()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        window.open('pdf_programaciones.php?idMateria=' + selMateria);
}

// Genera un PDF con el contenido de las unidades de programación
function generarPDFUnidades()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        window.open('pdf_unidades_programacion.php?idMateria=' + selMateria);
}

// Genera un PDF con el contenido de un apartado en concreto
function generarPDFApartado()
{
    if (selMateria <= 0 || selApartado <= 0) {
        mostrarMensaje("Debes seleccionar una materia y un apartado", 2);
    }
    else {
        // Si el apartado es de temas, no se genera el PDF desde aquí
        if (tipoApartado == TIPO_APARTADO_TEMAS) {
            window.open('pdf_unidades_programacion.php?idMateria=' + selMateria);
        }
        else {
            window.open('pdf_programaciones_apartado.php?idMateria=' + selMateria + '&idApartado=' + selApartado);
        }
    }
}

// Importa otra programación en la materia seleccionada
async function importarProgramacion()
{
    if(selMateria <= 0)
    {
        mostrarMensaje("Debes seleccionar una materia", 2);
    }
    else
    {
        if (await confirmar("Al importar una programación se borrarán TODOS los contenidos de la programación para la materia actualmente seleccionada. ¿Deseas continuar?"))
        {
            $('#idMateriaDestino').value = selMateria;
            $('#formimportarprog').modal('show');
        }        
    }
}

// Carga otra pestaña para editar el contenido por defecto de las unidades o temas
function contenidoDefectoTemas()
{
    window.open('temas_contenidos_defecto.php');
}

// Mostrar temas
document.getElementById("temas").on("submit", function(e)
{
    if(selMateria <= 0)
    {
        mostrarMensaje("Debes seleccionar una materia", 2);
        e.preventDefault();
    }
    else
    {
        $('#idMateria').value = selMateria;
    }
});


// Guardar cambios al contenido editado
document.getElementById("formprogramacion").on("submit", function(e)
{
    tinymce.get('texto').save();
    e.preventDefault();
    if (selApartado <= 0 || selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia y un apartado", 2);
    else
    {
        var formData = new FormData(document.forms.formprogramacion);

        $.ajax({
            url: "ajax/programaciones/insertar_contenido_programacion.php",
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

// Guardar cambios al contenido editado
document.getElementById("formimpprog").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formimpprog);
    $.ajax({
        url: "ajax/programaciones/importar_programacion.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        $('#idMateriaOrigen').value = "";
        $('#idMateriaDestino').value = "";
        document.getElementById("formimportarprog").modal('hide');
        $('#edicionapartado').style.display = "none";
        mostrarMensaje("Operación completada", 1);
        cambiarApartado();
    });
});

// Configuración de TinyMCE si procede
if($('#edicionapartado').length > 0)
{
    initTinyMCE('progeditar');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    $('#edicionapartado').style.display = "none";
}
