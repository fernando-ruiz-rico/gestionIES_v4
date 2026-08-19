// Funciones para la gestión de resultados de aprendizaje

// Variable para almacenar la materia seleccionada
let selMateria = 0;

// Función para cambiar la materia seleccionada
function cambiarMateria()
{
    selMateria = $('#seleccionMateria').value;
    if(selMateria > 0)
    {
        $('#idMateria').value = selMateria;
        cargarResultados();
    }
}

// Carga los resultados de aprendizaje para la materia seleccionada
function cargarResultados()
{
    if(selMateria > 0)
    {
        $('#resultados').load("ajax/resultados_aprendizaje/cargar_resultados_aprendizaje.php", {idMateria: selMateria});
    }
}

// Muestra los datos del resultado indicado en el formulario modal, para su edición
function cargarResultadoModal(id)
{
    fetch('ajax/resultados_aprendizaje/cargar_resultado_aprendizaje.php?' + new URLSearchParams({idResultado:id})).then(r => r.json()).then(res =>
        $('#idResultado').value = id;
        $('#texto').value = res.texto;
        $('#orden').value = res.orden;
        $('#porcentajeEmpresa').value = res.porcentaje_empresa;
        document.getElementById("formresultado").modal('show');
    });    
}

// Muestra el formulario modal limpio para insertar un nuevo resultado
function nuevoResultado()
{
    if(selMateria > 0)
    {
        limpiarFormularioResultados();
        $('#formresultado').modal('show');
    } else {
        mostrarMensaje("Debes elegir una materia primero", 2);
    }
}

// Borra el resultado indicado, previa confirmación
function borrarResultado (id, texto)
{
    if (confirm("Confirmas el borrado del resultado '" + texto + "'?"))
    {
        fetch('ajax/resultados_aprendizaje/borrar_resultado_aprendizaje.php', {method: 'POST', body: new URLSearchParams({id:id})}).then(r => r.text()).then(res =>
            cargarResultados();
        });            
    }
}

// Borra el contenido de los campos del formulario modal de alta/edición de cursos
function limpiarFormularioResultados()
{
    $('#idResultado').value = "";
    $('#texto').value = "";
    $('#orden').value = "";
    $('#porcentajeEmpresa').value = "0";    
}

// Actualiza las horas asignadas de la materia indicada para impartir en la empresa
function actualizarHorasEmpresa(idMateria)
{
    var horas = $('#horasEmpresa').value;
    $.post("ajax/materias/actualizar_horas_empresa.php", {idMateria:idMateria, horas: horas});
    mostrarMensaje("Datos actualizados", 1);
}

// Carga el modal para asociar criterios de evaluación al RA indicado
function asociarCriterios(id)
{
    fetch('ajax/resultados_aprendizaje/cargar_criterios_evaluacion.php?' + new URLSearchParams({idResultado:id})).then(r => r.json()).then(res =>
        $('#listadocriterios').innerHTML = res;
        $('#formcriterio').modal('show');
    });
}

// Borra el criterio de evaluación indicado por el id del RA y el código de criterio
function borrarCriterio(idRA, codigo)
{
    fetch('ajax/resultados_aprendizaje/borrar_criterio_evaluacion.php', {method: 'POST', body: new URLSearchParams({idResultado:idRA, codigo: codigo})}).then(r => r.text()).then(res =>
        asociarCriterios(idRA);
    });
}

// Actualiza los datos del criterio de evaluación indicado
function actualizarCriterio(idRA, codigo)
{
    let nuevoCodigo = $('#cce' + codigo).value;
    let nuevoTexto = $('#tce' + codigo).value;
    fetch('ajax/resultados_aprendizaje/actualizar_criterio_evaluacion.php', {method: 'POST', body: new URLSearchParams({idResultado:idRA, codigo: codigo, nuevoCodigo: nuevoCodigo, nuevoTexto: nuevoTexto})}).then(r => r.text()).then(res =>
        asociarCriterios(idRA);
    });
}

// Inserta un nuevo criterio para el RA indicado
function insertarCriterio(idRA)
{
    let nuevoCodigo = $('#codigoCE').value;
    let nuevoTexto = $('#textoCE').value;

    fetch('ajax/resultados_aprendizaje/insertar_criterio_evaluacion.php', {method: 'POST', body: new URLSearchParams({idResultado:idRA, nuevoCodigo: nuevoCodigo, nuevoTexto: nuevoTexto})}).then(r => r.text()).then(res =>
        asociarCriterios(idRA);
    });
}

// Evento de envío del formulario modal para inserción/modificación
document.getElementById("formres").on("submit", function(e)
{
    e.preventDefault();
    let formData = new FormData(document.forms.formres);
    $.ajax({
        url: "ajax/resultados_aprendizaje/insertar_resultado_aprendizaje.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        limpiarFormularioResultados();
        document.getElementById("formresultado").modal('hide');
        cargarResultados();
    });
});
