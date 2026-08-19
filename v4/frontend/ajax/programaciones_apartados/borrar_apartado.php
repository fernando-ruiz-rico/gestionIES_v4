<?php

// Elimina el apartado indicado de las programaciones, eliminando también las conexiones en los contenidos
// y contenidos por defecto.

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM contenidos_defecto_programaciones WHERE idApartado = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM contenidos_programaciones WHERE idApartado = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM apartados_programaciones WHERE id = "  . $_REQUEST['id']);
    include ('../../includes/database2.php');
}

?>

