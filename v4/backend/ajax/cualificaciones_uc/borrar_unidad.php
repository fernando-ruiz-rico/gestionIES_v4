<?php

// Elimina los datos de la unidad de competencia
// Devuelve "si" si se ha producido algún error en el proceso

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

$error = TRUE;

if ($permisos && !empty($_REQUEST['codigo']))
{
    include ('../../includes/database.php');
    // Eliminamos también las asociaciones de la unidad con los ciclos que haya
    mysqli_query($db, "DELETE FROM unidades_ciclos WHERE codigoUnidad = '"  . $_REQUEST['codigo'] . "'");
    mysqli_query($db, "DELETE FROM unidades_competencia WHERE codigo = '"  . $_REQUEST['codigo'] . "'");
    if (mysqli_affected_rows($db) > 0)
    {
        $error = FALSE;
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>