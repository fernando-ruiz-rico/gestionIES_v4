// Funciones para gestión de cualificaciones profesionales y unidades de competencia
// desde la vista "cualificaciones_uc.php"

// Carga el listado de cualificaciones en el "div" habilitado para ello
function cargarCualificaciones()
{
    fetch("ajax/cualificaciones_uc/cargar_cualificaciones.php").then(r => r.text()).then(html => document.getElementById("listaprincipal").innerHTML = html);
}

// Carga el listado de unidades de competencia en el "div" habilitado para ello
function cargarUnidades()
{
    fetch("ajax/cualificaciones_uc/cargar_unidades.php").then(r => r.text()).then(html => document.getElementById("listaprincipal").innerHTML = html);
}

// Muestra los datos de la cualificación en el formulario modal, para su edición
function cargarCualificacionModal(id)
{
    fetch("ajax/cualificaciones_uc/cargar_cualificacion.php?" + new URLSearchParams({codigo:id}).toString()).then(r => r.json()).then(res => {
        document.getElementById('idCualificacion').value = id;
        document.getElementById('codigoCualificacion').value = res.codigo;
        document.getElementById('textoCualificacion').value = res.texto;
        (() => { const el = document.getElementById("formcualificacion"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Muestra los datos de la unidad en el formulario modal, para su edición
function cargarUnidadModal(id)
{
    fetch("ajax/cualificaciones_uc/cargar_unidad.php?" + new URLSearchParams({codigo:id}).toString()).then(r => r.json()).then(res => {
        document.getElementById('idUnidad').value = id;
        document.getElementById('codigoUnidad').value = res.codigo;
        document.getElementById('textoUnidad').value = res.texto;
        (() => { const el = document.getElementById("formunidad"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Muestra el formulario modal limpio para insertar una nueva cualificación
function nuevaCualificacion()
{
    limpiarFormularioCualificaciones();
    (() => { const el = document.getElementById("formcualificacion"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
}

// Muestra el formulario modal limpio para insertar una nueva unidad
function nuevaUnidad()
{
    limpiarFormularioUnidades();
    (() => { const el = document.getElementById("formunidad"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
}

// Borra la cualificación indicada, previa confirmación
function borrarCualificacion(id)
{
    if (confirm("Confirmas el borrado de la cualificación '" + id + "'? Sólo se podrá eliminar si no tiene unidades de competencia asociadas. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch("ajax/cualificaciones_uc/borrar_cualificacion.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({codigo:id}).toString() }).then(r => r.text()).then(res => {
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
        fetch("ajax/cualificaciones_uc/borrar_unidad.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({codigo:id}).toString() }).then(r => r.text()).then(res => {
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
    document.getElementById('idCualificacion').value = '';
    document.getElementById('codigoCualificacion').value = '';
    document.getElementById('textoCualificacion').value = '';
}

// Borra el contenido de los campos del formulario modal de cualificaciones
function limpiarFormularioUnidades()
{
    document.getElementById('idUnidad').value = '';
    document.getElementById('codigoUnidad').value = '';
    document.getElementById('textoUnidad').value = '';
}

// Asocia unidades de competencia a una cualificación profesional
function asociarUnidades(idCualificacion)
{
    fetch("ajax/cualificaciones_uc/cargar_asociaciones_cualificacion.php?" + new URLSearchParams({codigo:idCualificacion}).toString()).then(r => r.json()).then(res => {
        document.getElementById("asociaciones").innerHTML = res;
        (() => { const el = document.getElementById("formcualuni"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Añade una nueva asociación de unidad de competencia a una cualificación
function nuevaAsociacion(codigoCualificacion)
{
    let codigoUnidad = document.getElementById('codigoAsociacion').value;
    if(codigoUnidad != "")
    {
        fetch("ajax/cualificaciones_uc/nueva_asociacion.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({codigoCualificacion:codigoCualificacion, codigoUnidad: codigoUnidad}).toString() }).then(r => r.text()).then(res => {
            asociarUnidades(codigoCualificacion);
        });
    }
}

// Elimina una asociación de unidad de competencia a cualificación
function borrarAsociacion(codigoCualificacion, codigoUnidad)
{
    fetch("ajax/cualificaciones_uc/borrar_asociacion.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({codigoCualificacion:codigoCualificacion, codigoUnidad: codigoUnidad}).toString() }).then(r => r.text()).then(res => {
        asociarUnidades(codigoCualificacion);
    });
}

// Evento de envío del formulario modal para inserción/modificación de cualificaciones
document.getElementById("formcua").addEventListener("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcua);
    fetch("ajax/cualificaciones_uc/insertar_cualificacion.php", { method: "POST", body: formData })
    .then(function(res) {
        limpiarFormularioCualificaciones();
        (() => { const el = document.getElementById("formcualificacion"); const modal = bootstrap.Modal.getInstance(el); if(modal) modal.hide(); })();
        cargarCualificaciones();
    });
});

// Evento de envío del formulario modal para inserción/modificación de unidades de competencia
document.getElementById("formuni").addEventListener("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formuni);
    fetch("ajax/cualificaciones_uc/insertar_unidad.php", { method: "POST", body: formData })
    .then(function(res) {
        limpiarFormularioUnidades();
        (() => { const el = document.getElementById("formunidad"); const modal = bootstrap.Modal.getInstance(el); if(modal) modal.hide(); })();
        cargarUnidades();
    });
});
