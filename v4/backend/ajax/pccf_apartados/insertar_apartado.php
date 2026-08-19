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
    $tipo = $_REQUEST['tipo'];

    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO apartados_pccf (titulo, subapartado, requerido, tipo, contenido_defecto) VALUES ('$titulo', $subapartado, $requerido, $tipo, $contenidoDefecto)");
    } else {
        mysqli_query($db, "UPDATE apartados_pccf SET titulo='$titulo', subapartado=$subapartado, requerido=$requerido, tipo=$tipo, contenido_defecto=$contenidoDefecto WHERE id = " . $_REQUEST['id']);
    }
    include ('../../includes/database2.php');
}

?>