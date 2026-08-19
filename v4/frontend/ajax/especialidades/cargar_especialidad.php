<?php

// Esta página devuelve los datos de una especialidad en formato JSON. Recibe el id de la especialidad a buscar

if (!empty($_REQUEST['idEspecialidad']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM especialidades WHERE id='" . $_REQUEST['idEspecialidad'] . "'");
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>