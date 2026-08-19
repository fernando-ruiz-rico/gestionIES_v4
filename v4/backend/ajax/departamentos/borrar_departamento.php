<?php

// Esta página recibe como parámetro un "id" de departamento y lo borra de la base de datos si no 
// tiene profesores asignados. Devuelve "si" si ha habido algún error en el proceso

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';
$error = TRUE;

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM profesores WHERE idDepartamento = " . $_REQUEST['id']);
    if (mysqli_num_rows($result) == 0)
    {
        // Borramos dependencias con otras tablas
        mysqli_query($db, "DELETE FROM especialidades WHERE idDepartamento = "  . $_REQUEST['id']);
        mysqli_query($db, "DELETE FROM actas_departamentos WHERE idDepartamento = "  . $_REQUEST['id']);
        mysqli_query($db, "UPDATE materias SET idDepartamento = NULL WHERE idDepartamento = " . $_REQUEST['id']);
        // Borramos el departamento
        mysqli_query($db, "DELETE FROM departamentos WHERE id = "  . $_REQUEST['id']);
        if (mysqli_affected_rows($db) > 0)
        {
            $error = FALSE;
        }
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>