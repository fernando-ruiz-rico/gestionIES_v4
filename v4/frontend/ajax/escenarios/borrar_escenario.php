<?php

// Elimina el escenario indicado de la base de datos, junto con las selecciones
// de materias vinculadas a él.
// Se devuelve "si" si ha habido algún error en el proceso de borrado

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
$error = TRUE;

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM escenarios_desideratas WHERE id = "  . $_REQUEST['id']);
    if (mysqli_affected_rows($db) > 0)
        $error = FALSE;
    // Borramos también las elecciones de materias que pudiera tener
    mysqli_query($db, "DELETE FROM seleccion WHERE idEscenario = "  . $_REQUEST['id']);
    include ('../../includes/database2.php');
}

echo $error?'si':'no';

?>