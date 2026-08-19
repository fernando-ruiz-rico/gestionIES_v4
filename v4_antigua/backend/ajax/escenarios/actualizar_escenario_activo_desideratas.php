<?php

// Establece el escenario indicado como activo/inactivo para desideratas
// Un escenario activo permite elegir materias sobre él
// El "valorActual" es el estado actual del escenario: "no" si no está actualmente
// activo, y "si" si lo está. Lo que hace esta página es invertir ese valor actual

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['idEscenario']) && !empty($_REQUEST['valorActual']))
{
    include ('../../includes/database.php');
    // Si estaba activo deja de estarlo, y si no, al revés
    $actual = $_REQUEST['valorActual'] == "no"?1:0;
    mysqli_query($db, "UPDATE escenarios_desideratas SET activo_desideratas = $actual WHERE id = " . $_REQUEST['idEscenario']);
    include ('../../includes/database2.php');
}

?>