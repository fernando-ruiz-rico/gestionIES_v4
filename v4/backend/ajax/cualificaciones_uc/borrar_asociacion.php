<?php

// Elimina la asociación de una unidad de competencia a una cualificación profesional

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

$error = TRUE;

if ($permisos && !empty($_REQUEST['codigoCualificacion']) && !empty($_REQUEST['codigoUnidad']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM cualificaciones_unidades WHERE codigoCualificacion = '"  . $_REQUEST['codigoCualificacion'] . "' AND codigoUnidad = '" . $_REQUEST['codigoUnidad'] . "'");
    if (mysqli_affected_rows($db) > 0)
    {
        $error = FALSE;
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>