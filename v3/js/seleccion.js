// Funciones para selección de materias en desideratas

// Variable para almacenar el escenario seleccionado
var selEscenario = -1;
// Variable para almacenar la especialidad seleccionada
var selEspecialidad = "Todos";
// Variable para almacenar el profesor seleccionado
var selProf = -1;
// Variable para almacenar la especialidad del profesor seleccionado
var selEspecialidadProf = "";
// Variable para almacenar la materia escogida de entre la selección del profesor (panel derecho)
var selSel = -1;

// Función que se activa cuando se selecciona un escenario del desplegable
function seleccionarEscenario()
{
    selEscenario = $('#escenario').val();
    if (selEscenario <= 0)
    {
        $('.panelseleccion').hide();
    } else {
        
        $('.panelseleccion').show();
        listarProfesores();
        listarCursos();
        listarSeleccion();
    }
}

// Marca como seleccionado al profesor indicado
function seleccionarProfesor(id, especialidad)
{
    selProf = id;
    selEspecialidadProf= especialidad;
    $('.profesor').attr('class', 'profesor izquierda claro');
    if (id > 0)
    {
        $('#prof'+id).attr('class', 'profesor izquierda oscuro');
        listarSeleccion()
    }
}

// Cambia la especialidad para listar los profesores que pertenezcan a esa especialidad
function cambiarEspecialidad(especialidad)
{
    selEspecialidad = especialidad;
    listarProfesores();
}

// Carga los profesores de la especialidad indicada para el escenario indicado
function listarProfesores()
{
    if(selEscenario > 0)
    {
        $('#listaprof').load("ajax/seleccion/listar_profesores.php", {idEspecialidad:selEspecialidad, idEscenario:selEscenario}, function()
        {
            seleccionarProfesor(selProf, selEspecialidadProf);
        });
    }
}

// Carga los cursos disponibles para seleccionar materias
function listarCursos()
{
    $('#listacur').load("ajax/seleccion/listar_cursos.php", {idEscenario:selEscenario}, function()
    {
        $('#listacur').accordion("refresh");
        $('#listacur').accordion({active: false});        
    });    
}

// Muestra la selección de materias del profesor actualmente seleccionado
function listarSeleccion()
{
    if(selProf > 0 && selEscenario > 0)
    {
        $('#listasel').load("ajax/seleccion/listar_seleccion.php?idProfesor="+selProf+"&idEscenario="+selEscenario);
        $('#profsel').load("ajax/seleccion/profesor_seleccion.php?idProfesor="+selProf+"&idEscenario="+selEscenario);
        $('#totalsel').load("ajax/seleccion/sumar_seleccion.php?idProfesor="+selProf+"&idEscenario="+selEscenario);    
        $('#botonsel').load("ajax/seleccion/botones_seleccion.php?idEscenario="+selEscenario);
    }
}

// Muestra el formulario modal para elegir una materia:
// - idMateria: la materia a elegir
// - idGrupo: grupo del que se escoge la materia
// - especialidadMateria: especialidad de profesorado que debe impartir la materia
// - horas: horas semanales de la materia que se eligen (por defecto todas)
// - divisible: indica si la materia se puede dividir entre varios profesores o no
function seleccionarHorasMateria(idMateria, idGrupo, especialidadMateria, horas, divisible)
{
    if (selEscenario < 0)
    {
        mostrarMensaje("Debes seleccionar un escenario", 0);
    }
    else if (selProf > 0)
    {
        var res = true;
        if (selEspecialidadProf != especialidadMateria && especialidadMateria != null)
            res = confirm("La materia seleccionada no corresponde a tu especialidad (" + selEspecialidadProf + ")\n¿Confirmas que quieres seleccionarla?");
        if (res)
        {
            $('#idMateria').val(idMateria);
            $('#idGrupo').val(idGrupo);
            $('#horas').val(horas);
            // Las horas no se pueden modificar si la materia no se puede dividir entre varios profesores
            $('#horas').prop("readonly", !divisible);
            $('#formhorasseleccion').modal('show');
        }
    }
}

// Función invocada desde el botón para borrar la materia elegida del panel de selección
function borrarSeleccion()
{
    if (selSel > 0)
    {
        if (confirm("¿Seguro que deseas quitar la materia seleccionada de tu lista?"))
            $('#listasel').load("ajax/seleccion/borrar_seleccion.php?id="+selSel+"&idProfesor="+selProf+"&idEscenario="+selEscenario, function()
            {
                $('#totalsel').load("ajax/seleccion/sumar_seleccion.php?idProfesor="+selProf+"&idEscenario="+selEscenario);
                seleccionarSeleccion(-1);
                listarProfesores();
                listarCursos();
            });
    }
}

