// Funciones para gestionar los contenidos de las programaciones

// Variables para almacenar materia y apartado a editar
let selMateria = 0;
let selApartado = 0;
let tipoApartado = 0;

// Cambia la materia seleccionada
function cambiarMateria()
{    
    selMateria = dom('#materia').val();
    dom('input[name="idMateria"]').val(selMateria);
    selApartado = 0;
    dom('#apartado').val(selApartado);
    dom('#edicionapartado').hide();
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");

    // Actualizamos los apartados según el tipo de materia elegida
    dom('#apartado').prop('disabled', true).html('<option value="0">Cargando…</option>');
    http.ajax({ url: 'ajax/programaciones/cargar_apartados.php', method: 'POST', dataType: 'json',
        data: { idMateria: selMateria }})
    .done(function (res) {
        let opciones = '<option value="0">--Selecciona un apartado--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id},${a.tipo}">${a.nombre}</option>`;
        });
        dom('#apartado').html(opciones).prop('disabled', false);   
    });
}

// Cambia el apartado seleccionado
function cambiarApartado()
{
    if (tinymce.get('texto'))
        tinymce.get('texto').setContent("");

    [selApartado, tipoApartado] = dom('#apartado').val().split(',').map(x => +x);

    if (tipoApartado == 0) {
        dom('#idApartado').val(selApartado);
        if (selMateria > 0 && selApartado > 0) {
            http.post('ajax/programaciones/cargar_contenido_programacion.php', {idMateria: selMateria, idApartado: selApartado}, function(res)
            {
                dom('#edicionapartado').show();
                dom('#mensajeapartadoautomatico').hide();
                if (tinymce.get('texto'))
                    tinymce.get('texto').setContent(res);
            });
        }
    }
    else {
        dom('#edicionapartado').hide();
        dom('#mensajeapartadoautomatico').show();
    }
}

// Genera una vista previa en HTML de la programación
function vistaPreviaProgramacion()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        GestionIES.open('programaciones_vista_previa.php?idMateria=' + selMateria);
}

// Genera un PDF con el contenido de la programación
function generarPDF()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        GestionIES.open('pdf_programaciones.php?idMateria=' + selMateria);
}

// Genera un PDF con el contenido de las unidades de programación
function generarPDFUnidades()
{
    if (selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia", 2);
    else
        GestionIES.open('pdf_unidades_programacion.php?idMateria=' + selMateria);
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
            GestionIES.open('pdf_unidades_programacion.php?idMateria=' + selMateria);
        }
        else {
            GestionIES.open('pdf_programaciones_apartado.php?idMateria=' + selMateria + '&idApartado=' + selApartado);
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
            dom('#idMateriaDestino').val(selMateria);
            dom('#formimportarprog').modal('show');
        }        
    }
}

// Carga otra pestaña para editar el contenido por defecto de las unidades o temas
function contenidoDefectoTemas()
{
    GestionIES.open('temas_contenidos_defecto.php');
}

// Mostrar temas
dom("#temas").on("submit", function(e)
{
    if(selMateria <= 0)
    {
        mostrarMensaje("Debes seleccionar una materia", 2);
        e.preventDefault();
    }
    else
    {
        dom('#idMateria').val(selMateria);
        e.preventDefault();
        GestionIES.open('temas.php?idMateria=' + selMateria);
    }
});


// Guardar cambios al contenido editado
dom("#formprogramacion").on("submit", function(e)
{
    tinymce.get('texto').save();
    e.preventDefault();
    if (selApartado <= 0 || selMateria <= 0)
        mostrarMensaje("Debes seleccionar una materia y un apartado", 2);
    else
    {
        var formData = new FormData(document.forms.formprogramacion);

        http.ajax({
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
dom("#formimpprog").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formimpprog);
    http.ajax({
        url: "ajax/programaciones/importar_programacion.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        dom('#idMateriaOrigen').val("");
        dom('#idMateriaDestino').val("");
        dom("#formimportarprog").modal('hide');
        dom('#edicionapartado').hide();
        mostrarMensaje("Operación completada", 1);
        cambiarApartado();
    });
});

// Configuración de TinyMCE si procede
if(dom('#edicionapartado').length > 0)
{
    initTinyMCE('progeditar');

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    dom('#edicionapartado').hide();
}
