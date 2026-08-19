<?php

// Inserta o actualiza una cualificación profesional

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['codigoCualificacion']))
{
    include('../../includes/database.php');
    $codigo = $_REQUEST['codigoCualificacion'];
    $texto = $_REQUEST['textoCualificacion'];

    // Si no llega id es una inserción
    if (empty($_REQUEST['idCualificacion']))
    {
        mysqli_query($db, "INSERT INTO cualificaciones_profesionales (codigo, texto) VALUES ('$codigo', '$texto')");    
    } else {
        mysqli_query($db, "UPDATE cualificaciones_profesionales SET codigo='$codigo', texto='$texto' WHERE codigo = '" . $_REQUEST['idCualificacion'] . "'");
        // Actualizamos también el código en la asociación con UC, si procede
        mysqli_query($db, "UPDATE cualificaciones_unidades SET codigoCualificacion='$codigo' WHERE codigoCualificacion = '" . $_REQUEST['idCualificacion'] . "'");
    }
    include ('../../includes/database2.php');
}

?>