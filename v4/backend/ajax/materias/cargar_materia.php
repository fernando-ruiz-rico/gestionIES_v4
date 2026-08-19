<?php

// Devuelve los datos de la materia indicada, en formato JSON

if (!empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM materias WHERE id=" . $_REQUEST['idMateria']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>