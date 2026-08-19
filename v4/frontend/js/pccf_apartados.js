// Funciones para la gestión de los apartados de los PCCF

// Carga los apartados en el "div" habilitado para ello
function cargarApartados()
{
    fetch("ajax/pccf_apartados/cargar_apartados.php").then(r => r.text()).then(html => document.getElementById("apartadospccf").innerHTML = html);
}

// Muestra los datos de un apartado en el formulario modal
function cargarApartadoModal(id)
{
    fetch("ajax/pccf_apartados/cargar_apartado.php?" + new URLSearchParams({idApartado:id}).toString()).then(r => r.json()).then(res => {
        document.getElementById('idApartado').value = id;
        document.getElementById('titulo').value = res.titulo;
        document.getElementById('tipo').value = res.tipo;
        if (res.subapartado == 1)
            document.getElementById('subapartado').checked = true;
        else
            document.getElementById('subapartado').checked = false;
        if (res.requerido == 1)
            document.getElementById('requerido').checked = true;
        else
            document.getElementById('requerido').checked = false;
        if (res.contenido_defecto == 1)
            document.getElementById('contenidoDefecto').checked = true;
        else
            document.getElementById('contenidoDefecto').checked = false;
        (() => { const el = document.getElementById("formapartadopccf"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Muestra el formulario modal para crear un nuevo apartado
function nuevoApartado()
{
    limpiarFormularioApartados();
    (() => { const el = document.getElementById("formapartadopccf"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
}

// Borra un apartado, previa confirmación
function borrarApartado (id, titulo)
{
    if (confirm("Confirmas el borrado del apartado '" + titulo + "'? Se eliminarán todos los contenidos relativos a dicho apartado."))
    {
        fetch("ajax/pccf_apartados/borrar_apartado.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({id:id}).toString() }).then(r => r.text()).then(res => {
            cargarApartados();
        });            
    }
}

// Limpia los campos del formulario modal
function limpiarFormularioApartados()
{
    document.getElementById('idApartado').value = '';
    document.getElementById('titulo').value = '';
    document.getElementById('tipo').value = '';
    document.getElementById('subapartado').removeAttr("checked");
    document.getElementById('requerido').setAttribute("checked", "checked");    
    document.getElementById('contenidoDefecto').removeAttr("checked");
}

// Evento de ordenación de los apartados
document.getElementById('apartadospccf').sortable({ items: '.apartado', update: function()
    {
        var elementos = (() => { const el = this; return Array.from(el.children).map(c => c.id).join(","); })();
        $.get("ajax/pccf_apartados/ordenar_apartados.php", {orden: elementos}, function()
        {
            cargarApartados();
        });
    }
});

// Evento de envío del formulario modal para crear/modificar apartados
document.getElementById("formapartado").addEventListener("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formapartado);
    fetch("ajax/pccf_apartados/insertar_apartado.php", { method: "POST", body: formData })
    .then(function(res) {
        limpiarFormularioApartados();
	    (() => { const el = document.getElementById("formapartadopccf"); const modal = bootstrap.Modal.getInstance(el); if(modal) modal.hide(); })();
        cargarApartados();
    });
});

cargarApartados();