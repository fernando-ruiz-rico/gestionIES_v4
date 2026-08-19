// Funciones para gestión de cualificaciones profesionales y unidades de competencia
// desde la vista "cualificaciones_uc.php"

// Carga el listado de cualificaciones en el "div" habilitado para ello
function cargarCualificaciones()
{
    document.getElementById("listaprincipal").load("ajax/cualificaciones_uc/cargar_cualificaciones.php");
}

// Carga el listado de unidades de competencia en el "div" habilitado para ello
function cargarUnidades()
{
    document.getElementById("listaprincipal").load("ajax/cualificaciones_uc/cargar_unidades.php");
}

// Muestra los datos de la cualificación en el formulario modal, para su edición
function cargarCualificacionModal(id)
{
    fetch('ajax/cualificaciones_uc/cargar_cualificacion.php?' + new URLSearchParams({codigo:id})).then(r => r.json()).then(res =>
        $('#idCualificacion').value = id;
        $('#codigoCualificacion').value = res.codigo;
        $('#textoCualificacion').value = res.texto;
        document.getElementById("formcualificacion").modal('show');
    });    
}

// Muestra los datos de la unidad en el formulario modal, para su edición
function cargarUnidadModal(id)
{
    fetch('ajax/cualificaciones_uc/cargar_unidad.php?' + new URLSearchParams({codigo:id})).then(r => r.json()).then(res =>
        $('#idUnidad').value = id;
        $('#codigoUnidad').value = res.codigo;
        $('#textoUnidad').value = res.texto;
        document.getElementById("formunidad").modal('show');
    });    
}

// Muestra el formulario modal limpio para insertar una nueva cualificación
function nuevaCualificacion()
{
    limpiarFormularioCualificaciones();
    $('#formcualificacion').modal('show');
}

// Muestra el formulario modal limpio para insertar una nueva unidad
function nuevaUnidad()
{
    limpiarFormularioUnidades();
    $('#formunidad').modal('show');
}

// Borra la cualificación indicada, previa confirmación
function borrarCualificacion(id)
{
    if (confirm("Confirmas el borrado de la cualificación '" + id + "'? Sólo se podrá eliminar si no tiene unidades de competencia asociadas. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch('ajax/cualificaciones_uc/borrar_cualificacion.php', {method: 'POST', body: new URLSearchParams({codigo:id})}).then(r => r.text()).then(res =>
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
        fetch('ajax/cualificaciones_uc/borrar_unidad.php', {method: 'POST', body: new URLSearchParams({codigo:id})}).then(r => r.text()).then(res =>
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
    $('#idCualificacion').value = "";
    $('#codigoCualificacion').value = "";
    $('#textoCualificacion').value = "";
}

// Borra el contenido de los campos del formulario modal de cualificaciones
function limpiarFormularioUnidades()
{
    $('#idUnidad').value = "";
    $('#codigoUnidad').value = "";
    $('#textoUnidad').value = "";
}

// Asocia unidades de competencia a una cualificación profesional
function asociarUnidades(idCualificacion)
{
    fetch('ajax/cualificaciones_uc/cargar_asociaciones_cualificacion.php?' + new URLSearchParams({codigo:idCualificacion})).then(r => r.json()).then(res =>
        document.getElementById("asociaciones").innerHTML = res;
        document.getElementById("formcualuni").modal('show');
    });    
}

// Añade una nueva asociación de unidad de competencia a una cualificación
function nuevaAsociacion(codigoCualificacion)
{
    let codigoUnidad = $('#codigoAsociacion').value;
    if(codigoUnidad != "")
    {
        fetch('ajax/cualificaciones_uc/nueva_asociacion.php', {method: 'POST', body: new URLSearchParams({codigoCualificacion:codigoCualificacion, codigoUnidad: codigoUnidad})}).then(r => r.text()).then(res =>
            asociarUnidades(codigoCualificacion);
        });
    }
}

// Elimina una asociación de unidad de competencia a cualificación
function borrarAsociacion(codigoCualificacion, codigoUnidad)
{
    fetch('ajax/cualificaciones_uc/borrar_asociacion.php', {method: 'POST', body: new URLSearchParams({codigoCualificacion:codigoCualificacion, codigoUnidad: codigoUnidad})}).then(r => r.text()).then(res =>
        asociarUnidades(codigoCualificacion);
    });
}

// Evento de envío del formulario modal para inserción/modificación de cualificaciones
document.getElementById("formcua").on("submit", function(e)
{
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
        document.getElementById("formcualificacion").modal('hide');
        cargarCualificaciones();
    });
});

// Evento de envío del formulario modal para inserción/modificación de unidades de competencia
document.getElementById("formuni").on("submit", function(e)
{
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
        document.getElementById("formunidad").modal('hide');
        cargarUnidades();
    });
});
