<?php

// Devuelve la fecha del acta cuyo id se especifica

if (!empty($_REQUEST['idActa']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT fecha FROM actas_departamentos WHERE id=" . $_REQUEST['idActa']);
    $resultado = mysqli_fetch_assoc($result);
    echo date('d/m/Y', strtotime($resultado['fecha']));
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>