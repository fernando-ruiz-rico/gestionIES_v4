<?php

// Devuelve el contenido (texto) del acta cuyo id se especifica

if (!empty($_REQUEST['idActa']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT texto FROM actas_departamentos WHERE id=" . $_REQUEST['idActa']);
    $resultado = mysqli_fetch_assoc($result);
    echo $resultado['texto'];
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>