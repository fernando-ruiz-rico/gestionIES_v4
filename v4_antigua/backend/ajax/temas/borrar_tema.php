<?php

// Elimina un tema asociado a una materia

@session_start();

if (!empty($_SESSION['idUsuario'])  && !empty($_REQUEST['id']))
{
    include('../../includes/database.php');
    // Borramos también relaciones con otras tablas
    mysqli_query($db, "DELETE FROM competencias_temas WHERE idTema = " . $_REQUEST['id']);    
    mysqli_query($db, "DELETE FROM criterios_temas WHERE idTema = " . $_REQUEST['id']);    
    mysqli_query($db, "DELETE FROM programaciones_aula_temas WHERE idTema = " . $_REQUEST['id']);    

    mysqli_query($db, "DELETE FROM temas WHERE id = " . $_REQUEST['id']);    
    include ('../../includes/database2.php');
}

?>