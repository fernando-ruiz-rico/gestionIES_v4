<?php

// Borra la competencia indicada. Devuelve "si" si ha habido algún error

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';
$error = TRUE;

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM competencias_ciclos WHERE id = "  . $_REQUEST['id']);
    if (mysqli_affected_rows($db) > 0)
    {
        $error = FALSE;
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>