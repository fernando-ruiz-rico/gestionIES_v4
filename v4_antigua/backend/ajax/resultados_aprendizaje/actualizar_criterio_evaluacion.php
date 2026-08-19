<?php

// Actualiza los datos del criterio de evaluación indicado

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idResultado']) && !empty($_REQUEST['codigo']) && !empty($_REQUEST['nuevoCodigo']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "UPDATE criterios_evaluacion SET codigo = '" . $_REQUEST['nuevoCodigo'] . "', texto = '" . $_REQUEST['nuevoTexto'] . "' WHERE idRA = " . $_REQUEST['idResultado'] . " AND codigo = '" . $_REQUEST['codigo'] . "'");
    include ('../../includes/database2.php');
}

?>