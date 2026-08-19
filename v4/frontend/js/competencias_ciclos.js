// Funciones para gestión de competencias de ciclos formativos desde la vista "competencias_ciclos.php"

// Variable donde almacenamos el ciclo actualmente seleccionado
var selCiclo = 0;

// Se activa al cambiar el curso seleccionado
function seleccionarCiclo()
{
    selCiclo  = dom('#ciclos').val();
    dom('#idCiclo').val(selCiclo);
    cargarCompetencias();
}

// Carga las competencias del ciclo seleccionado actualmente
function cargarCompetencias()
{
    dom("#listacompetencias").load("ajax/competencias_ciclos/cargar_competencias.php", {idCiclo: selCiclo});
}

// Carga en el formulario modal los datos de la competencia indicada
function cargarCompetenciaModal(id)
{
    http.get("ajax/competencias_ciclos/cargar_competencia.php", {idCompetencia:id}, function(res)
    {
        dom('#idCompetencia').val(id);
        dom('#idCiclo').val(res.idCiclo);
        dom('#codigo').val(res.codigo);
        dom('#texto').val(res.texto);
        dom('#tipo').val(res.tipo);
        dom("#formcompetencia").modal('show');
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
        dom('#formcompetencia').modal('show');
    }
}

// Elimina la competencia indicada, previa confirmación
function borrarCompetencia (id, codigo)
{
    if (confirm("Confirmas el borrado de la competencia '" + codigo + "'?"))
    {
        http.post("ajax/competencias_ciclos/borrar_competencia.php", {id:id}, function(res)
        {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar la competencia", 0);
            cargarCompetencias();
        });            
    }
}

// Borra los campos del formulario de competencias
function limpiarFormularioCompetencias()
{
    dom('#idCompetencia').val("");
    dom('#codigo').val("");
    dom('#texto').val("");
    dom('#tipo').val("");
}

// Evento para auto-ordenar las competencias
dom('#listacompetencias').sortable({ items: '.competencia', update: function()
    {
        // Se envían los datos en un string. Cada competencia con el prefijo "cm" y su id, separados por comas
        // En el servidor se procesa esa cadena, se parte y se le asigna un número de orden a cada competencia
        var elementos = dom(this).sortable("toArray").toString();
        http.get("ajax/competencias_ciclos/ordenar_competencias.php", {orden: elementos}, function()
        {
            cargarCompetencias();
        });
    }
});

// Evento de envío del formulario modal para insertar/modificar competencias
dom("#formcomp").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcomp);
    http.ajax({
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
        dom("#formcompetencia").modal('hide');
        cargarCompetencias();
    });
});
