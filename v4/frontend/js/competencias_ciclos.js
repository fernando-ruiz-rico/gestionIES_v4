// Funciones para gestión de competencias de ciclos formativos desde la vista "competencias_ciclos.php"

// Variable donde almacenamos el ciclo actualmente seleccionado
var selCiclo = 0;

// Se activa al cambiar el curso seleccionado
function seleccionarCiclo()
{
    selCiclo  = $('#ciclos').value;
    $('#idCiclo').value = selCiclo;
    cargarCompetencias();
}

// Carga las competencias del ciclo seleccionado actualmente
function cargarCompetencias()
{
    document.getElementById("listacompetencias").load("ajax/competencias_ciclos/cargar_competencias.php", {idCiclo: selCiclo});
}

// Carga en el formulario modal los datos de la competencia indicada
function cargarCompetenciaModal(id)
{
    fetch('ajax/competencias_ciclos/cargar_competencia.php?' + new URLSearchParams({idCompetencia:id})).then(r => r.json()).then(res =>
        $('#idCompetencia').value = id;
        $('#idCiclo').value = res.idCiclo;
        $('#codigo').value = res.codigo;
        $('#texto').value = res.texto;
        $('#tipo').value = res.tipo;
        document.getElementById("formcompetencia").modal('show');
    });    
}

// Limpia el formulario modal para crear una nueva competencia
function nuevaCompetencia()
{
    if (selCiclo <= 0)
    {
        mostrarMensaje("Debes seleccionar un ciclo primero", 0);
    } else {
        limpiarFormularioCompetencias();
        $('#formcompetencia').modal('show');
    }
}

// Elimina la competencia indicada, previa confirmación
function borrarCompetencia (id, codigo)
{
    if (confirm("Confirmas el borrado de la competencia '" + codigo + "'?"))
    {
        fetch('ajax/competencias_ciclos/borrar_competencia.php', {method: 'POST', body: new URLSearchParams({id:id})}).then(r => r.text()).then(res =>
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar la competencia", 0);
            cargarCompetencias();
        });            
    }
}

// Borra los campos del formulario de competencias
function limpiarFormularioCompetencias()
{
    $('#idCompetencia').value = "";
    $('#codigo').value = "";
    $('#texto').value = "";
    $('#tipo').value = "";
}

// Evento para auto-ordenar las competencias
$('#listacompetencias').sortable({ items: '.competencia', update: function()
    {
        // Se envían los datos en un string. Cada competencia con el prefijo "cm" y su id, separados por comas
        // En el servidor se procesa esa cadena, se parte y se le asigna un número de orden a cada competencia
        var elementos = $(this).sortable("toArray").toString();
        $.get("ajax/competencias_ciclos/ordenar_competencias.php", {orden: elementos}, function()
        {
            cargarCompetencias();
        });
    }
});

// Evento de envío del formulario modal para insertar/modificar competencias
document.getElementById("formcomp").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcomp);
    $.ajax({
        url: "ajax/competencias_ciclos/insertar_competencia.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioCompetencias();
        document.getElementById("formcompetencia").modal('hide');
        cargarCompetencias();
    });
});
