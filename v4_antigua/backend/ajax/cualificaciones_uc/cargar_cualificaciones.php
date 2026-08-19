<?php

// Devuelve un listado HTML de las cualificaciones profesionales

include('../../includes/database.php');

$result = mysqli_query($db, "SELECT * FROM cualificaciones_profesionales ORDER BY codigo");

echo "<h1>Cualificaciones profesionales</h1>";
echo '<div style="text-align: center"><button class="btn btn-light" onclick="nuevaCualificacion()"><i class="bi bi-plus-circle"></i> Nueva Cualificación</button></div>';

while ($fila = mysqli_fetch_assoc($result))
{
    $codigo = $fila['codigo'];
    $texto = $fila['texto'];
    echo '<div class="listado claro izquierda">';
    echo '<div class="izquierda">'. $codigo . ' - ' . $texto . '</div>';
    // Botones para borrar y editar
    echo '<div class="derecha"><button title="Asociar unidades de competencia" class="btn btn-light" onclick="asociarUnidades(' . "'" . $codigo . "'" . ')"><i class="bi bi-bezier2"></i></button><button class="btn btn-light" onclick="borrarCualificacion(' . "'" . $codigo . "'" . ')"><i class="bi bi-trash"></i></button><button class="btn btn-light" onclick="cargarCualificacionModal(' . "'" . $codigo . "'" . ')"><i class="bi bi-pencil-square"></i></button></div>';
    echo '</div>';
}

mysqli_free_result($result);

include ('../../includes/database2.php');

?>
