<?php

// Establece cuál el escenario indicado como actualmente vigente en el centro (o no)
// Los escenarios actuales son los que se emplean durante el curso ordinario para las programaciones
// Los que no son actuales son, o bien futuros (elecciones de cara al curso próximo) o bien históricos

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['idEscenario']) && !empty($_REQUEST['valorActual']))
{
    include ('../../includes/database.php');
    // Invertimos el estado del escenario: si era actual deja de serlo y viceversa
    $actual = $_REQUEST['valorActual'] == "no"?1:0;
    mysqli_query($db, "UPDATE escenarios_desideratas SET actual = $actual WHERE id = " . $_REQUEST['idEscenario']);
    include ('../../includes/database2.php');
}

?>