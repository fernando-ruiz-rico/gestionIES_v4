<?php

// Devuelve un listado HTML de competencias para su publicación en la vista principal de competencias por ciclo,

$error = FALSE;

if (!empty($_REQUEST['idCiclo']))
{
    include('../../includes/database.php');
    $idCiclo = $_REQUEST['idCiclo'];
    $result = mysqli_query($db, "SELECT * FROM competencias_ciclos WHERE idCiclo = $idCiclo ORDER BY orden");

    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $codigo = $fila['codigo'];
        $texto = $fila['texto'];
        // El class "competencia" y el id asignado es para reordenarlas con drag&drop
        echo '<div class="listado claro izquierda competencia" id="cm' . $id . '">';
        echo '<div class="izquierda">'. $codigo . '. ' . $texto . '</div>';
        // Botones para borrar o editar la competencia
        echo '<div class="derecha"><button class="btn btn-light" onclick="borrarCompetencia(' . $id . ",'" . $codigo . "'" . ')"><img src="img/delete.png"></button><button class="btn btn-light" onclick="cargarCompetenciaModal(' . $id . ')"><img src="img/edit.png"></button></div>';
        echo '</div>';
    }

    mysqli_free_result($result);
    include ('../../includes/database2.php');
}

?>
