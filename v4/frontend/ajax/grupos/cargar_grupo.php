<?php

// Devuelve los datos del grupo indicado, en formato JSON

if (!empty($_REQUEST['idGrupo']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM grupos WHERE id=" . $_REQUEST['idGrupo']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>