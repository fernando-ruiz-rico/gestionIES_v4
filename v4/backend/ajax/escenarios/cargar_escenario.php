<?php

// Devuelve un JSON con los datos del escenario actual

if (!empty($_REQUEST['idEscenario']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM escenarios_desideratas WHERE id=" . $_REQUEST['idEscenario']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>