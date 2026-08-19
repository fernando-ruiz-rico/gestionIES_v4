<?php

// Actualiza las horas destinadas a la empresa de la materia indicada

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idMateria']) && isset($_REQUEST['horas']) && $_REQUEST['horas'] >= 0)
{
    include ('../../includes/database.php');
    mysqli_query($db, "UPDATE materias SET horas_empresa = " . $_REQUEST['horas'] . " WHERE id = " . $_REQUEST['idMateria']);
    include ('../../includes/database2.php');
}

?>