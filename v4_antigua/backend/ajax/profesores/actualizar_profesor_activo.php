<?php

// Este archivo activa/desactiva al profesor especificado.
// La idea es que los profesores inactivos no puedan acceder al sistema pero permanezan en el histórico
// para futuras operaciones o una posible vuelta

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['idProfesor']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "UPDATE profesores SET activo = !activo WHERE id = " . $_REQUEST['idProfesor']);
    include ('../../includes/database2.php');
}

?>