<?php
/**
 * Carga el listado de departamentos en formato HTML
 */

include('../../includes/database.php');

$result = mysqli_query($db, "SELECT * FROM departamentos ORDER BY nombre");

while ($fila = mysqli_fetch_assoc($result))
{
    $id = $fila['id'];
    $nombre = $fila['nombre'];
    echo '<div class="listado claro d-flex justify-content-between align-items-center">';
    echo '<span>'. htmlspecialchars($nombre) . '</span>';
    echo '<div>';
    echo '<button class="btn btn-light btn-sm" onclick="borrarDepartamento(' . $id . ",\'" . htmlspecialchars($nombre, ENT_QUOTES) . "\')" title="Borrar"><i class="bi bi-trash"></i></button>";
    echo '<button class="btn btn-light btn-sm" onclick="cargarDepartamentoModal(' . $id . ')" title="Editar"><i class="bi bi-pencil"></i></button>';
    echo '</div>';
    echo '</div>';
}

mysqli_free_result($result);

include('../../includes/database2.php');

?>
