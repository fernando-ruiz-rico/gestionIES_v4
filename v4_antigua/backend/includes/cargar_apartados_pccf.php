<?php

// Carga el listado de <option> con los apartados del PCCF disponibles

include('includes/database.php');

$result = mysqli_query($db, "SELECT * FROM apartados_pccf ORDER BY orden");
$cont = 0;
$cont2 = 0;

while($fila = mysqli_fetch_assoc($result))
{
    $id = $fila['id'];
    $titulo = $fila['titulo'];
    $subapartado = $fila['subapartado'];
    $tipo = $fila['tipo'];
    if (!$subapartado)
    {
        $cont++;
        $cont2 = 0;
        if($tipo == 0)
            echo '<option value="' . $id . '">' . "$cont. $titulo" . '</option>';
    } else {
        $cont2++;
        if($tipo == 0)
            echo '<option value="' . $id . '">' . "$cont.$cont2. $titulo" . '</option>';
    }
}

mysqli_free_result($result);

include('includes/database2.php');

?>