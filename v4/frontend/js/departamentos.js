// Funciones útiles para la gestión de los departamentos

// Carga en el div con id "listadepartamentos" el listado de departamentos obtenido
function cargarDepartamentos()
{
    fetch("ajax/departamentos/cargar_departamentos.php").then(r => r.text()).then(html => document.getElementById("listadepartamentos").innerHTML = html);
}

// Carga en el formulario modal de departamentos modales/departamentos.php los datos
// del departamento con el "id" proporcionado (se reciben en formato JSON)
function cargarDepartamentoModal(id)
{
    fetch("ajax/departamentos/cargar_departamento.php?" + new URLSearchParams({idDepartamento:id}).toString()).then(r => r.json()).then(res => {
        document.getElementById('idDepartamento').value = id;
        document.getElementById('nombre').value = res.nombre;
        (() => { const el = document.getElementById("formdepartamento"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    });    
}

// Abre el formulario modal de departamentos para dar de alta uno nuevo (borra sus campos antes)
function nuevoDepartamento()
{
    limpiarFormularioDepartamentos();
    (() => { const el = document.getElementById("formdepartamento"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
}

// Borra el departamento con el "id" indicado, previa confirmación
// La llamada AJAX devuelve "si" si ha habido algún error en el proceso
function borrarDepartamento (id, nombre)
{
    if (confirm("Confirmas el borrado del departamento '" + nombre + "'? Sólo se podrá eliminar si no tiene profesores asociados. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch("ajax/departamentos/borrar_departamento.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({id:id}).toString() }).then(r => r.text()).then(res => {
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar el departamento. Puede que tenga profesores u otros recursos asociados que se deban borrar antes", 0);
            else
                window.location.href="departamentos.php";
        });            
    }
}

// Borra los datos del formulario modal de departamentos
function limpiarFormularioDepartamentos()
{
    document.getElementById('idDepartamento').value = '';
    document.getElementById('nombre').value = '';
}

// Evento de envío del formulario modal de departamentos
document.getElementById("formdep").addEventListener("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formdep);
    fetch("ajax/departamentos/insertar_departamento.php", { method: "POST", body: formData })
    .then(function(res) {
        // Al recibir la respuesta, vaciamos formulario y recargamos la página
        // En este caso no se controlan errores porque los datos son simples
        limpiarFormularioDepartamentos();
        (() => { const el = document.getElementById("formdepartamento"); const modal = bootstrap.Modal.getInstance(el); if(modal) modal.hide(); })();
        window.location.href="departamentos.php";
    });
});
