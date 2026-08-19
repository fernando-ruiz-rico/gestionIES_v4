// Funciones para gestión de especialidades desde la vista "especialidades.php"
// IMPORTANTE: la función "mostrarMensaje" que se usa en algunas funciones viene incorporada desde
// el fichero js/main.js

// Variable donde guardamos el departamento elegido en el desplegable superior
var selDepartamento = 0;

// Función para guardar en la variable anterior el departamento actualmente seleccionado y
// cargar las especialidades asociadas a ese departamento
function seleccionarDepartamento()
{
    selDepartamento  = $('#seleccionDepartamento').value;
    $('#idDepartamento').value = selDepartamento;
    cargarEspecialidades();
}

// Carga las especialidades del departamento actualmente seleccionado
function cargarEspecialidades()
{
    document.getElementById("listaespecialidades").load("ajax/especialidades/cargar_especialidades.php", {idDepartamento: selDepartamento});
}

// Abre el diálogo modal con los datos de la especialidad indicada por su "id"
function cargarEspecialidadModal(id)
{
    fetch('ajax/especialidades/cargar_especialidad.php?' + new URLSearchParams({idEspecialidad:id})).then(r => r.json()).then(res =>
        $('#idAntiguo').value = res.id;
        $('#idEspecialidad').value = res.id;
        $('#idDepartamento').value = res.idDepartamento;
        $('#descripcion').value = res.descripcion;
        $('#horasTutoria').value = res.horasTutoria;
        $('#horasIngles').value = res.horasIngles;
        $('#profesores').value = res.profesores;
        document.getElementById("formespecialidad").modal('show');
    });    
}

// Abre el diálogo modal para insertar una nueva especialidad
function nuevaEspecialidad()
{
    if(selDepartamento > 0)
    {
        limpiarFormularioEspecialidades();
        $('#formespecialidad').modal('show');
    } else {
        mostrarMensaje("Debes seleccionar un departamento", 0);
    }
}

// Borra la especialidad especificada
function borrarEspecialidad(id)
{    
    if (confirm("Confirmas el borrado de la especialidad '" + id + "'?"))
    {
        fetch('ajax/especialidades/borrar_especialidad.php', {method: 'POST', body: new URLSearchParams({id:id})}).then(r => r.text()).then(res =>
            if (res.trim() == 'si')
                 mostrarMensaje("Error al borrar la especialidad", 0);
           cargarEspecialidades();
        });            
    }
}

// Borra los datos del formulario modal de alta/edición de especialidad
function limpiarFormularioEspecialidades()
{
    $('#idAntiguo').value = "";
    $('#idEspecialidad').value = "";
    $('#descripcion').value = "";
    $('#horasTutoria').value = "";
    $('#horasIngles').value = "";
    $('#profesores').value = "";
}

// Evento de envío del formulario modal de especialidad
document.getElementById("formesp").on("submit", function(e)
{
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
        document.getElementById("formespecialidad").modal('hide');
        if (res.trim().startsWith('si'))
            mostrarMensaje("Error al realizar la operación solicitada: " + res.trim().substring(2), 0);
        else
            mostrarMensaje("Operación realizada correctamente", 1);
        // Actualizamos las especialidades
        cargarEspecialidades();
    });
});