// Función invocada desde el botón para borrar todas las selecciones del profesor actual
function borrarTodaSeleccion()
{
    if (confirm("¿Seguro que deseas vaciar toda tu selección?"))
        $('#listasel').load("ajax/seleccion/borrar_toda_seleccion.php?idProfesor="+selProf+"&idEscenario="+selEscenario, function()
        {
            $('#totalsel').load("ajax/seleccion/sumar_seleccion.php?idProfesor="+selProf+"&idEscenario="+selEscenario);
            seleccionarSeleccion(-1);
            listarProfesores();
            listarCursos();
        });
}

// (Sólo para admin o jefe de departamento) Función invocada para borrar todas las selecciones del escenario actual
function borrarTodasSelecciones()
{
    if (selEscenario > 0 && confirm("¿Seguro que deseas eliminar todas las selecciones de todos los profesores para el escenario actual?"))
        $.post("ajax/seleccion/borrar_todas_selecciones.php?idEscenario="+selEscenario, function()
        {
            mostrarMensaje("La lista de selecciones ahora está vacía", 1);
            listarProfesores();
            listarCursos();
            listarSeleccion();
        });
}

// Marca como seleccionada una de las materias del panel de selección (derecho)
function seleccionarSeleccion(id)
{
    $('.seleccion').attr('class', 'seleccion izquierda claro');
    if (id > 0)
        $('#sel'+id).attr('class', 'seleccion izquierda oscuro');
    selSel = id;
}

// Función invocada desde el "badge" de cada materia, para ver qué profesores la han elegido
function cargarSeleccionesMateria(idMateria, idGrupo, idEscenario, nombreMateria, nombreCurso, nombreGrupo)
{
    $('#nombreMateria').html(nombreMateria);
    $('#nombreCurso').html(nombreCurso + nombreGrupo);
    // Cargamos en el "div" correspondiente del modal los profesores que han elegido esta materia
    $('#listadoProfesoresMateria').load('ajax/seleccion/cargar_listado_profesores_materia.php', {idMateria: idMateria, idGrupo: idGrupo, idEscenario: idEscenario}, function()
    {
        $('#seleccionesmateria').modal('show');          
    });
}

// Abre una ventana con las estadísticas para el escenario actual
function estadisticas()
{
    window.open("estadisticas.php?idEscenario=" + selEscenario);    
}

// Genera un Excel con la selección para el escenario actual
function generarExcel()
{
    window.open("excel.php?idEscenario=" + selEscenario);
}

// Genera un PDF con la selección del profesor indicado para el escenario actual
function imprimirSeleccion(unProfesor)
{
    if (unProfesor)
        window.open("pdf_desiderata.php?idProfesor="+selProf+"&idEscenario="+selEscenario);
    else
        window.open("pdf_desiderata.php?selEsp="+selEspecialidad+"&idEscenario="+selEscenario);   
}

// Genera un PDF con las preferencias horarias del profesor actual
function imprimirPreferenciasSeleccion(unProfesor)
{
    if(unProfesor)
        window.open("pdf_preferencias.php?idProfesor="+selProf);
    else
        window.open("pdf_preferencias.php?selEsp="+selEspecialidad);   
}

// Función invocada desde el formulario donde se selecciona una materia
function seleccionarHoras()
{
    var idMateria = $('#idMateria').val();
    var idGrupo = $('#idGrupo').val();
    var horas = $('#horas').val();
    $('#listasel').load("ajax/seleccion/insertar_seleccion.php?idMateria="+idMateria+"&idGrupo=" + idGrupo + "&idProfesor="+selProf + "&idEscenario=" + selEscenario + "&horas=" + horas, function()
    {
        $('#totalsel').load("ajax/seleccion/sumar_seleccion.php?idProfesor="+selProf+"&idEscenario="+selEscenario);        
        listarProfesores();
        seleccionarSeleccion(-1);
        listarCursos();
    });
    $('#formhorasseleccion').modal('hide');
}

// Muestra una vista previa general de la selección de todos los profesores para el escenario actual
function vistaPrevia()
{
    window.open("historico.php?idEscenario=" + selEscenario);
}

// Actualiza la selección actual y las materias disponibles
function actualizar()
{
    listarCursos();
    listarSeleccion();
}

// Función de inicialización. Se llama al cargar la página
function init()
{
    seleccionarEscenario();
    cambiarEspecialidad('Todos');
    listarCursos();
    $('#listacur').accordion({header: '.curso', heightStyle: 'content', active: false, collapsible: true});        
    $('#listasel').sortable({update: function()
    {
       var elementos = $(this).sortable("toArray").toString();
        $.get("ajax/seleccion/ordenar_seleccion.php", {idEscenario: selEscenario, orden: elementos});
    }
    });
}

init();
