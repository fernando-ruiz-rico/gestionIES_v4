<?php

// Actualiza los datos de la asociación de un curso a un ciclo

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['idCiclo']) && !empty($_REQUEST['idCurso']) && !empty($_REQUEST['orden']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "UPDATE cursos_ciclos SET orden = " . $_REQUEST['orden'] . " WHERE idCiclo = " . $_REQUEST['idCiclo'] . " AND idCurso = " . $_REQUEST['idCurso']);
    include ('../../includes/database2.php');
}

?>