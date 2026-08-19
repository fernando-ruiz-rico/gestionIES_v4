// Funciones de uso general en varios ficheros JavaScript

// Función para mostrar el modal modales/mensaje.php con un mensaje de acierto o error
function mostrarMensaje(mensaje, tipo)
{
    // Categorías de mensaje (parámetro "tipo"):
    // 0 = mensaje de error (color rojo)
    // 1 = mensaje de OK (color verde)
    // 2 = mensaje de warning (color amarillo)
    // Otro valor = mensaje convencional (color claro)

    if (tipo == 0)
    { 
        // ERROR
        $('#textoMensajeModal').attr('class', 'alert alert-danger');
    } else if (tipo == 1) {
        // OK
        $('#textoMensajeModal').attr('class', 'alert alert-success');
    } else if (tipo == 2) {
        // WARNING
        $('#textoMensajeModal').attr('class', 'alert alert-warning');        
    } else {
        // OTROS
        $('#textoMensajeModal').attr('class', 'alert alert-light');       
    } 
    $('#textoMensajeModal').html(mensaje);
    $('#mensajemodal').modal('show');
}

// Función para confirmar una acción con un mensaje personalizado
async function confirmar(mensaje, titulo = '¿Estás seguro?') 
{
    return Swal.fire({
        title: titulo,
        text: mensaje,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: "btn btn-outline-danger me-2",
            cancelButton: "btn btn-outline-secondary"
        },
        buttonsStyling: false
    }).then(result => result.isConfirmed);
}

// Función que muestra en el div "listaprofesores" los profesores del departamento seleccionado
function cargarProfesores(selDepartamento)
{
    $("#listaprofesores").load("ajax/profesores/cargar_profesores.php", {idDepartamento: selDepartamento});
}

// Función para cargar en el DOM el modal del formulario para crear un profesor o editar su perfil
// Recibe como parámetro el id del departamento para cargar las posibles especialidades
async function cargarModalProfesor(idDepartamento) 
{
    // Si el modal para mostrar el perfil del profesor no existe en el DOM, lo cargamos
    if ($('#formprofesor').length === 0) {
        const modal = await $.get('modales/profesores.php');
        $('body').append(modal); // Insertamos el modal al final del body
        
        // Evento de envío del formulario de datos de profesor
        // Hay que tener en cuenta que este formulario se puede cargar y enviar tanto desde la vista "profesores.php" como desde el menú "Perfil"
        $("#formprof").on("submit", function(e)
        {
            e.preventDefault();
            var formData = new FormData(document.forms.formprof);
            $.ajax({
                url: "ajax/profesores/insertar_profesor.php",
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
                processData: false
            })
            .done(function(res){
                // Mostramos resultado y actualizamos profesores (sólo si estamos en la vista de profesores y existe la función "cargarProfesores")
                $("#formprofesor").modal('hide');
                if (res.trim().startsWith('si'))
                    mostrarMensaje("Error al realizar la operación solicitada: " + res.trim().substring(2), 0);
                else
                    mostrarMensaje("Operación realizada correctamente", 1);
                if (cargarProfesores !== undefined)
                    cargarProfesores(idDepartamento);
            });
        });
    }

    if ($('#idDepartamentoPerfil').val() != idDepartamento) {
        // Cargamos las especialidades del departamento para poder rellenar el select del formulario
        const resEsp = await $.get("ajax/especialidades/cargar_especialidades_json.php", {idDepartamento:idDepartamento});
        const resultado = JSON.parse(resEsp);
        // Accedemos al "select" de especialidad del formulario y rellenamos las opciones
        $('#idEspecialidadPerfil').empty();
        for(var i = 0; i < resultado.length; i++) {
            var $option = $('<option></option>')
                .attr('value', resultado[i].id)
                .text(resultado[i].descripcion);
            $('#idEspecialidadPerfil').append($option);
        }

        $('#idDepartamentoPerfil').val(idDepartamento);
    }
}

