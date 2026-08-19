<?php

// Establece el modo rueda en el escenario indicado
// Con el modo rueda activo los profesores no pueden elegir por sí mismo las asignaturas, ni cambiar el orden

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['idEscenario']) && !empty($_REQUEST['valorActual']))
{
    include ('../../includes/database.php');
    // Invertimos el estado del modo rueda para el escenario
    $actual = $_REQUEST['valorActual'] == "no"?1:0;
    mysqli_query($db, "UPDATE escenarios_desideratas SET modo_rueda = $actual WHERE id = " . $_REQUEST['idEscenario']);
    include ('../../includes/database2.php');
}

?>