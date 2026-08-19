<?php

// Devuelve un listado HTML de grupos para su publicación en la vista principal de grupos,
// para el curso especificado

$error = FALSE;

if (!empty($_REQUEST['idCurso']))
{
    include('../../includes/database.php');
    $idCurso = $_REQUEST['idCurso'];
    $result = mysqli_query($db, "SELECT * FROM grupos WHERE idCurso = $idCurso ORDER BY orden");

    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        echo '<div class="listado claro izquierda grupo" id="gr' . $id . '">';
        echo '<div class="izquierda">'. $nombre . '</div>';
        // Botones para borrar o editar el grupo
        echo '<div class="derecha"><button class="btn btn-light" onclick="borrarGrupo(' . $id . ",'" . $nombre . "'" . ')"><img src="img/delete.png"></button><button class="btn btn-light" onclick="cargarGrupoModal(' . $id . ')"><img src="img/edit.png"></button></div>';
        echo '</div>';
    }

    mysqli_free_result($result);
    include ('../../includes/database2.php');
}

?>
