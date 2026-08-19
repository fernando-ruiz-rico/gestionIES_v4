<?php

// Inserta los datos de un nuevo criterio de evaluación

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idResultado']) && !empty($_REQUEST['nuevoCodigo']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "INSERT INTO criterios_evaluacion (idRA, codigo, texto) VALUES (" . $_REQUEST['idResultado'] . ", '" .  $_REQUEST['nuevoCodigo'] . "', '" . $_REQUEST['nuevoTexto'] . "')");
    include ('../../includes/database2.php');
}

?>