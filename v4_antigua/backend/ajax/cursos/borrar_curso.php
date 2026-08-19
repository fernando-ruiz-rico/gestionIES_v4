<?php

// Elimina los datos del curso indicado, junto con las materias y grupos asociados
// No se puede borrar un curso si tiene materias y grupos asociados
// Devuelve "si" si se ha producido algún error en el proceso

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';
$error = TRUE;

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    $result = mysqli_query($db, "SELECT id FROM grupos WHERE idCurso = " . $_REQUEST['id'] . " UNION SELECT id FROM materias WHERE idCurso = " . $_REQUEST['id']);
    if (mysqli_num_rows($result) == 0)
    {
        mysqli_query($db, "DELETE FROM cursos WHERE id = "  . $_REQUEST['id']);
        if (mysqli_affected_rows($db) > 0)
        {
            $error = FALSE;
            mysqli_query($db, "DELETE FROM seleccion WHERE idMateria IN (SELECT id FROM materias WHERE idCurso = "  . $_REQUEST['id'] . ")");
            mysqli_query($db, "DELETE FROM materias WHERE idCurso = "  . $_REQUEST['id']);
            mysqli_query($db, "DELETE FROM grupos WHERE idCurso = "  . $_REQUEST['id']);
        }
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>