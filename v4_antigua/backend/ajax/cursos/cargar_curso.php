<?php

// Devuelve en formato JSON los datos del curso que se indica

if (!empty($_REQUEST['idCurso']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM cursos WHERE id=" . $_REQUEST['idCurso']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>