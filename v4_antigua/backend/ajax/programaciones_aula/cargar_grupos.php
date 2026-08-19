<?php

// Devuelve un JSON con los grupos que imparte un profesor para una materia en concreto

$resultado = array();

if (!empty($_REQUEST['idMateria']) && !empty($_REQUEST['idProfesor']))
{
    $idMateria = $_REQUEST['idMateria'];
    $idProfesor = $_REQUEST['idProfesor'];

    include('../../includes/database.php');

    $result = mysqli_query($db, "SELECT * FROM grupos WHERE id IN (SELECT seleccion.idGrupo FROM seleccion, escenarios_desideratas WHERE escenarios_desideratas.id = seleccion.idEscenario AND escenarios_desideratas.actual = TRUE AND idProfesor = $idProfesor AND idMateria = $idMateria) ORDER BY nombre");
    while($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        $resultado[] = array('id' => $id, 'nombre' => $nombre);
    }
    mysqli_free_result($result);

    include ('../../includes/database2.php');
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);

?>