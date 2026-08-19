<?php

// Esta página recibe como parámetro un "id" de departamento y devuelve en formato JSON sus datos

if (!empty($_REQUEST['idDepartamento']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM departamentos WHERE id=" . $_REQUEST['idDepartamento']);
    $resultado = mysqli_fetch_assoc($result);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>