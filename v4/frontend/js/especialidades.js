// Funciones para gestión de especialidades desde la vista "especialidades.php"
// IMPORTANTE: la función "mostrarMensaje" que se usa en algunas funciones viene incorporada desde
// el fichero js/main.js

// Variable donde guardamos el departamento elegido en el desplegable superior
var selDepartamento = 0;

// Función para guardar en la variable anterior el departamento actualmente seleccionado y
// cargar las especialidades asociadas a ese departamento
function seleccionarDepartamento()
{
    selDepartamento  = document.getElementById('seleccionDepartamento').value;
    document.getElementById('idDepartamento').value = selDepartamento;
    cargarEspecialidades();
}

// Carga las especialidades del departamento actualmente seleccionado
function cargarEspecialidades()
{
    $("#listaespecialidades").load("ajax/especialidades/cargar_especialidades.php", {idDepartamento: selDepartamento});
}

// Abre el diálogo modal con los datos de la especialidad indicada por su "id"
function cargarEspecialidadModal(id)
{
    fetch("ajax/especialidades/cargar_especialidad.php?" + new URLSearchParams(idEspecialidad:id)).then(response => response.text()).then(res => {
        document.getElementById('idAntiguo').value = res.id;
        document.getElementById('idEspecialidad').value = res.id;
        document.getElementById('idDepartamento').value = res.idDepartamento;
        document.getElementById('descripcion').value = res.descripcion;
        document.getElementById('horasTutoria').value = res.horasTutoria;
        document.getElementById('horasIngles').value = res.horasIngles;
        document.getElementById('profesores').value = res.profesores;
        $("#formespecialidad").show();
    });    
}

// Abre el diálogo modal para insertar una nueva especialidad
function nuevaEspecialidad()
{
    if(selDepartamento > 0)
    {
        limpiarFormularioEspecialidades();
        document.getElementById('formespecialidad').show();
    } else {
        mostrarMensaje("Debes seleccionar un departamento", 0);
    }
}

// Borra la especialidad especificada
function borrarEspecialidad(id)
{    
    if (confirm("Confirmas el borrado de la especialidad '" + id + "'?"))
    {
        $.post("ajax/especialidades/borrar_especialidad.php", {id:id}, function(res)
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
    document.getElementById('idAntiguo').value = "";
    document.getElementById('idEspecialidad').value = "";
    document.getElementById('descripcion').value = "";
    document.getElementById('horasTutoria').value = "";
    document.getElementById('horasIngles').value = "";
    document.getElementById('profesores').value = "";
}

// Evento de envío del formulario modal de especialidad
$("#formesp").addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(document.forms.formesp);
    $.ajax({
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
        $("#formespecialidad").hide();
        if (res.trim().startsWith('si'))
            mostrarMensaje("Error al realizar la operación solicitada: " + res.trim().substring(2), 0);
        else
            mostrarMensaje("Operación realizada correctamente", 1);
        // Actualizamos las especialidades
        cargarEspecialidades();
    });
});
