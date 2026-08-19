<?php

// Inserta o actualiza el ciclo que se recibe

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['nombre']))
{
    include('../../includes/database.php');
    $nombre = $_REQUEST['nombre'];
    $familia = $_REQUEST['familia'];
    $nivel = $_REQUEST['nivel'];

    // Si no llega id de curso es una inserción
    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO ciclos (nombre, familia, nivel) VALUES ('$nombre', '$familia', '$nivel')");    
    } else {
        mysqli_query($db, "UPDATE ciclos SET nombre='$nombre', familia='$familia', nivel='$nivel' WHERE id = " . $_REQUEST['id']);            
    }
    include ('../../includes/database2.php');
}

?>