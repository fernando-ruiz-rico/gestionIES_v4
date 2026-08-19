<?php

// Elimina la asociación de un curso a un ciclo

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['idCiclo']) && !empty($_REQUEST['idCurso']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM cursos_ciclos WHERE idCiclo = " . $_REQUEST['idCiclo'] . " AND idCurso = " . $_REQUEST['idCurso']);
    include ('../../includes/database2.php');
}

?>