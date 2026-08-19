// Funciones para gestión de ciclos desde la vista "ciclos.php"

// Carga el listado de ciclos en el "div" habilitado para ello
function cargarCiclos()
{
    fetch("ajax/ciclos/cargar_ciclos.php").then(r => r.text()).then(html => document.getElementById("listaciclos").innerHTML = html);
}

// Muestra los datos del ciclo indicado en el formulario modal, para su edición
function cargarCicloModal(id)
{
    fetch("ajax/ciclos/cargar_ciclo.php?" + new URLSearchParams({idCiclo:id}).toString()).then(r => r.json()).then(res => {
        document.getElementById('idCiclo').value = id;
        document.getElementById('nombre').value = res.nombre;
        document.getElementById('familia').value = res.familia;
        document.getElementById('nivel').value = res.nivel;
        (() => { const el = document.getElementById("formciclo"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Muestra el formulario modal limpio para insertar un nuevo ciclo
function nuevoCiclo()
{
    limpiarFormularioCiclos();
    (() => { const el = document.getElementById("formciclo"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
}

// Borra el ciclo indicado, previa confirmación
// El ciclo sólo podrá borrarse si no tiene vinculaciones con otras tablas importantes
function borrarCiclo (id, nombre)
{
    if (confirm("Confirmas el borrado del ciclo '" + nombre + "'? Sólo se podrá eliminar si no tiene cursos asociados. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch("ajax/ciclos/borrar_ciclo.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({id:id}).toString() }).then(r => r.text()).then(res => {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar el ciclo. Asegúrate de que no tenga cursos asociados", 0);
            else
                cargarCiclos();
        });            
    }
}

// Borra el contenido de los campos del formulario modal de alta/edición de ciclos
function limpiarFormularioCiclos()
{
    document.getElementById('idCiclo').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('familia').value = '';
    document.getElementById('nivel').value = '';    
}

// Asocia unidades de competencia a un ciclo
function asociarUnidades(idCiclo)
{
    fetch("ajax/ciclos/cargar_asociaciones_unidades.php?" + new URLSearchParams({idCiclo: idCiclo}).toString()).then(r => r.json()).then(res => {
        document.getElementById("asociaciones").innerHTML = res;
        (() => { const el = document.getElementById("formunicic"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Añade una nueva asociación de unidad de competencia a un ciclo
function nuevaAsociacion(idCiclo)
{
    let codigoUnidad = document.getElementById('codigoAsociacion').value;
    if(codigoUnidad != "")
    {
        fetch("ajax/ciclos/nueva_asociacion.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idCiclo:idCiclo, codigoUnidad: codigoUnidad}).toString() }).then(r => r.text()).then(res => {
            asociarUnidades(idCiclo);
        });
    }
}

// Elimina una asociación de unidad de competencia a ciclo
function borrarAsociacion(idCiclo, codigoUnidad)
{
    fetch("ajax/ciclos/borrar_asociacion.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idCiclo: idCiclo, codigoUnidad: codigoUnidad}).toString() }).then(r => r.text()).then(res => {
        asociarUnidades(idCiclo);
    });
}

// Asocia cursos a un ciclo
function asociarCursos(idCiclo)
{
    fetch("ajax/ciclos/cargar_asociaciones_cursos.php?" + new URLSearchParams({idCiclo: idCiclo}).toString()).then(r => r.json()).then(res => {
        document.getElementById("asociacionesCursos").innerHTML = res;
        (() => { const el = document.getElementById("formcurcic"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Borra una asociación de curso con ciclo
function borrarCurso(idCiclo, idCurso)
{
    fetch("ajax/ciclos/borrar_curso_ciclo.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idCiclo:idCiclo, idCurso: idCurso}).toString() }).then(r => r.text()).then(res => {
        asociarCursos(idCiclo);
    });
}

// Actualiza los datos de un curso en el ciclo
function actualizarCurso(idCiclo, idCurso)
{
    let orden = document.getElementById('orden' + idCurso).value;

    fetch("ajax/ciclos/actualizar_curso_ciclo.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idCiclo:idCiclo, idCurso: idCurso, orden: orden}).toString() }).then(r => r.text()).then(res => {
        asociarCursos(idCiclo);
    });
}

// Añade un nuevo curso al ciclo
function nuevoCurso(idCiclo)
{
    let idCurso = document.getElementById('codigoAsociacionCurso').value;
    let orden = document.getElementById('orden').value;

    fetch("ajax/ciclos/insertar_curso_ciclo.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idCiclo:idCiclo, idCurso: idCurso, orden: orden}).toString() }).then(r => r.text()).then(res => {
        asociarCursos(idCiclo);
    });
}

// Evento de envío del formulario modal para inserción/modificación
document.getElementById("formcic").addEventListener("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcic);
    fetch("ajax/ciclos/insertar_ciclo.php", { method: "POST", body: formData })
    .then(function(res) {
        limpiarFormularioCiclos();
        (() => { const el = document.getElementById("formciclo"); const modal = bootstrap.Modal.getInstance(el); if(modal) modal.hide(); })();
        cargarCiclos();
    });
});
