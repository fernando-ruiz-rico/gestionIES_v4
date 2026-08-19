// Funciones para el seguimiento de las programaciones

// Variables para almacenar el curso, evaluación o materia seleccionados
var selMateria = 0;
var selCurso = "";
var selEvaluacion = 0;

// Si hay desplegable de curso, elegimos el curso actual
// La variable "cursoActual" se ha establecido en la página "programaciones_seguimiento.php"
document.getElementById('cursoSeguimiento').value = cursoActual;

// Actualiza los valores seleccionados de curso, evaluación y materia y comprueba que
// no estén vacíos
function actualizarDatos(comunes = false)
{
    selMateria = document.getElementById('materiaSeguimiento').value;
    if (cursoActual == "")
        selCurso = document.getElementById('cursoSeguimiento').value;
    else
        selCurso = cursoActual;
    selEvaluacion = document.getElementById('evaluacionSeguimiento').value;    
    
    // Si vamos a editar datos comunes no hace falta seleccionar materia
    return (selMateria > 0 || comunes) && selCurso != "" && selEvaluacion > 0;
}

function ejecutarOperacionSeleccionada(operacion)
{
    // Cargar datos de seguimiento del curso, materia y evaluación elegidos
    if (operacion == 'cargarDatos')
    {
        if(actualizarDatos(true))
        {
            fetch("ajax/programaciones_seguimiento/cargar_datos_seguimiento_comun.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({curso: selCurso, evaluacion: selEvaluacion}).toString() }).then(r => r.text()).then(res => {
                if (res == "error")
                {
                    mostrarMensaje("No se encontraron datos", 2);
                    if (tinymce.get('funcionamiento_departamento'))
                        tinymce.get('funcionamiento_departamento').setContent("");
                    if (tinymce.get('actividades'))
                        tinymce.get('actividades').setContent("");       
                    if (tinymce.get('temporalizacion_defecto'))
                        tinymce.get('temporalizacion_defecto').setContent("");       
                }
                else
                {
                    if (tinymce.get('funcionamiento_departamento'))
                        tinymce.get('funcionamiento_departamento').setContent(res.funcionamiento_departamento);
                    if (tinymce.get('actividades'))
                        tinymce.get('actividades').setContent(res.actividades_extraescolares);
                    if (tinymce.get('temporalizacion_defecto'))
                        tinymce.get('temporalizacion_defecto').setContent(res.temporalizacion_defecto);       
                }
            });
        } else {            
            if (tinymce.get('funcionamiento_departamento'))
                tinymce.get('funcionamiento_departamento').setContent("");
            if (tinymce.get('actividades'))
                tinymce.get('actividades').setContent("");
            if (tinymce.get('temporalizacion_defecto'))
                tinymce.get('temporalizacion_defecto').setContent("");       
        }

        if (actualizarDatos())
        {
            fetch("ajax/programaciones_seguimiento/cargar_datos_seguimiento.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({idMateria: selMateria, curso: selCurso, evaluacion: selEvaluacion}).toString() }).then(r => r.text()).then(res => {
                if (res == "error")
                {
                    mostrarMensaje("No se encontraron datos", 2);
                    if (tinymce.get('temporalizacion'))
                        tinymce.get('temporalizacion').setContent("");
                    if (tinymce.get('resultados'))
                        tinymce.get('resultados').setContent("");       
                    document.getElementById('resultadosPorcentaje').value = 0;                
                }
                else
                {
                    if (tinymce.get('temporalizacion'))
                        tinymce.get('temporalizacion').setContent(res.temporalizacion);
                    if (tinymce.get('resultados'))
                        tinymce.get('resultados').setContent(res.resultados);
                    document.getElementById('resultadosPorcentaje').value = res.resultados_porcentaje;
                }
            });
        } else {
            if (tinymce.get('temporalizacion'))
                tinymce.get('temporalizacion').setContent("");
            if (tinymce.get('resultados'))
                tinymce.get('resultados').setContent("");
            document.getElementById('resultadosPorcentaje').value = 0;
        }
    // Importar datos de evaluación anterior
    } else if (operacion == 'importarEvaluacion') {
        if (confirm ("Al importar datos de otra evaluación se perderá lo que haya introducido hasta ahora. ¿Quieres continuar?"))
        {
            if (actualizarDatos())
            {
                if (selEvaluacion == 1)
                {
                    mostrarMensaje('Esta opción sólo está disponible para la 2ª o 3ª evaluación', 0)
                } else {
                    fetch("ajax/programaciones_seguimiento/cargar_datos_seguimiento.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({modo: 'evaluacion', idMateria: selMateria, curso: selCurso, evaluacion: selEvaluacion}).toString() }).then(r => r.text()).then(res => {
                        if (res == "error")
                        {
                            mostrarMensaje("No se encontraron datos", 2);
                            if (tinymce.get('temporalizacion'))
                                tinymce.get('temporalizacion').setContent("");
                            if (tinymce.get('resultados'))
                                tinymce.get('resultados').setContent("");       
                            document.getElementById('resultadosPorcentaje').value = 0;                
                        }
                        else
                        {
                            if (tinymce.get('temporalizacion'))
                                tinymce.get('temporalizacion').setContent(res.temporalizacion);
                            if (tinymce.get('resultados'))
                                tinymce.get('resultados').setContent(res.resultados);
                            document.getElementById('resultadosPorcentaje').value = res.resultados_porcentaje;
                        }
                    });                
                }
            } else {
                mostrarMensaje('Falta algún elemento por seleccionar (materia, curso o evaluación)', 2);
            }        
        }
    // Importar datos de curso anterior
    } else if (operacion == 'importarCursoAnterior') {
        if (confirm ("Al importar datos de otro curso se perderá lo que haya introducido hasta ahora. ¿Quieres continuar?"))
        {
            if (actualizarDatos())
            {
                if (selCurso != cursoActual)
                {
                    mostrarMensaje('Esta opción sólo está disponible para el curso actual', 0)
                } else {
                    fetch("ajax/programaciones_seguimiento/cargar_datos_seguimiento.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({modo: 'curso', idMateria: selMateria, curso: selCurso, evaluacion: selEvaluacion}).toString() }).then(r => r.text()).then(res => {
                        if (res == "error")
                        {
                            mostrarMensaje("No se encontraron datos", 2);
                            if (tinymce.get('temporalizacion'))
                                tinymce.get('temporalizacion').setContent("");
                            if (tinymce.get('resultados'))
                                tinymce.get('resultados').setContent("");       
                            document.getElementById('resultadosPorcentaje').value = 0;                
                        }
                        else
                        {
                            if (tinymce.get('temporalizacion'))
                                tinymce.get('temporalizacion').setContent(res.temporalizacion);
                            if (tinymce.get('resultados'))
                                tinymce.get('resultados').setContent(res.resultados);
                            document.getElementById('resultadosPorcentaje').value = res.resultados_porcentaje;
                        }
                    });                
                }
            } else {
                mostrarMensaje('Falta algún elemento por seleccionar (materia, curso o evaluación)', 2);            
            }
        }
    } else if (operacion == 'vistaPrevia') {
        if (actualizarDatos())
        {
            window.open('programaciones_seguimiento_vista_previa.php?idMateria=' + selMateria + '&curso=' + selCurso + '&evaluacion=' + selEvaluacion);        
        } else {
            mostrarMensaje('Falta algún elemento por seleccionar (materia, curso o evaluación)', 2);            
        }
    }        
    else if (operacion == 'cargarDatosComun')
    {
        if (actualizarDatos(true))
        {
            fetch("ajax/programaciones_seguimiento/cargar_datos_seguimiento_comun.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({curso: selCurso, evaluacion: selEvaluacion}).toString() }).then(r => r.text()).then(res => {
                if (res == "error")
                {
                    mostrarMensaje("No se encontraron datos", 2);
                    if (tinymce.get('funcionamiento_departamento'))
                        tinymce.get('funcionamiento_departamento').setContent("");
                    if (tinymce.get('actividades'))
                        tinymce.get('actividades').setContent("");       
                    if (tinymce.get('temporalizacion_defecto'))
                        tinymce.get('temporalizacion_defecto').setContent("");       
                }
                else
                {
                    if (tinymce.get('funcionamiento_departamento'))
                        tinymce.get('funcionamiento_departamento').setContent(res.funcionamiento_departamento);
                    if (tinymce.get('actividades'))
                        tinymce.get('actividades').setContent(res.actividades_extraescolares);
                    if (tinymce.get('temporalizacion_defecto'))
                        tinymce.get('temporalizacion_defecto').setContent(res.temporalizacion_defecto);       
                }
            });
        } else {            
            if (tinymce.get('funcionamiento_departamento'))
                tinymce.get('funcionamiento_departamento').setContent("");
            if (tinymce.get('actividades'))
                tinymce.get('actividades').setContent("");
            if (tinymce.get('temporalizacion_defecto'))
                tinymce.get('temporalizacion_defecto').setContent("");       
        }
    } else if (operacion == 'importarEvaluacionComun') {
        if (confirm ("Al importar datos de otra evaluación se perderá lo que haya introducido hasta ahora. ¿Quieres continuar?"))
        {
            if (actualizarDatos(true))
            {
                if (selEvaluacion == 1)
                {
                    mostrarMensaje('Esta opción sólo está disponible para la 2ª o 3ª evaluación', 0)
                } else {
                    fetch("ajax/programaciones_seguimiento/cargar_datos_seguimiento_comun.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({modo: 'evaluacion', curso: selCurso, evaluacion: selEvaluacion}).toString() }).then(r => r.text()).then(res => {
                        if (res == "error")
                        {
                            mostrarMensaje("No se encontraron datos", 2);
                            if (tinymce.get('funcionamiento_departamento'))
                                tinymce.get('funcionamiento_departamento').setContent("");
                            if (tinymce.get('actividades'))
                                tinymce.get('actividades').setContent("");
                            if (tinymce.get('temporalizacion_defecto'))
                                tinymce.get('temporalizacion_defecto').setContent("");       
                        }
                        else
                        {
                            if (tinymce.get('funcionamiento_departamento'))
                                tinymce.get('funcionamiento_departamento').setContent(res.funcionamiento_departamento);
                            if (tinymce.get('actividades'))
                                tinymce.get('actividades').setContent(res.actividades_extraescolares);
                            if (tinymce.get('temporalizacion_defecto'))
                                tinymce.get('temporalizacion_defecto').setContent(res.temporalizacion_defecto);       
                        }
                    });
                }
            } else {
                mostrarMensaje('Falta algún elemento por seleccionar (curso o evaluación)', 2);            
            }
        }
    } else if (operacion == 'importarCursoAnteriorComun') {
        if (confirm ("Al importar datos de otro curso se perderá lo que haya introducido hasta ahora. ¿Quieres continuar?"))
        {
            if (actualizarDatos(true))
            {
                if (selCurso != cursoActual)
                {
                    mostrarMensaje('Esta opción sólo está disponible para el curso actual', 0)
                } else {
                    fetch("ajax/programaciones_seguimiento/cargar_datos_seguimiento_comun.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: new URLSearchParams({modo: 'curso', curso: selCurso, evaluacion: selEvaluacion}).toString() }).then(r => r.text()).then(res => {
                        if (res == "error")
                        {
                            mostrarMensaje("No se encontraron datos", 2);
                            if (tinymce.get('funcionamiento_departamento'))
                                tinymce.get('funcionamiento_departamento').setContent("");
                            if (tinymce.get('actividades'))
                                tinymce.get('actividades').setContent("");
                            if (tinymce.get('temporalizacion_defecto'))
                                tinymce.get('temporalizacion_defecto').setContent("");       
                        }
                        else
                        {
                            if (tinymce.get('funcionamiento_departamento'))
                                tinymce.get('funcionamiento_departamento').setContent(res.funcionamiento_departamento);
                            if (tinymce.get('actividades'))
                                tinymce.get('actividades').setContent(res.actividades_extraescolares);
                            if (tinymce.get('temporalizacion_defecto'))
                                tinymce.get('temporalizacion_defecto').setContent(res.temporalizacion_defecto);       
                            }
                    });                
                }
            } else {
                mostrarMensaje('Falta algún elemento por seleccionar (curso o evaluación)', 2);
            }
        }
    } else if (operacion == 'vistaPreviaComun') {
        if (actualizarDatos(true))
        {
            window.open('programaciones_seguimiento_vista_previa.php?curso=' + selCurso + '&evaluacion=' + selEvaluacion);
        } else {
            mostrarMensaje('Falta algún elemento por seleccionar (curso o evaluación)', 2);            
        }
    }        
}

