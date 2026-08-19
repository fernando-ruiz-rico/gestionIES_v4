<?php

// Devuelve un listado HTML de las unidades de competencia

include('../../includes/database.php');

$result = mysqli_query($db, "SELECT * FROM unidades_competencia ORDER BY codigo");

echo "<h1>Unidades de competencia</h1>";
echo '<div style="text-align: center"><button class="btn btn-light" onclick="nuevaUnidad()"><i class="bi bi-plus-circle"></i> Nueva Cualificación</button></div>';

while ($fila = mysqli_fetch_assoc($result))
{
    $codigo = $fila['codigo'];
    $texto = $fila['texto'];
    echo '<div class="listado claro izquierda">';
    echo '<div class="izquierda">'. $codigo . ' - ' . $texto . '</div>';
    // Botones para borrar y editar
    echo '<div class="derecha"><button class="btn btn-light" onclick="borrarUnidad(' . "'" . $codigo . "'" . ')"><i class="bi bi-trash"></i></button><button class="btn btn-light" onclick="cargarUnidadModal(' . "'" . $codigo . "'" . ')"><i class="bi bi-pencil-square"></i></button></div>';
    echo '</div>';
}

mysqli_free_result($result);

include ('../../includes/database2.php');

?>
