<?php

// Inserta o actualiza una unidad de competencia

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['codigoUnidad']))
{
    include('../../includes/database.php');
    $codigo = $_REQUEST['codigoUnidad'];
    $texto = $_REQUEST['textoUnidad'];

    // Si no llega id es una inserción
    if (empty($_REQUEST['idUnidad']))
    {
        mysqli_query($db, "INSERT INTO unidades_competencia (codigo, texto) VALUES ('$codigo', '$texto')");    
    } else {
        mysqli_query($db, "UPDATE unidades_competencia SET codigo='$codigo', texto='$texto' WHERE codigo = '" . $_REQUEST['idUnidad'] . "'");
        // Actualizamos también el código en la asociación con ciclos, si procede
        mysqli_query($db, "UPDATE unidades_ciclos SET codigoUnidad='$codigo' WHERE codigoUnidad = '" . $_REQUEST['idUnidad'] . "'");
    }
    include ('../../includes/database2.php');
}

?>