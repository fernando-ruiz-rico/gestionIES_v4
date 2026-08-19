// Funciones para la gestión de profesores

// Función que se activa cuando se elige un departamento del desplegable de la página
function seleccionarDepartamento()
{
    // Se obtiene el departamento seleccionado actualmente
    const selDepartamento = dom('#seleccionDepartamento').val();
    // Se muestran los profesores de ese departamento
    cargarProfesores(selDepartamento);
    // Se carga el modal para dar de alta o editar un profesor en ese departamento
    cargarModalProfesor(selDepartamento);
}

// Función que muestra el formulario para dar de alta un profesor, siempre que se haya elegido antes
// el departamento al que pertenece
async function nuevoProfesor()
{
    if(dom('#seleccionDepartamento').val() > 0)
    {
        limpiarFormularioProfesores();
        (() => { const el = document.getElementById("formprofesor"); const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el); modal.show(); })();
    } else {
        mostrarMensaje("Debes seleccionar un departamento", 0);
    }
}

// Función para borrar el profesor con el id indicado (previa confirmación)
function borrarProfesor (id, nombre)
{    
    if (confirm("Confirmas el borrado del profesor '" + nombre + "'?"))
    {
        fetch("ajax/profesores/borrar_profesor.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({id:id}).toString() }).then(r => r.text()).then(res => {
            if (res.trim() == 'si')
                 mostrarMensaje("Error al borrar el profesor", 0);
           cargarProfesores();
        });            
    }
}

// Borra los campos del formulario de profesores
function limpiarFormularioProfesores()
{
    document.getElementById('idPerfil').value = '';
    document.getElementById('nombrePerfil').value = '';
    document.getElementById('abreviaturaPerfil').value = '';
    document.getElementById('usuarioPerfil').value = '';
    document.getElementById('clavePerfil').value = '';
    document.getElementById('telefonoPerfil').value = '';
    document.getElementById('emailPerfil').value = '';
    document.getElementById('idEspecialidadPerfil').value = '';
    document.getElementById('observacionesPerfil').value = '';
    fetch("ajax/profesores/cargar_preferencias_profesor.php").then(r => r.text()).then(html => document.getElementById("prefhoras").innerHTML = html);
}

// Cambia el jefe del departamento indicado
function cambiarJefe(idProfesor, idDepartamento)
{
    http.post("ajax/profesores/actualizar_jefe_departamento.php", {idProfesor: idProfesor, idDepartamento: idDepartamento}, function()
    {
        cargarProfesores();
    });    
}

// Activa/Desactiva el profesor indicado
function cambiarActivo(idProfesor)
{
    http.post("ajax/profesores/actualizar_profesor_activo.php", {idProfesor: idProfesor}, function()
    {
        cargarProfesores();
    });    
}

// Evento drag & drop sobre la lista de profesores
// Son ordenables todos los elementos de "listaprofesores" que tengan class="profesor"
dom('#listaprofesores').sortable({ items: '.profesor', update: function()
    {
        var elementos = (() => { const el = this; return Array.from(el.children).map(c => c.id).join(","); })();
        // Enviamos como parámetro los "ids" de las cajas en el parámetro "orden"
        // Cada "id" se compone del prefijo "pr" seguido del id del profesor, y se colocan
        // en el orden en que han quedado.
        http.get("ajax/profesores/ordenar_profesores.php", {orden: elementos}, function()
        {
            cargarProfesores();
        });
    }
});
