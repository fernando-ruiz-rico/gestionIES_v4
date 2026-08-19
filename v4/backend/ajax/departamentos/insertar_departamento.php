<?php
/**
 * Inserta o actualiza un departamento
 */

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['nombre']))
{
    include('../../includes/database.php');
    $nombre = mysqli_real_escape_string($db, $_REQUEST['nombre']);

    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO departamentos (nombre) VALUES ('$nombre')");    
    } else {
        $id = intval($_REQUEST['id']);
        mysqli_query($db, "UPDATE departamentos SET nombre='$nombre' WHERE id = $id");            
    }
    include('../../includes/database2.php');
}

?>
