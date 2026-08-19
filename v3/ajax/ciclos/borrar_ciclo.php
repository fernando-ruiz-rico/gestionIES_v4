<?php

// Elimina los datos del ciclo indicado
// No se puede borrar un ciclo si tiene cursos asociados
// Devuelve "si" si se ha producido algún error en el proceso

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

$error = TRUE;

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    $result = mysqli_query($db, "SELECT id FROM cursos WHERE idCiclo = " . $_REQUEST['id']);
    if (mysqli_num_rows($result) == 0)
    {
        // Borramos también los datos asociados al ciclo
        mysqli_query($db, "DELETE FROM unidades_ciclos WHERE idCiclo = "  . $_REQUEST['id']);
        mysqli_query($db, "DELETE FROM ciclos WHERE id = "  . $_REQUEST['id']);
        if (mysqli_affected_rows($db) > 0)
        {
            $error = FALSE;
        }
    }
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>