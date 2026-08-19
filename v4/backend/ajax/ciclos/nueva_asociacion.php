<?php

// Añade una asociación de una unidad de competencia a un ciclo

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['idCiclo']) && !empty($_REQUEST['codigoUnidad']))
{
    include('../../includes/database.php');
    $idCiclo = $_REQUEST['idCiclo'];
    $codigoUnidad = $_REQUEST['codigoUnidad'];
    mysqli_query($db, "INSERT INTO unidades_ciclos (idCiclo, codigoUnidad) VALUES ($idCiclo, '$codigoUnidad')");
    include ('../../includes/database2.php');
}

?>