// Funciones útiles para la gestión de los departamentos

// Carga en el div con id "listadepartamentos" el listado de departamentos obtenido
function cargarDepartamentos()
{
    $("#listadepartamentos").load("ajax/departamentos/cargar_departamentos.php");
}

// Carga en el formulario modal de departamentos modales/departamentos.php los datos
// del departamento con el "id" proporcionado (se reciben en formato JSON)
function cargarDepartamentoModal(id)
{
    $.get("ajax/departamentos/cargar_departamento.php", {idDepartamento:id}, function(res)
    {
        $('#idDepartamento').val(id);
        $('#nombre').val(res.nombre);
        $("#formdepartamento").modal('show');
    });    
}

// Abre el formulario modal de departamentos para dar de alta uno nuevo (borra sus campos antes)
function nuevoDepartamento()
{
    limpiarFormularioDepartamentos();
    $('#formdepartamento').modal('show');
}

// Borra el departamento con el "id" indicado, previa confirmación
// La llamada AJAX devuelve "si" si ha habido algún error en el proceso
function borrarDepartamento (id, nombre)
{
    if (confirm("Confirmas el borrado del departamento '" + nombre + "'? Sólo se podrá eliminar si no tiene profesores asociados. En caso contrario, deberás borrar estos elementos antes."))
    {
        $.post("ajax/departamentos/borrar_departamento.php", {id:id}, function(res)
        {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar el departamento. Puede que tenga profesores u otros recursos asociados que se deban borrar antes", 0);
            else
                window.location.href="departamentos.php";
        });            
    }
}

// Borra los datos del formulario modal de departamentos
function limpiarFormularioDepartamentos()
{
    $('#idDepartamento').val("");
    $('#nombre').val("");
}

// Evento de envío del formulario modal de departamentos
$("#formdep").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formdep);
    $.ajax({
        url: "ajax/departamentos/insertar_departamento.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        // Al recibir la respuesta, vaciamos formulario y recargamos la página
        // En este caso no se controlan errores porque los datos son simples
        limpiarFormularioDepartamentos();
        $("#formdepartamento").modal('hide');
        window.location.href="departamentos.php";
    });
});
