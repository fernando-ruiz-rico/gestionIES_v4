<?php

@session_start();

// Carga los botones de la parte inferior de la selección del profesor según la configuración del escenario

if(!empty($_REQUEST['idEscenario']))
{
    include('../../includes/database.php');

    $idEscenario = $_REQUEST['idEscenario'];

    // Vemos si el escenario está en modo rueda o no, para deshabilitar ciertas acciones (elegir materias por profesores)
    $resultado = mysqli_query($db, "SELECT modo_rueda FROM escenarios_desideratas WHERE id = " . $idEscenario);
    $modoRueda = FALSE;
    while($fila = mysqli_fetch_assoc($resultado))
        $modoRueda = $fila['modo_rueda'];
    mysqli_free_result($resultado);

    // Guardamos si el usuario tiene permisos superiores (jefe de departamento o admin)
    $super = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

    if($modoRueda && !$super)
    {
        echo '<script type="text/javascript">';
        echo 'dom("#listasel").sortable("disable");';
        echo '</script>';
    }
    else
    {
        echo '<img class="botonRueda" src="img/delete2.png" title="Quitar la materia seleccionada de la lista" onclick="borrarSeleccion()">&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<img class="botonRueda" src="img/deleteall.png" title="Vaciar lista" onclick="borrarTodaSeleccion()">&nbsp;&nbsp;&nbsp;&nbsp;';
    }

    echo '<img src="img/stats.png" title="Mostrar estadísticas y conflictos" onclick="estadisticas()">&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<img src="img/print.png" title="Imprimir la ficha del profesor" onclick="imprimirSeleccion(true)">&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<img src="img/timetable.png" title="Imprimir las preferencias de horario del profesor" onclick="imprimirPreferenciasSeleccion(true)">&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<img src="img/excel.png" title="Generar hoja Excel con los datos introducidos" onclick="generarExcel()">&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<img src="img/preview.png" title="Vista general de selecciones de todos los profesores" onclick="vistaPrevia()">&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<img src="img/reset.png" title="Actualizar estado de selección" onclick="actualizar()">';

    include ('../../includes/database2.php');
    
}

?>