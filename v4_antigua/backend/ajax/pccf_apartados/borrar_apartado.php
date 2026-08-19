<?php

// Elimina el apartado indicado de las PCCF, eliminando también las conexiones en los contenidos
// y contenidos por defecto.

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM contenidos_defecto_pccf WHERE idApartado = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM contenidos_pccf WHERE idApartado = "  . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM apartados_pccf WHERE id = "  . $_REQUEST['id']);
    include ('../../includes/database2.php');
}

?>

