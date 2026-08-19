// Funciones para gestión de ciclos desde la vista "ciclos.php"

// Carga el listado de ciclos en el "div" habilitado para ello
function cargarCiclos()
{
    document.getElementById("listaciclos").load("ajax/ciclos/cargar_ciclos.php");
}

// Muestra los datos del ciclo indicado en el formulario modal, para su edición
function cargarCicloModal(id)
{
    fetch('ajax/ciclos/cargar_ciclo.php?' + new URLSearchParams({idCiclo:id})).then(r => r.json()).then(res =>
        $('#idCiclo').value = id;
        $('#nombre').value = res.nombre;
        $('#familia').value = res.familia;
        $('#nivel').value = res.nivel;
        document.getElementById("formciclo").modal('show');
    });    
}

// Muestra el formulario modal limpio para insertar un nuevo ciclo
function nuevoCiclo()
{
    limpiarFormularioCiclos();
    $('#formciclo').modal('show');
}

// Borra el ciclo indicado, previa confirmación
// El ciclo sólo podrá borrarse si no tiene vinculaciones con otras tablas importantes
function borrarCiclo (id, nombre)
{
    if (confirm("Confirmas el borrado del ciclo '" + nombre + "'? Sólo se podrá eliminar si no tiene cursos asociados. En caso contrario, deberás borrar estos elementos antes."))
    {
        fetch('ajax/ciclos/borrar_ciclo.php', {method: 'POST', body: new URLSearchParams({id:id})}).then(r => r.text()).then(res =>
            if (res.trim() == 'si')
                mostrarMensaje("Error al borrar el ciclo. Asegúrate de que no tenga cursos asociados", 0);
            else
                cargarCiclos();
        });            
    }
}

// Borra el contenido de los campos del formulario modal de alta/edición de ciclos
function limpiarFormularioCiclos()
{
    $('#idCiclo').value = "";
    $('#nombre').value = "";
    $('#familia').value = "";
    $('#nivel').value = "";    
}

// Asocia unidades de competencia a un ciclo
function asociarUnidades(idCiclo)
{
    fetch('ajax/ciclos/cargar_asociaciones_unidades.php?' + new URLSearchParams({idCiclo: idCiclo})).then(r => r.json()).then(res =>
        document.getElementById("asociaciones").innerHTML = res;
        document.getElementById("formunicic").modal('show');
    });    
}

// Añade una nueva asociación de unidad de competencia a un ciclo
function nuevaAsociacion(idCiclo)
{
    let codigoUnidad = $('#codigoAsociacion').value;
    if(codigoUnidad != "")
    {
        fetch('ajax/ciclos/nueva_asociacion.php', {method: 'POST', body: new URLSearchParams({idCiclo:idCiclo, codigoUnidad: codigoUnidad})}).then(r => r.text()).then(res =>
            asociarUnidades(idCiclo);
        });
    }
}

// Elimina una asociación de unidad de competencia a ciclo
function borrarAsociacion(idCiclo, codigoUnidad)
{
    fetch('ajax/ciclos/borrar_asociacion.php', {method: 'POST', body: new URLSearchParams({idCiclo: idCiclo, codigoUnidad: codigoUnidad})}).then(r => r.text()).then(res =>
        asociarUnidades(idCiclo);
    });
}

// Asocia cursos a un ciclo
function asociarCursos(idCiclo)
{
    fetch('ajax/ciclos/cargar_asociaciones_cursos.php?' + new URLSearchParams({idCiclo: idCiclo})).then(r => r.json()).then(res =>
        document.getElementById("asociacionesCursos").innerHTML = res;
        document.getElementById("formcurcic").modal('show');
    });    
}

// Borra una asociación de curso con ciclo
function borrarCurso(idCiclo, idCurso)
{
    fetch('ajax/ciclos/borrar_curso_ciclo.php', {method: 'POST', body: new URLSearchParams({idCiclo:idCiclo, idCurso: idCurso})}).then(r => r.text()).then(res =>
        asociarCursos(idCiclo);
    });
}

// Actualiza los datos de un curso en el ciclo
function actualizarCurso(idCiclo, idCurso)
{
    let orden = $('#orden' + idCurso).value;

    fetch('ajax/ciclos/actualizar_curso_ciclo.php', {method: 'POST', body: new URLSearchParams({idCiclo:idCiclo, idCurso: idCurso, orden: orden})}).then(r => r.text()).then(res =>
        asociarCursos(idCiclo);
    });
}

// Añade un nuevo curso al ciclo
function nuevoCurso(idCiclo)
{
    let idCurso = $('#codigoAsociacionCurso').value;
    let orden = $('#orden').value;

    fetch('ajax/ciclos/insertar_curso_ciclo.php', {method: 'POST', body: new URLSearchParams({idCiclo:idCiclo, idCurso: idCurso, orden: orden})}).then(r => r.text()).then(res =>
        asociarCursos(idCiclo);
    });
}

// Evento de envío del formulario modal para inserción/modificación
document.getElementById("formcic").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(document.forms.formcic);
    $.ajax({
        url: "ajax/ciclos/insertar_ciclo.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioCiclos();
        document.getElementById("formciclo").modal('hide');
        cargarCiclos();
    });
});
