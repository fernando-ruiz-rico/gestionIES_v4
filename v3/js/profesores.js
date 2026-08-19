// Funciones para la gestión de profesores

// Función que se activa cuando se elige un departamento del desplegable de la página
function seleccionarDepartamento()
{
    // Se obtiene el departamento seleccionado actualmente
    const selDepartamento = $('#seleccionDepartamento').val();
    // Se muestran los profesores de ese departamento
    cargarProfesores(selDepartamento);
    // Se carga el modal para dar de alta o editar un profesor en ese departamento
    cargarModalProfesor(selDepartamento);
}

// Función que muestra el formulario para dar de alta un profesor, siempre que se haya elegido antes
// el departamento al que pertenece
async function nuevoProfesor()
{
    if($('#seleccionDepartamento').val() > 0)
    {
        limpiarFormularioProfesores();
        $('#formprofesor').modal('show');
    } else {
        mostrarMensaje("Debes seleccionar un departamento", 0);
    }
}

// Función para borrar el profesor con el id indicado (previa confirmación)
function borrarProfesor (id, nombre)
{    
    if (confirm("Confirmas el borrado del profesor '" + nombre + "'?"))
    {
        $.post("ajax/profesores/borrar_profesor.php", {id:id}, function(res)
        {
            if (res.trim() == 'si')
                 mostrarMensaje("Error al borrar el profesor", 0);
           cargarProfesores();
        });            
    }
}

// Borra los campos del formulario de profesores
function limpiarFormularioProfesores()
{
    $('#idPerfil').val("");
    $('#nombrePerfil').val("");
    $('#abreviaturaPerfil').val("");
    $('#usuarioPerfil').val("");
    $('#clavePerfil').val("");
    $('#telefonoPerfil').val("");
    $('#emailPerfil').val("");
    $('#idEspecialidadPerfil').val("");
    $('#observacionesPerfil').val("");
    $('#prefhoras').load('ajax/profesores/cargar_preferencias_profesor.php');
}

// Cambia el jefe del departamento indicado
function cambiarJefe(idProfesor, idDepartamento)
{
    $.post("ajax/profesores/actualizar_jefe_departamento.php", {idProfesor: idProfesor, idDepartamento: idDepartamento}, function()
    {
        cargarProfesores();
    });    
}

// Activa/Desactiva el profesor indicado
function cambiarActivo(idProfesor)
{
    $.post("ajax/profesores/actualizar_profesor_activo.php", {idProfesor: idProfesor}, function()
    {
        cargarProfesores();
    });    
}

// Evento drag & drop sobre la lista de profesores
// Son ordenables todos los elementos de "listaprofesores" que tengan class="profesor"
$('#listaprofesores').sortable({ items: '.profesor', update: function()
    {
        var elementos = $(this).sortable("toArray").toString();
        // Enviamos como parámetro los "ids" de las cajas en el parámetro "orden"
        // Cada "id" se compone del prefijo "pr" seguido del id del profesor, y se colocan
        // en el orden en que han quedado.
        $.get("ajax/profesores/ordenar_profesores.php", {orden: elementos}, function()
        {
            cargarProfesores();
        });
    }
});
