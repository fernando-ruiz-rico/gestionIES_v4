<?php

// Devuelve un listado HTML de los cursos existentes

include('../../includes/database.php');

$result = mysqli_query($db, "SELECT * FROM cursos ORDER BY orden");

while ($fila = mysqli_fetch_assoc($result))
{
    $id = $fila['id'];
    $nombre = $fila['nombre'];
    // El class="curso" permite ordenarlos por drag&drop. El id "cuXX" permite enviar ese orden al servidor
    echo '<div class="listado claro izquierda curso" id="cu' . $id . '">';
    echo '<div class="izquierda">'. $nombre . '</div>';
    // Botones para borrar y editar el curso
    echo '<div class="derecha"><button class="btn btn-light" onclick="borrarCurso(' . $id . ",'" . $nombre . "'" . ')"><img src="img/delete.png"></button><button class="btn btn-light" onclick="cargarCursoModal(' . $id . ')"><img src="img/edit.png"></button></div>';
    echo '</div>';
}

mysqli_free_result($result);

include ('../../includes/database2.php');

?>
