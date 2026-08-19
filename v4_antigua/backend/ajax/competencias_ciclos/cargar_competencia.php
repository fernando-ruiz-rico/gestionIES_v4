<?php

// Devuelve los datos de la competencia indicada, en formato JSON

if (!empty($_REQUEST['idCompetencia']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM competencias_ciclos WHERE id=" . $_REQUEST['idCompetencia']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>