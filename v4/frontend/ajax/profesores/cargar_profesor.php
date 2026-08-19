<?php

// Devuelve los datos del profesor indicado, en formato JSON

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idProfesor']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM profesores WHERE id=" . $_REQUEST['idProfesor']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>