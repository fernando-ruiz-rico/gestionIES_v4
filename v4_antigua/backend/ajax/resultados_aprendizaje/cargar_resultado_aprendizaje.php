<?php

// Devuelve en formato JSON los datos del resultado de aprendizaje que se indica
if (!empty($_REQUEST['idResultado']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT * FROM resultados_aprendizaje WHERE id=" . $_REQUEST['idResultado']);
    $resultado = mysqli_fetch_assoc($result);
    // Codificamos el texto a UTF-8 porque los resultados pueden haberse volcado desde scripts sin esa codificación
    // $resultado['texto'] = utf8_encode($resultado['texto']);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($resultado);
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>