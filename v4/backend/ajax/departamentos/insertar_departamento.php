<?php

// Esta página inserta el departamento que recibe en la petición, o lo actualiza si viene con un 
// "id" ya asignado.
// En principio no se controlan errores porque los datos son bastante simples

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['nombre']))
{
    include('../../includes/database.php');
    $nombre = $_REQUEST['nombre'];

    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO departamentos (nombre) VALUES ('$nombre')");    
    } else {
        mysqli_query($db, "UPDATE departamentos SET nombre='$nombre' WHERE id = " . $_REQUEST['id']);            
    }
    include ('../../includes/database2.php');
}

?>