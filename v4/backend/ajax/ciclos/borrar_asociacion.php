<?php

// Elimina la asociación de una unidad de competencia a un ciclo

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

$error = TRUE;

if ($permisos && !empty($_REQUEST['idCiclo']) && !empty($_REQUEST['codigoUnidad']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM unidades_ciclos WHERE idCiclo = "  . $_REQUEST['idCiclo'] . " AND codigoUnidad = '" . $_REQUEST['codigoUnidad'] . "'");
    if (mysqli_affected_rows($db) > 0)
    {
        $error = FALSE;
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>