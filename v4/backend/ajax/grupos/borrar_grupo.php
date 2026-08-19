<?php

// Borra el grupo indicado. Devuelve "si" si ha habido algún error

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';
$error = TRUE;

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM grupos WHERE id = "  . $_REQUEST['id']);
    if (mysqli_affected_rows($db) > 0)
    {
        // Borramos también las elecciones y configuraciones de materias que tengan que ver con ese grupo
        $error = FALSE;
        mysqli_query($db, "DELETE FROM materias_grupos WHERE idGrupo = "  . $_REQUEST['id']);
        mysqli_query($db, "DELETE FROM programaciones_aula_temas WHERE idGrupo = "  . $_REQUEST['id']);
        mysqli_query($db, "DELETE FROM seleccion WHERE idGrupo = "  . $_REQUEST['id']);
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>