// Funciones para gestión de cualificaciones profesionales y unidades de competencia
// desde la vista "cualificaciones_uc.php"

// Carga el listado de cualificaciones en el "div" habilitado para ello
function cargarCualificaciones()
{
    dom("#listaprincipal").load("ajax/cualificaciones_uc/cargar_cualificaciones.php");
}

// Carga el listado de unidades de competencia en el "div" habilitado para ello
function cargarUnidades()
{
    dom("#listaprincipal").load("ajax/cualificaciones_uc/cargar_unidades.php");
}

// Muestra los datos de la cualificación en el formulario modal, para su edición
function cargarCualificacionModal(id)
{
    http.get("ajax/cualificaciones_uc/cargar_cualificacion.php", {codigo:id}, function(res)
    {
        dom('#idCualificacion').val(id);
        dom('#codigoCualificacion').val(res.codigo);
        dom('#textoCualificacion').val(res.texto);
        dom("#formcualificacion").modal('show');
    });    
}

// Muestra los datos de la unidad en el formulario modal, para su edición
function cargarUnidadModal(id)
{
    http.get("ajax/cualificaciones_uc/cargar_unidad.php", {codigo:id}, function(res)
    {
        dom('#idUnidad').val(id);
        dom('#codigoUnidad').val(res.codigo);
        dom('#textoUnidad').val(res.texto);
        dom("#formunidad").modal('show');
    });    
}

// Muestra el formulario modal limpio para insertar una nueva cualificación
function nuevaCualificacion()
{
    limpiarFormularioCualificaciones();
    dom('#formcualificacion').modal('show');
}

// Muestra el formulario modal limpio para insertar una nueva unidad
function nuevaUnidad()
{
    limpiarFormularioUnidades();
    dom('#formunidad').modal('show');
}

// Borra la cualificación indicada, previa confirmación
function borrarCualificacion(id)
{
    if (confirm("Confirmas el borrado de la cualificación '" + id + "'? Sólo se podrá eliminar si no tiene unidades de competencia asociadas. En caso contrario, deberás borrar estos elementos antes."))
    {
        http.post("ajax/cualificaciones_uc/borrar_cualificacion.php", {codigo:id}, function(res)
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
        http.post("ajax/cualificaciones_uc/borrar_unidad.php", {codigo:id}, function(res)
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
    dom('#idCualificacion').val("");
    dom('#codigoCualificacion').val("");
    dom('#textoCualificacion').val("");
}

// Borra el contenido de los campos del formulario modal de cualificaciones
function limpiarFormularioUnidades()
{
    dom('#idUnidad').val("");
    dom('#codigoUnidad').val("");
    dom('#textoUnidad').val("");
}

// Asocia unidades de competencia a una cualificación profesional
function asociarUnidades(idCualificacion)
{
    http.get("ajax/cualificaciones_uc/cargar_asociaciones_cualificacion.php", {codigo:idCualificacion}, function(res)
    {
        dom("#asociaciones").html(res);
        dom("#formcualuni").modal('show');
    });    
}

// Añade una nueva asociación de unidad de competencia a una cualificación
function nuevaAsociacion(codigoCualificacion)
{
    let codigoUnidad = dom('#codigoAsociacion').val();
    if(codigoUnidad != "")
    {
        http.post("ajax/cualificaciones_uc/nueva_asociacion.php", {codigoCualificacion:codigoCualificacion, codigoUnidad: codigoUnidad}, function(res)
        {
            asociarUnidades(codigoCualificacion);
        });
    }
}

// Elimina una asociación de unidad de competencia a cualificación
function borrarAsociacion(codigoCualificacion, codigoUnidad)
{
    http.post("ajax/cualificaciones_uc/borrar_asociacion.php", {codigoCualificacion:codigoCualificacion, codigoUnidad: codigoUnidad}, function(res)
    {
        asociarUnidades(codigoCualificacion);
    });
}

// Evento de envío del formulario modal para inserción/modificación de cualificaciones
dom("#formcua").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcua);
    http.ajax({
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
        dom("#formcualificacion").modal('hide');
        cargarCualificaciones();
    });
});

// Evento de envío del formulario modal para inserción/modificación de unidades de competencia
dom("#formuni").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formuni);
    http.ajax({
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
        dom("#formunidad").modal('hide');
        cargarUnidades();
    });
});