// Carga la ventana modal "modales/profesor.php" para editar los datos de un profesor
// Recibe como parámetro el id del profesor, el de su departamento y un booleano que 
// indica si se puede editar la abreviatura del profesor o no (dependiendo de desde dónde
// se abra el formulario)
async function cargarPerfil(idProf, idDep, editarAbreviatura = true)
{
    // Si el modal para mostrar el perfil del profesor no existe en el DOM, lo cargamos
    await cargarModalProfesor(idDep);

    // Ahora cargamos los datos del profesor y los ponemos en el formulario
    $.get("ajax/profesores/cargar_profesor.php", {idProfesor:idProf}, function(res)
    {
        $('#idPerfil').val(idProf);
        $('#nombrePerfil').val(res.nombre);
        $('#abreviaturaPerfil').val(res.abreviatura);
        $('#usuarioPerfil').val(res.usuario);
        $('#clavePerfil').val("");
        $('#telefonoPerfil').val(res.telefono);
        $('#emailPerfil').val(res.email);
        $('#idEspecialidadPerfil').val(res.idEspecialidad);
        $('#observacionesPerfil').val(res.observaciones_horario);
        $('#prefhoras').load('ajax/profesores/cargar_preferencias_profesor.php', {idProfesor:idProf});
        // Marcamos como solo lectura la abreviatura si no se puede editar
        if(editarAbreviatura)
            $('#abreviaturaPerfil').prop('readonly', false);
        else
            $('#abreviaturaPerfil').prop('readonly', true);

        $("#formprofesor").modal('show');
    });
}

// Esta función se activa cada vez que el usuario elige una preferencia de horario (casilla)
// en la tabla de horario del formulario. Recibe como parámetros:
// - El código o "id" de la hora, formado por el día y hora concatenados con un formato específico
//   Ejemplo: marcar el lunes a las 07:55 se guarda como L07_55
// - El tipo de preferencia (1 para roja, 2 para amarilla)
// Esta función se invoca desde el código generado en ajax/profesores/cargar_preferencias_profesor.php
function preferencia(id, tipo)
{
    // Casillas rojas y amarillas actualmente seleccionadas
    var rojas = $('#prefRojas').val();
    var amarillas = $('#prefAmarillas').val();
    
    // Quitamos de las casillas rojas o amarillas la casilla involucrada.
    // Esto es así porque pueden pasar varias cosas:
    // - Que la casilla fuera roja y ahora sea amarilla (con lo que hay que quitarla de las rojas para luego añadirla a las amarillas)
    // - Que la casilla fuera amarilla y ahora ya no esté seleccionada (con lo que hay que quitarla de las amarillas)
    // Para aunar todos los casos posibles, eliminamos la casilla de las preferencias y, según su nuevo color, la colocamos en la lista adecuada
    rojas = rojas.replace(id, "");
    amarillas = amarillas.replace(id, "");

    // Aquí es donde colocamos la casilla elegida en la lista adecuada
    // Si la casilla seleccionada es roja, la añadimos a la lista de rojas
    if (tipo == 1)
        rojas = rojas + id;
    // Si es amarilla, a la lista de amarillas
    if (tipo == 2)
        amarillas = amarillas + id;
    
    // Actualizamos los campos "hidden" con los nuevos valores de rojas y amarillas
    $('#prefRojas').val(rojas);
    $('#prefAmarillas').val(amarillas);    
}

// Función para recargar la página actual con el departamento seleccionado en el desplegable (si lo hay)
// Se apoya en el "select" incluido desde includes/seleccion_departamento.php
function seleccionarDepartamento()
{
    const selDepartamento = $('#seleccionDepartamento').val();
    if (selDepartamento != "")
    {
        const pagina = window.location.pathname;
        window.location.href = pagina + "?idDepartamento=" + selDepartamento;
    }
}

// Función para inicializar TinyMCE en un textarea determinado con un alto específico
function initTinyMCE(selector, height = 300) {
  return tinymce.init({
    selector: "textarea." + selector,  // CSS selector de los <textarea>
    height: height,
    resize: true,

    plugins: 'autolink lists advlist code fullscreen wordcount',
    toolbar: 'undo redo | styles | bold italic underline removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | code fullscreen',
    statusbar: true,
    menubar: false,
    branding: false,

    content_css: 'css/estilos_tiny.css'
  });
}