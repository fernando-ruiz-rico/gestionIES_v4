<?php

// Inserta/Modifica el apartado recibido

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($permisos && !empty($_REQUEST['titulo']))
{
    include('../../includes/database.php');
    $titulo = $_REQUEST['titulo'];
    $subapartado = empty($_REQUEST['subapartado'])?0:1;
    $requerido = empty($_REQUEST['requerido'])?0:1;
    $contenidoDefecto = empty($_REQUEST['contenidoDefecto'])?0:1;
    $categoria = $_REQUEST['categoria'];
    $tipo = $_REQUEST['tipo'];

    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO apartados_programaciones (titulo, subapartado, requerido, contenido_defecto, categoria, tipo) VALUES ('$titulo', $subapartado, $requerido, $contenidoDefecto, '$categoria', $tipo)");    
    } else {
        mysqli_query($db, "UPDATE apartados_programaciones SET titulo='$titulo', subapartado=$subapartado, requerido=$requerido, contenido_defecto=$contenidoDefecto, categoria='$categoria', tipo=$tipo WHERE id = " . $_REQUEST['id']);
    }
    include ('../../includes/database2.php');
}

?>