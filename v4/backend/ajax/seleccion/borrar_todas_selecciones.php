<?php

// Borramos todas las selecciones del escenario indicado

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['idEscenario']))
{
    include ('../../includes/database.php');
    mysqli_query($db, "DELETE FROM seleccion WHERE idEscenario = " . $_REQUEST['idEscenario']);
    include ('../../includes/database2.php');
}

?>