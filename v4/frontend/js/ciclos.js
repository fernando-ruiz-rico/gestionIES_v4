// Funciones para gestión de ciclos desde la vista "ciclos.php"

// Carga el listado de ciclos en el "div" habilitado para ello
function cargarCiclos()
{
    $("#listaciclos").load("ajax/ciclos/cargar_ciclos.php");
}

// Muestra los datos del ciclo indicado en el formulario modal, para su edición
function cargarCicloModal(id)
{
    fetch("ajax/ciclos/cargar_ciclo.php?" + new URLSearchParams(idCiclo:id)).then(response => response.text()).then(res => {
        document.getElementById('idCiclo').value = id;
        document.getElementById('nombre').value = res.nombre;
        document.getElementById('familia').value = res.familia;
        document.getElementById('nivel').value = res.nivel;
        $("#formciclo").show();
    });    
}

// Muestra el formulario modal limpio para insertar un nuevo ciclo
function nuevoCiclo()
{
    limpiarFormularioCiclos();
    document.getElementById('formciclo').show();
}

// Borra el ciclo indicado, previa confirmación
// El ciclo sólo podrá borrarse si no tiene vinculaciones con otras tablas importantes
function borrarCiclo (id, nombre)
{
    if (confirm("Confirmas el borrado del ciclo '" + nombre + "'? Sólo se podrá eliminar si no tiene cursos asociados. En caso contrario, deberás borrar estos elementos antes."))
    {
        $.post("ajax/ciclos/borrar_ciclo.php", {id:id}, function(res)
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
    document.getElementById('idCiclo').value = "";
    document.getElementById('nombre').value = "";
    document.getElementById('familia').value = "";
    document.getElementById('nivel').value = "";    
}

// Asocia unidades de competencia a un ciclo
function asociarUnidades(idCiclo)
{
    fetch("ajax/ciclos/cargar_asociaciones_unidades.php?" + new URLSearchParams(idCiclo: idCiclo)).then(response => response.text()).then(res => {
        $("#asociaciones").innerHTML = res;
        $("#formunicic").show();
    });    
}

// Añade una nueva asociación de unidad de competencia a un ciclo
function nuevaAsociacion(idCiclo)
{
    let codigoUnidad = document.getElementById('codigoAsociacion').value;
    if(codigoUnidad != "")
    {
        $.post("ajax/ciclos/nueva_asociacion.php", {idCiclo:idCiclo, codigoUnidad: codigoUnidad}, function(res)
        {
            asociarUnidades(idCiclo);
        });
    }
}

// Elimina una asociación de unidad de competencia a ciclo
function borrarAsociacion(idCiclo, codigoUnidad)
{
    $.post("ajax/ciclos/borrar_asociacion.php", {idCiclo: idCiclo, codigoUnidad: codigoUnidad}, function(res)
    {
        asociarUnidades(idCiclo);
    });
}

// Asocia cursos a un ciclo
function asociarCursos(idCiclo)
{
    fetch("ajax/ciclos/cargar_asociaciones_cursos.php?" + new URLSearchParams(idCiclo: idCiclo)).then(response => response.text()).then(res => {
        $("#asociacionesCursos").innerHTML = res;
        $("#formcurcic").show();
    });    
}

// Borra una asociación de curso con ciclo
function borrarCurso(idCiclo, idCurso)
{
    $.post("ajax/ciclos/borrar_curso_ciclo.php", {idCiclo:idCiclo, idCurso: idCurso}, function(res)
    {
        asociarCursos(idCiclo);
    });
}

// Actualiza los datos de un curso en el ciclo
function actualizarCurso(idCiclo, idCurso)
{
    let orden = $('#orden' + idCurso).value;

    $.post("ajax/ciclos/actualizar_curso_ciclo.php", {idCiclo:idCiclo, idCurso: idCurso, orden: orden}, function(res)
    {
        asociarCursos(idCiclo);
    });
}

// Añade un nuevo curso al ciclo
function nuevoCurso(idCiclo)
{
    let idCurso = document.getElementById('codigoAsociacionCurso').value;
    let orden = document.getElementById('orden').value;

    $.post("ajax/ciclos/insertar_curso_ciclo.php", {idCiclo:idCiclo, idCurso: idCurso, orden: orden}, function(res)
    {
        asociarCursos(idCiclo);
    });
}

// Evento de envío del formulario modal para inserción/modificación
$("#formcic").addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(document.forms.formcic);
    $.ajax({
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
        $("#formciclo").hide();
        cargarCiclos();
    });
});
