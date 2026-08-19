<?php

// Añade una asociación de una competencia a una materia

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['idCompetencia']) && !empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');
    mysqli_query($db, "INSERT INTO competencias_materias (idCompetencia, idMateria) VALUES (" . $_REQUEST['idCompetencia'] . ", " . $_REQUEST['idMateria'] . ")");
    include ('../../includes/database2.php');
}

?>