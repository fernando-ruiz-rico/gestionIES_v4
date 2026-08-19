// Funciones para gestión de especialidades desde la vista "especialidades.php"
// IMPORTANTE: la función "mostrarMensaje" que se usa en algunas funciones viene incorporada desde
// el fichero js/main.js

// Variable donde guardamos el departamento elegido en el desplegable superior
var selDepartamento = 0;

// Función para guardar en la variable anterior el departamento actualmente seleccionado y
// cargar las especialidades asociadas a ese departamento
function seleccionarDepartamento()
{
    selDepartamento  = dom('#seleccionDepartamento').val();
    dom('#idDepartamento').val(selDepartamento);
    cargarEspecialidades();
}

// Carga las especialidades del departamento actualmente seleccionado
function cargarEspecialidades()
{
    dom("#listaespecialidades").load("ajax/especialidades/cargar_especialidades.php", {idDepartamento: selDepartamento});
}

// Abre el diálogo modal con los datos de la especialidad indicada por su "id"
function cargarEspecialidadModal(id)
{
    http.get("ajax/especialidades/cargar_especialidad.php", {idEspecialidad:id}, function(res)
    {
        dom('#idAntiguo').val(res.id);
        dom('#idEspecialidad').val(res.id);
        dom('#idDepartamento').val(res.idDepartamento);
        dom('#descripcion').val(res.descripcion);
        dom('#horasTutoria').val(res.horasTutoria);
        dom('#horasIngles').val(res.horasIngles);
        dom('#profesores').val(res.profesores);
        dom("#formespecialidad").modal('show');
    });    
}

// Abre el diálogo modal para insertar una nueva especialidad
function nuevaEspecialidad()
{
    if(selDepartamento > 0)
    {
        limpiarFormularioEspecialidades();
        dom('#formespecialidad').modal('show');
    } else {
        mostrarMensaje("Debes seleccionar un departamento", 0);
    }
}

// Borra la especialidad especificada
function borrarEspecialidad(id)
{    
    if (confirm("Confirmas el borrado de la especialidad '" + id + "'?"))
    {
        http.post("ajax/especialidades/borrar_especialidad.php", {id:id}, function(res)
        {
            if (res.trim() == 'si')
                 mostrarMensaje("Error al borrar la especialidad", 0);
           cargarEspecialidades();
        });            
    }
}

// Borra los datos del formulario modal de alta/edición de especialidad
function limpiarFormularioEspecialidades()
{
    dom('#idAntiguo').val("");
    dom('#idEspecialidad').val("");
    dom('#descripcion').val("");
    dom('#horasTutoria').val("");
    dom('#horasIngles').val("");
    dom('#profesores').val("");
}

// Evento de envío del formulario modal de especialidad
dom("#formesp").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formesp);
    http.ajax({
        url: "ajax/especialidades/insertar_especialidad.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        // Recogemos respuesta y mostramos resultado en ventana modal de mensaje
        limpiarFormularioEspecialidades();
        dom("#formespecialidad").modal('hide');
        if (res.trim().startsWith('si'))
            mostrarMensaje("Error al realizar la operación solicitada: " + res.trim().substring(2), 0);
        else
            mostrarMensaje("Operación realizada correctamente", 1);
        // Actualizamos las especialidades
        cargarEspecialidades();
    });
});
