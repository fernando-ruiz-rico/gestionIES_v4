<?php

// Devuelve un JSON con los temas de la materia indicada

$resultado = array();

if (!empty($_REQUEST['idMateria']))
{
    $idMateria = $_REQUEST['idMateria'];

    include('../../includes/database.php');

    $result = mysqli_query($db, "SELECT * FROM temas WHERE idMateria = $idMateria ORDER BY orden");
    while($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $orden = $fila['orden'];
        $titulo = $fila['titulo'];
        $resultado[] = array('id' => $id, 'orden' => $orden, 'titulo' => $titulo);
    }
    mysqli_free_result($result);

    include ('../../includes/database2.php');
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);

?>