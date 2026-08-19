// Funciones para la gestión de las programaciones de aula

let selMateria = 0;  // Materia seleccionada
let selGrupo = 0;    // Grupo seleccionado
let selEvaluacion = 0;     // Evaluación seleccionada
let selDepartamento = document.getElementById('idDepartamento').value; // Departamento seleccionado
let selProfesor = document.getElementById('idProfesor').value; // Profesor seleccionado
let selCurso = document.getElementById('curso').value; // Curso seleccionado

const camposTexto = ["temporalizacion", "resultados", "inclusion"];
const camposNumero = ["num_aprobados", "num_suspensos", "num_otros"];

// Función para recargar la página con el profesor seleccionado en el desplegable (si lo hay)
function seleccionarProfesor()
{
    selProfesor = document.getElementById('seleccionProfesor').value;
    document.getElementById('idProfesor').value = selProfesor;
    if (selProfesor) {
        window.location.href = "programaciones_seguimiento_aula.php?idProfesor=" + selProfesor;
    }
}

// Cambia la materia seleccionada
function cambiarMateria()
{    
    selMateria = document.getElementById('idMateria').value;
    selGrupo = 0;
    document.getElementById('idGrupo').value = selGrupo;

    // Actualizamos los grupos según la materia elegida
    document.getElementById('idGrupo').prop('disabled', true).innerHTML = '<option value="0">Cargando…</option>';
    fetch("ajax/programaciones_aula/cargar_grupos.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" } }).then(r => r.json())
        data: { idMateria: selMateria, idProfesor: selProfesor }})
    .then(function(res) {
        let opciones = '<option value="0">--Selecciona un grupo--</option>';
        res.forEach(function (a) {
            opciones += `<option value="${a.id}">${a.nombre}</option>`;
        });
        document.getElementById('idGrupo').innerHTML = opciones.prop('disabled', false);   
    });

    cargarContenido();
}

// Cambiar el grupo seleccionado
function cambiarGrupo()
{
    selGrupo = document.getElementById('idGrupo').value;
    cargarContenido();
}

// Cambiar el grupo seleccionado
function cambiarEvaluacion()
{
    selEvaluacion = document.getElementById('idEvaluacion').value;
    cargarContenido();
}

// Calcula el total de alumnos
function calcularTotalAlumnos()
{
    let total = 0;
    camposNumero.forEach(function(idCampo) {
        total += parseInt(document.getElementById(idCampo).value);
    });
    document.getElementById('alumnostotal').value = total;
}    

// Comprueba si debe cargar contenido en el editor TinyMCE
function cargarContenido()
{
    if(selMateria > 0 && selGrupo > 0 && selEvaluacion > 0)
    {
        fetch("ajax/programaciones_seguimiento/cargar_datos_seguimiento_aula.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idMateria: selMateria, idGrupo: selGrupo, idProfesor: selProfesor, curso: selCurso, idEvaluacion: selEvaluacion}).toString() }).then(r => r.text()).then(res => {
            document.getElementById('edicionseguimientoaula').style.display = 'block';
            camposTexto.forEach(function(idCampo) {
                if (tinymce.get(idCampo)) {
                    tinymce.get(idCampo).setContent(res[idCampo] ? res[idCampo] : '');
                }
            });
            camposNumero.forEach(function(idCampo) {
                document.getElementById(idCampo).value = res[idCampo] ? res[idCampo] : 0;
            });
            calcularTotalAlumnos();
        });
    }
    else
    {
        document.getElementById('edicionseguimientoaula').style.display = 'none';
    }
}

// Genera un PDF con el seguimiento de todas las programaciones
function generarPDFSeguimientoAula(categoria)
{
    if (selEvaluacion)
    {
        window.open('pdf_programaciones_seguimiento.php?departamento=' + selDepartamento + '&curso=' + selCurso + '&evaluacion=' + selEvaluacion + '&categoria=' + categoria);
    } else {
        mostrarMensaje("Debes seleccionar una evaluación", 2);        
    }
}

// Guardar cambios al contenido editado
document.getElementById("formSeguimientoAula").addEventListener("submit", function(e)
{
    e.preventDefault();
    calcularTotalAlumnos();
    camposTexto.forEach(function(idCampo) {
        if (tinymce.get(idCampo)) {
            tinymce.get(idCampo).save();
        }
    });
    const formData = new FormData(document.forms.formSeguimientoAula);
    fetch("ajax/programaciones_seguimiento/insertar_seguimiento_programacion_aula.php", { method: "POST", body: formData })
    .then(function(res) {
        if (res.trim() == 'si')
            mostrarMensaje("Error al realizar la operación indicada. Si no has hecho cambios respecto al contenido previamente guardado, ignora este mensaje", 0);
        else
            mostrarMensaje("Datos guardados correctamente", 1);
    });
});

// Configuración de TinyMCE si procede
if(document.getElementById('temporalizacion').length > 0 && document.getElementById('resultados').length > 0 && document.getElementById('inclusion').length > 0)
{
    initTinyMCE('seguimientoeditar', 200);

    // Si hay TinyMCE hay formulario. Inicialmente lo ocultamos
    // Sólo se mostrará si elegimos un apartado concreto del listado
    document.getElementById('edicionseguimientoaula').style.display = 'none';
}