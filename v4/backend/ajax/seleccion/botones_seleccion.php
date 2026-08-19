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
        echo '<i class="bi bi-trash botonRueda" title="Quitar la materia seleccionada de la lista" onclick="borrarSeleccion()"></i>&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<i class="bi bi-trash3 botonRueda" title="Vaciar lista" onclick="borrarTodaSeleccion()"></i>&nbsp;&nbsp;&nbsp;&nbsp;';
    }

    echo '<i class="bi bi-bar-chart"></i>&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<i class="bi bi-printer"></i>&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<i class="bi bi-calendar-week"></i>&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<i class="bi bi-file-earmark-spreadsheet"></i>&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<i class="bi bi-eye"></i>&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<i class="bi bi-arrow-counterclockwise"></i>';

    include ('../../includes/database2.php');
    
}

?>