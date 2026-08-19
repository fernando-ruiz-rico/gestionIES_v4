<?php

// Devuelve un listado HTML con los apartados actuales

include('../../includes/database.php');

$error = FALSE;

$result = mysqli_query($db, "SELECT * FROM apartados_programaciones ORDER BY orden");
// Indices para contar el número de sección o subsección
$cont = 0;
$cont2 = 0;

while ($fila = mysqli_fetch_assoc($result))
{
    $id = $fila['id'];
    $titulo = $fila['titulo'];
    $subapartado = $fila['subapartado'];
    $requerido = $fila['requerido'];
    // Sección principal
    if (!$subapartado)
    {
        $cont++;
        $cont2 = 0;
        echo '<div class="listado claro izquierda apartado" id="ap' . $id . '">';
        echo '<div class="izquierda">'. "$cont. $titulo" . ($requerido?"":" (opcional)") . '</div>';
        echo '<div class="derecha"><button class="btn btn-light" onclick="borrarApartado(' . $id . ",'" . $titulo . "'" . ')"><i class="bi bi-trash"></i></button><button class="btn btn-light" onclick="cargarApartadoModal(' . $id . ')"><i class="bi bi-pencil-square"></i></button></div>';
        echo '</div>';
    // Subapartado de sección principal
    } else {
        $cont2++;
        echo '<div class="listado claro izquierda apartado" id="ap' . $id . '">';
        echo '<div class="izquierda">'. "$cont.$cont2. $titulo" . ($requerido?"":" (opcional)") . '</div>';
        echo '<div class="derecha"><button class="btn btn-light" onclick="borrarApartado(' . $id . ",'" . $titulo . "'" . ')"><i class="bi bi-trash"></i></button><button class="btn btn-light" onclick="cargarApartadoModal(' . $id . ')"><i class="bi bi-pencil-square"></i></button></div>';
        echo '</div>';
    }
}

mysqli_free_result($result);

include ('../../includes/database2.php');

?>
