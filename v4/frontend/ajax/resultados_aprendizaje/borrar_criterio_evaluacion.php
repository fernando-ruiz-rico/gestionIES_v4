<?php

// Elimina el criterio de evaluación indicado

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idResultado']) && !empty($_REQUEST['codigo']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM criterios_evaluacion WHERE idRA = " . $_REQUEST['idResultado'] . " AND codigo = '" . $_REQUEST['codigo'] . "'");
    include ('../../includes/database2.php');
}

?>