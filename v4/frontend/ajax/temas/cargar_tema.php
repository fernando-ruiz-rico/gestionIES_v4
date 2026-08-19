<?php

// Devuelve en formato JSON los datos del tema que se indica

if (!empty($_REQUEST['idTema']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM temas WHERE id=" . $_REQUEST['idTema']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>