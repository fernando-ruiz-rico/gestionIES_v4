<?php

// Devuelve en formato JSON los datos de la cualificación que se indica

if (!empty($_REQUEST['codigo']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM cualificaciones_profesionales WHERE codigo='" . $_REQUEST['codigo'] . "'");
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>