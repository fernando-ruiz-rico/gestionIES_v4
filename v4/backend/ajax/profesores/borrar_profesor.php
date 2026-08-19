<?php

// Borra el profesor indicado de la base de datos. Elimina también todos los vínculos que tuviera (selección de materias, preferencias de horario...)

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM seleccion WHERE idProfesor = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM preferencias_horario WHERE idProfesor = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM programaciones_aula_temas WHERE idProfesor = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM profesores WHERE id = "  . $_REQUEST['id']);
    include ('../../includes/database2.php');
}

?>