<?php

// Este archivo recibe como parámetros un id de profesor y de departamento, y establece/quita a ese
// profesor como jefe de ese departamento.

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['idProfesor']) && !empty($_REQUEST['idDepartamento']))
{
    include ('../../includes/database.php');
    // Asignar/Quitar jefe de departamento
    mysqli_query($db, "UPDATE profesores SET jefe_departamento = 1 - jefe_departamento WHERE id = " . $_REQUEST['idProfesor']);
    include ('../../includes/database2.php');
}

?>