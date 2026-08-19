<?php

// Devuelve en formato JSON los datos del ciclo que se indica

if (!empty($_REQUEST['idCiclo']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM ciclos WHERE id=" . $_REQUEST['idCiclo']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>