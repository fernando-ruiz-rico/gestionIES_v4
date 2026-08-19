<?php

// Devuelve en formato JSON los datos del apartado solicitado

if (!empty($_REQUEST['idApartado']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM apartados_programaciones WHERE id=" . $_REQUEST['idApartado']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>