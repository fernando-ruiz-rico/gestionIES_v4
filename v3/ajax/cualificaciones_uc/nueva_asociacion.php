<?php

// Añade una asociación de una unidad de competencia a una cualificación profesional

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['codigoCualificacion']) && !empty($_REQUEST['codigoUnidad']))
{
    include('../../includes/database.php');
    $codigoCualificacion = $_REQUEST['codigoCualificacion'];
    $codigoUnidad = $_REQUEST['codigoUnidad'];
    mysqli_query($db, "INSERT INTO cualificaciones_unidades (codigoCualificacion, codigoUnidad) VALUES ('$codigoCualificacion', '$codigoUnidad')");
    include ('../../includes/database2.php');
}

?>