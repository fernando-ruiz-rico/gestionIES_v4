<?php

// Esta página borra la especialidad cuyo "id" se pasa como parámetro. Deja a nulas las especialidades
// de los profesores vinculados
// Devuelve "si" si ha habido algún error en el proceso

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';
$error = TRUE;

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "UPDATE profesores SET especialidad = NULL WHERE especialidad = '" . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM especialidades WHERE id = "  . $_REQUEST['id']);
    if (mysqli_affected_rows($db) > 0)
    {
        $error = FALSE;
    }
    include ('../../includes/database2.php');
}


echo $error?'si':'no';

?>