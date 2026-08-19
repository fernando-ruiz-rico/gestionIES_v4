<?php

// Elimina los datos del resultado de aprendizaje indicado

@session_start();

if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['id']))
{
    include ('../../includes/database.php');
    // Borramos también los CE asociados
    mysqli_query($db, "DELETE FROM criterios_evaluacion WHERE idRA = " . $_REQUEST['id']);
    mysqli_query($db, "DELETE FROM resultados_aprendizaje WHERE id = " . $_REQUEST['id']);
    include ('../../includes/database2.php');
}

?>