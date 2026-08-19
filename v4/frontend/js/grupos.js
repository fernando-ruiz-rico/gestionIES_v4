// Funciones para gestión de grupos desde la vista "grupos.php"

// Variable donde almacenamos el curso actualmente seleccionado
var selCurso = 0;

// Se activa al cambiar el curso seleccionado
function seleccionarCursoGrupo()
{
    selCurso  = document.getElementById('cursosgrupos').value;
    document.getElementById('idCurso').value = selCurso;
    cargarGrupos();
}

// Carga los grupos del curso seleccionado actualmente
function cargarGrupos()
{
    $("#listagrupos").load("ajax/grupos/cargar_grupos.php", {idCurso: selCurso});
}

// Carga en el formulario modal los datos del grupo indicado
function cargarGrupoModal(id)
{
    fetch("ajax/grupos/cargar_grupo.php?" + new URLSearchParams(idGrupo:id)).then(response => response.text()).then(res => {
        document.getElementById('idGrupo').value = id;
        document.getElementById('idCurso').value = res.idCurso;
        document.getElementById('nombre').value = res.nombre;
        document.getElementById('abreviatura').value = res.abreviatura;
        if (res.mostrar == 1)
            document.getElementById('mostrar').prop('checked', true);
        else
            document.getElementById('mostrar').prop('checked', false);
        document.getElementById('horasComplementariasDual').value = res.horas_complementarias_dual;

        $("#formgrupo").show();
    });    
}

// Limpia el formulario modal para crear un nuevo grupo
function nuevoGrupo()
{
    if (selCurso <= 0)
    {
        mostrarMensaje("Debes seleccionar un curso primero", 0);
    } else {
        limpiarFormularioGrupos();
        document.getElementById('formgrupo').show();
    }
}

// Elimina el grupo indicado, previa confirmación
function borrarGrupo (id, nombre)
{
    if (confirm("Confirmas el borrado del grupo '" + nombre + "'?"))
    {
        $.post("ajax/grupos/borrar_grupo.php", {id:id}, function(res)
        {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar el grupo", 0);
            cargarGrupos();
        });            
    }
}

// Borra los campos del formulario de grupos
function limpiarFormularioGrupos()
{
    document.getElementById('idGrupo').value = "";
    document.getElementById('nombre').value = "";
    document.getElementById('abreviatura').value = "";
    document.getElementById('horasComplementariasDual').value = "";
    document.getElementById('mostrar').removeAttr("checked");    
}

// Evento para auto-ordenar los grupos
document.getElementById('listagrupos').sortable({ items: '.grupo', update: function()
    {
        // Se envían los datos en un string. Cada grupo con el prefijo "gr" y su código, separados por comas
        // En el servidor se procesa esa cadena, se parte y se le asigna un número de orden a cada grupo
        var elementos = $(this).sortable("toArray").toString();
        $.get("ajax/grupos/ordenar_grupos.php", {orden: elementos}, function()
        {
            cargarGrupos();
        });
    }
});

// Evento de envío del formulario modal para insertar/modificar grupos
$("#formgrup").addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(document.forms.formgrup);
    $.ajax({
        url: "ajax/grupos/insertar_grupo.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioGrupos();
        $("#formgrupo").hide();
        cargarGrupos();
    });
});