// Genera un PDF con el seguimiento de todas las programaciones (sólo accesible desde usuarios admin o jefes de departamento)
function generarPDFSeguimiento()
{
    if (actualizarDatos(true))
    {
        window.open('pdf_programaciones_seguimiento.php?curso=' + selCurso + '&evaluacion=' + selEvaluacion);
    } else {
        mostrarMensaje("Debes seleccionar un curso y evaluación", 2);        
    }
}

// Guardar datos de seguimiento de materia
document.getElementById("formseguimiento").addEventListener("submit", function(e)
{
    if (actualizarDatos())
    {
        e.preventDefault();
        document.getElementById('idMateria').value = selMateria;
        document.getElementById('curso').value = selCurso;
        document.getElementById('evaluacion').value = selEvaluacion;
        tinymce.get('temporalizacion').save();
        tinymce.get('resultados').save();
        var formData = new FormData(document.forms.formseguimiento);
        fetch("ajax/programaciones_seguimiento/insertar_seguimiento_programacion.php", { method: "POST", body: formData })
        .then(function(res) {
            if (res.trim() == 'si')
                mostrarMensaje("Error al realizar la operación indicada", 0);
            else
                mostrarMensaje("Datos guardados correctamente", 1);
        });
    } else {
        mostrarMensaje("Debes seleccionar una materia, curso y evaluación", 2);        
    }
});

// Guardar datos de seguimiento comunes
document.getElementById("formseguimientocomun").addEventListener("submit", function(e)
{
    if (actualizarDatos(true))
    {
        e.preventDefault();
        document.getElementById('cursoComun').value = selCurso;
        document.getElementById('evaluacionComun').value = selEvaluacion;
        tinymce.get('funcionamiento_departamento').save();
        tinymce.get('actividades').save();
        tinymce.get('temporalizacion_defecto').save();
        var formData = new FormData(document.forms.formseguimientocomun);
        fetch("ajax/programaciones_seguimiento/insertar_seguimiento_comun_programacion.php", { method: "POST", body: formData })
        .then(function(res) {
            if (res.trim() == 'si')
                mostrarMensaje("Error al realizar la operación indicada", 0);
            else
                mostrarMensaje("Datos guardados correctamente", 1);
        });
    } else {
        mostrarMensaje("Debes seleccionar un curso y evaluación", 2);        
    }
});

// Configuración de TinyMCE
initTinyMCE('seguimiento');