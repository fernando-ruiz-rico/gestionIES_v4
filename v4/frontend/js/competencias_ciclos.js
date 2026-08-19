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
    document.getElementById("listacompetencias").load("ajax/competencias_ciclos/cargar_competencias.php", {idCiclo: selCiclo});
}

// Carga en el formulario modal los datos de la competencia indicada
function cargarCompetenciaModal(id)
{
    fetch("ajax/competencias_ciclos/cargar_competencia.php?" + new URLSearchParams({idCompetencia:id}).toString()).then(r => r.json()).then(res => {
        document.getElementById('idCompetencia').value = id;
        document.getElementById('idCiclo').value = res.idCiclo;
        document.getElementById('codigo').value = res.codigo;
        document.getElementById('texto').value = res.texto;
        document.getElementById('tipo').value = res.tipo;
        (() => { const el = document.getElementById("formcompetencia"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
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
        (() => { const el = document.getElementById("formcompetencia"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    }
}

// Elimina la competencia indicada, previa confirmación
function borrarCompetencia (id, codigo)
{
    if (confirm("Confirmas el borrado de la competencia '" + codigo + "'?"))
    {
        fetch("ajax/competencias_ciclos/borrar_competencia.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({id:id}).toString() }).then(r => r.text()).then(res => {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar la competencia", 0);
            cargarCompetencias();
        });            
    }
}

// Borra los campos del formulario de competencias
function limpiarFormularioCompetencias()
{
    document.getElementById('idCompetencia').value = '';
    document.getElementById('codigo').value = '';
    document.getElementById('texto').value = '';
    document.getElementById('tipo').value = '';
}

// Evento para auto-ordenar las competencias
dom('#listacompetencias').sortable({ items: '.competencia', update: function()
    {
        // Se envían los datos en un string. Cada competencia con el prefijo "cm" y su id, separados por comas
        // En el servidor se procesa esa cadena, se parte y se le asigna un número de orden a cada competencia
        var elementos = (() => { const el = this; return Array.from(el.children).map(c => c.id).join(","); })();
        $.get("ajax/competencias_ciclos/ordenar_competencias.php", {orden: elementos}, function()
        {
            cargarCompetencias();
        });
    }
});

// Evento de envío del formulario modal para insertar/modificar competencias
document.getElementById("formcomp").addEventListener("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcomp);
    fetch("ajax/competencias_ciclos/insertar_competencia.php", { method: "POST", body: formData })
    .then(function(res) {
        limpiarFormularioCompetencias();
        (() => { const el = document.getElementById("formcompetencia"); const modal = bootstrap.Modal.getInstance(el); if(modal) modal.hide(); })();
        cargarCompetencias();
    });
});
