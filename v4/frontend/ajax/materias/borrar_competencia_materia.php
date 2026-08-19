<?php

// Elimina la asociación de una competencia a una materia

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

$error = TRUE;

if ($permisos && !empty($_REQUEST['idMateria']) && !empty($_REQUEST['idCompetencia']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM competencias_materias WHERE idCompetencia = "  . $_REQUEST['idCompetencia'] . " AND idMateria = " . $_REQUEST['idMateria']);
    if (mysqli_affected_rows($db) > 0)
    {
        $error = FALSE;
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>