// Funciones para gestión de cursos desde la vista "cursos.php"

// Carga el listado de cursos en el "div" habilitado para ello
function cargarCursos()
{
    document.getElementById("listacursos").load("ajax/cursos/cargar_cursos.php");
}

// Muestra los datos del curso indicado en el formulario modal, para su edición
function cargarCursoModal(id)
{
    fetch('ajax/cursos/cargar_curso.php?' + new URLSearchParams({idCurso:id})).then(r => r.json()).then(res =>
        $('#idCurso').value = id;
        $('#nombre').value = res.nombre;
        $('#abreviatura').value = res.abreviatura;
        $('#horasSemana').value = res.horas_semana;
        $('#categoria').value = res.categoria;
        document.getElementById("formcurso").modal('show');
    });    
}

// Muestra el formulario modal limpio para insertar un nuevo curso
function nuevoCurso()
{
    limpiarFormularioCursos();
    $('#formcurso').modal('show');
}

// Borra el curso indicado, previa confirmación
// El curso sólo podrá borrarse si no tiene vinculaciones con otras tablas importantes
function borrarCurso (id, nombre)
{
    if (confirm("Confirmas el borrado del curso '" + nombre + "'? Sólo se podrá eliminar si no tiene grupos ni materias asociadas. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch('ajax/cursos/borrar_curso.php', {method: 'POST', body: new URLSearchParams({id:id})}).then(r => r.text()).then(res =>
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar el curso. Asegúrate de que no tenga grupos o materias asociados", 0);
            else
                cargarCursos();
        });            
    }
}

// Borra el contenido de los campos del formulario modal de alta/edición de cursos
function limpiarFormularioCursos()
{
    $('#idCurso').value = "";
    $('#nombre').value = "";
    $('#abreviatura').value = "";
    $('#horasSemana').value = "";    
    $('#categoria').value = "";    
}

// Evento de auto-ordenación sobre los items de la lista de cursos
$('#listacursos').sortable({ items: '.curso', update: function()
    {
        // Recoge los elementos contenidos en el "div"
        var elementos = $(this).sortable("toArray").toString();
        // Invoca por AJAX al código PHP que ordena los cursos, pasándole los elementos a ordenar
        // Cada elemento se compone del prefijo "cu" seguido del código del curso, y los elementos
        // se envían separados por comas. La página PHP los recibe, trocea y procesa
        $.get("ajax/cursos/ordenar_cursos.php", {orden: elementos}, function()
        {
            cargarCursos();
        });
    }
});

// Evento de envío del formulario modal para inserción/modificación
document.getElementById("formcur").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcur);
    $.ajax({
        url: "ajax/cursos/insertar_curso.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioCursos();
        document.getElementById("formcurso").modal('hide');
        cargarCursos();
    });
});
