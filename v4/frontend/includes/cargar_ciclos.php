<?php

// Carga el listado de <option> con los ciclos formativos disponibles

include('includes/database.php');

$result = mysqli_query($db, "SELECT * FROM ciclos ORDER BY nombre");
while($fila = mysqli_fetch_assoc($result))
{
    $id = $fila['id'];
    $nombre = $fila['nombre'];
    echo '<option value="' . $id . '">' . $nombre . '</option>';
}

mysqli_free_result($result);
include('includes/database2.php');

?>