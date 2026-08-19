<?php

// Elimina la materia indicada. También la borra de las selecciones que hayan hecho
// los profesores

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM seleccion WHERE idMateria = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM materias_grupos WHERE idMateria = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM contenidos_programaciones WHERE idMateria = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM materias WHERE id = "  . $_REQUEST['id']);
    include ('../../includes/database2.php');
}

?>