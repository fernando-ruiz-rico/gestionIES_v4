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
    echo '<div class="derecha"><button title="Asociar cursos al ciclo" class="btn btn-light" onclick="asociarCursos(' . $id . ')"><i class="bi bi-bezier2"></i></button><button title="Asociar unidades de competencia" class="btn btn-light" onclick="asociarUnidades(' . $id . ')"><i class="bi bi-patch-check"></i></button><button class="btn btn-light" onclick="borrarCiclo(' . $id . ",'" . $nombre . "'" . ')"><i class="bi bi-trash"></i></button><button class="btn btn-light" onclick="cargarCicloModal(' . $id . ')"><i class="bi bi-pencil-square"></i></button></div>';
    echo '</div>';
}

mysqli_free_result($result);

include ('../../includes/database2.php');

?>
