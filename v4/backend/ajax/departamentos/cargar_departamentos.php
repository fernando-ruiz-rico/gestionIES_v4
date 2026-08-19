<?php

// Esta página devuelve un listado de los departamentos actuales, en formato HTML
// para cargarse en la página "departamentos.php", dentro del "div" con id="listadepartamentos"
// Es invocada desde la función "cargarDepartamentos" de "js/departamentos.js"

include('../../includes/database.php');

$result = mysqli_query($db, "SELECT * FROM departamentos ORDER BY nombre");

while ($fila = mysqli_fetch_assoc($result))
{
    $id = $fila['id'];
    $nombre = $fila['nombre'];
    echo '<div class="listado claro izquierda">';
    echo '<div class="izquierda">'. $nombre . '</div>';
    // Enlaces para borrar o editar el departamento
    echo '<div class="derecha"><button class="btn btn-light" onclick="borrarDepartamento(' . $id . ",'" . $nombre . "'" . ')"><i class="bi bi-trash"></i></button><button class="btn btn-light" onclick="cargarDepartamentoModal(' . $id . ')"><i class="bi bi-pencil-square"></i></button></div>';
    echo '</div>';
}

mysqli_free_result($result);

include ('../../includes/database2.php');

?>
