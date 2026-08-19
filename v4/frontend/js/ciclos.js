// Funciones para gestión de ciclos desde la vista "ciclos.php"

// Carga el listado de ciclos en el "div" habilitado para ello
function cargarCiclos()
{
    dom("#listaciclos").load("ajax/ciclos/cargar_ciclos.php");
}

// Muestra los datos del ciclo indicado en el formulario modal, para su edición
function cargarCicloModal(id)
{
    http.get("ajax/ciclos/cargar_ciclo.php", {idCiclo:id}, function(res)
    {
        dom('#idCiclo').val(id);
        dom('#nombre').val(res.nombre);
        dom('#familia').val(res.familia);
        dom('#nivel').val(res.nivel);
        dom("#formciclo").modal('show');
    });    
}

// Muestra el formulario modal limpio para insertar un nuevo ciclo
function nuevoCiclo()
{
    limpiarFormularioCiclos();
    dom('#formciclo').modal('show');
}

// Borra el ciclo indicado, previa confirmación
// El ciclo sólo podrá borrarse si no tiene vinculaciones con otras tablas importantes
function borrarCiclo (id, nombre)
{
    if (confirm("Confirmas el borrado del ciclo '" + nombre + "'? Sólo se podrá eliminar si no tiene cursos asociados. En caso contrario, deberás borrar estos elementos antes."))
    {
        http.post("ajax/ciclos/borrar_ciclo.php", {id:id}, function(res)
        {
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
    dom('#idCiclo').val("");
    dom('#nombre').val("");
    dom('#familia').val("");
    dom('#nivel').val("");    
}

// Asocia unidades de competencia a un ciclo
function asociarUnidades(idCiclo)
{
    http.get("ajax/ciclos/cargar_asociaciones_unidades.php", {idCiclo: idCiclo}, function(res)
    {
        dom("#asociaciones").html(res);
        dom("#formunicic").modal('show');
    });    
}

// Añade una nueva asociación de unidad de competencia a un ciclo
function nuevaAsociacion(idCiclo)
{
    let codigoUnidad = dom('#codigoAsociacion').val();
    if(codigoUnidad != "")
    {
        http.post("ajax/ciclos/nueva_asociacion.php", {idCiclo:idCiclo, codigoUnidad: codigoUnidad}, function(res)
        {
            asociarUnidades(idCiclo);
        });
    }
}

// Elimina una asociación de unidad de competencia a ciclo
function borrarAsociacion(idCiclo, codigoUnidad)
{
    http.post("ajax/ciclos/borrar_asociacion.php", {idCiclo: idCiclo, codigoUnidad: codigoUnidad}, function(res)
    {
        asociarUnidades(idCiclo);
    });
}

// Asocia cursos a un ciclo
function asociarCursos(idCiclo)
{
    http.get("ajax/ciclos/cargar_asociaciones_cursos.php", {idCiclo: idCiclo}, function(res)
    {
        dom("#asociacionesCursos").html(res);
        dom("#formcurcic").modal('show');
    });    
}

// Borra una asociación de curso con ciclo
function borrarCurso(idCiclo, idCurso)
{
    http.post("ajax/ciclos/borrar_curso_ciclo.php", {idCiclo:idCiclo, idCurso: idCurso}, function(res)
    {
        asociarCursos(idCiclo);
    });
}

// Actualiza los datos de un curso en el ciclo
function actualizarCurso(idCiclo, idCurso)
{
    let orden = dom('#orden' + idCurso).val();

    http.post("ajax/ciclos/actualizar_curso_ciclo.php", {idCiclo:idCiclo, idCurso: idCurso, orden: orden}, function(res)
    {
        asociarCursos(idCiclo);
    });
}

// Añade un nuevo curso al ciclo
function nuevoCurso(idCiclo)
{
    let idCurso = dom('#codigoAsociacionCurso').val();
    let orden = dom('#orden').val();

    http.post("ajax/ciclos/insertar_curso_ciclo.php", {idCiclo:idCiclo, idCurso: idCurso, orden: orden}, function(res)
    {
        asociarCursos(idCiclo);
    });
}

// Evento de envío del formulario modal para inserción/modificación
dom("#formcic").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcic);
    http.ajax({
        url: "ajax/ciclos/insertar_ciclo.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioCiclos();
        dom("#formciclo").modal('hide');
        cargarCiclos();
    });
});
