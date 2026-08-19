<?php

// Devuelve un listado HTML de los ciclos existentes

include('../../includes/database.php');

$result = mysqli_query($db, "SELECT * FROM ciclos ORDER BY nombre");

while ($fila = mysqli_fetch_assoc($result))
{
    $id = $fila['id'];
    $nombre = $fila['nombre'];
    echo '<div class="listado claro izquierda">';
    echo '<div class="izquierda">'. $nombre . '</div>';
    // Botones para borrar y editar el ciclo
    echo '<div class="derecha"><button title="Asociar cursos al ciclo" class="btn btn-light" onclick="asociarCursos(' . $id . ')"><img src="img/tree2.png"></button><button title="Asociar unidades de competencia" class="btn btn-light" onclick="asociarUnidades(' . $id . ')"><img src="img/qualification.png"></button><button class="btn btn-light" onclick="borrarCiclo(' . $id . ",'" . $nombre . "'" . ')"><img src="img/delete.png"></button><button class="btn btn-light" onclick="cargarCicloModal(' . $id . ')"><img src="img/edit.png"></button></div>';
    echo '</div>';
}

mysqli_free_result($result);

include ('../../includes/database2.php');

?>
