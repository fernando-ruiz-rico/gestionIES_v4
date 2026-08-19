<?php

// Propaga los datos de un campo de "evaluacion" al resto de temas de la materia

@session_start();

$error = TRUE;

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');
    $idMateria = $_REQUEST['idMateria'];
    $evaluacion = $_REQUEST['evaluacion'];

    mysqli_query($db, "UPDATE temas SET evaluacion = '$evaluacion' WHERE idMateria = $idMateria");
    if (mysqli_affected_rows($db) > 0)
    {
        $error = FALSE;
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>