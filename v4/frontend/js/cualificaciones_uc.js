// Funciones para gestión de cualificaciones profesionales y unidades de competencia
// desde la vista "cualificaciones_uc.php"

// Carga el listado de cualificaciones en el "div" habilitado para ello
function cargarCualificaciones()
{
    $("#listaprincipal").load("ajax/cualificaciones_uc/cargar_cualificaciones.php");
}

// Carga el listado de unidades de competencia en el "div" habilitado para ello
function cargarUnidades()
{
    $("#listaprincipal").load("ajax/cualificaciones_uc/cargar_unidades.php");
}

// Muestra los datos de la cualificación en el formulario modal, para su edición
function cargarCualificacionModal(id)
{
    fetch("ajax/cualificaciones_uc/cargar_cualificacion.php?" + new URLSearchParams(codigo:id)).then(response => response.text()).then(res => {
        document.getElementById('idCualificacion').value = id;
        document.getElementById('codigoCualificacion').value = res.codigo;
        document.getElementById('textoCualificacion').value = res.texto;
        $("#formcualificacion").show();
    });    
}

// Muestra los datos de la unidad en el formulario modal, para su edición
function cargarUnidadModal(id)
{
    fetch("ajax/cualificaciones_uc/cargar_unidad.php?" + new URLSearchParams(codigo:id)).then(response => response.text()).then(res => {
        document.getElementById('idUnidad').value = id;
        document.getElementById('codigoUnidad').value = res.codigo;
        document.getElementById('textoUnidad').value = res.texto;
        $("#formunidad").show();
    });    
}

// Muestra el formulario modal limpio para insertar una nueva cualificación
function nuevaCualificacion()
{
    limpiarFormularioCualificaciones();
    document.getElementById('formcualificacion').show();
}

// Muestra el formulario modal limpio para insertar una nueva unidad
function nuevaUnidad()
{
    limpiarFormularioUnidades();
    document.getElementById('formunidad').show();
}

// Borra la cualificación indicada, previa confirmación
function borrarCualificacion(id)
{
    if (confirm("Confirmas el borrado de la cualificación '" + id + "'? Sólo se podrá eliminar si no tiene unidades de competencia asociadas. En caso contrario, deberás borrar estos elementos antes."))
    {
        $.post("ajax/cualificaciones_uc/borrar_cualificacion.php", {codigo:id}, function(res)
        {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar la cualificación. Asegúrate de que no tenga unidades asociadas", 0);
            else
                cargarCualificaciones();
        });            
    }
}

// Borra la unidad indicada, previa confirmación
function borrarUnidad(id)
{
    if (confirm("Confirmas el borrado de la unidad '" + id + "'?"))
    {
        $.post("ajax/cualificaciones_uc/borrar_unidad.php", {codigo:id}, function(res)
        {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar la unidad", 0);
            else
                cargarUnidades();
        });            
    }
}

// Borra el contenido de los campos del formulario modal de cualificaciones
function limpiarFormularioCualificaciones()
{
    document.getElementById('idCualificacion').value = "";
    document.getElementById('codigoCualificacion').value = "";
    document.getElementById('textoCualificacion').value = "";
}

// Borra el contenido de los campos del formulario modal de cualificaciones
function limpiarFormularioUnidades()
{
    document.getElementById('idUnidad').value = "";
    document.getElementById('codigoUnidad').value = "";
    document.getElementById('textoUnidad').value = "";
}

// Asocia unidades de competencia a una cualificación profesional
function asociarUnidades(idCualificacion)
{
    fetch("ajax/cualificaciones_uc/cargar_asociaciones_cualificacion.php?" + new URLSearchParams(codigo:idCualificacion)).then(response => response.text()).then(res => {
        $("#asociaciones").innerHTML = res;
        $("#formcualuni").show();
    });    
}

// Añade una nueva asociación de unidad de competencia a una cualificación
function nuevaAsociacion(codigoCualificacion)
{
    let codigoUnidad = document.getElementById('codigoAsociacion').value;
    if(codigoUnidad != "")
    {
        $.post("ajax/cualificaciones_uc/nueva_asociacion.php", {codigoCualificacion:codigoCualificacion, codigoUnidad: codigoUnidad}, function(res)
        {
            asociarUnidades(codigoCualificacion);
        });
    }
}

// Elimina una asociación de unidad de competencia a cualificación
function borrarAsociacion(codigoCualificacion, codigoUnidad)
{
    $.post("ajax/cualificaciones_uc/borrar_asociacion.php", {codigoCualificacion:codigoCualificacion, codigoUnidad: codigoUnidad}, function(res)
    {
        asociarUnidades(codigoCualificacion);
    });
}

// Evento de envío del formulario modal para inserción/modificación de cualificaciones
$("#formcua").addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(document.forms.formcua);
    $.ajax({
        url: "ajax/cualificaciones_uc/insertar_cualificacion.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioCualificaciones();
        $("#formcualificacion").hide();
        cargarCualificaciones();
    });
});

// Evento de envío del formulario modal para inserción/modificación de unidades de competencia
$("#formuni").addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(document.forms.formuni);
    $.ajax({
        url: "ajax/cualificaciones_uc/insertar_unidad.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioUnidades();
        $("#formunidad").hide();
        cargarUnidades();
    });
});
