<?php

// Inserta un nuevo curso a un ciclo

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['idCiclo']) && !empty($_REQUEST['idCurso']) && !empty($_REQUEST['orden']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "INSERT INTO cursos_ciclos (idCiclo, idCurso, orden) VALUES (" . $_REQUEST['idCiclo'] . ", " .  $_REQUEST['idCurso'] . ", " . $_REQUEST['orden'] . ")");
    include ('../../includes/database2.php');
}

?>