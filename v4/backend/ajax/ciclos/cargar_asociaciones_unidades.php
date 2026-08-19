<?php

// Carga los datos de las unidades asociadas a un ciclo

if (!empty($_REQUEST['idCiclo']))
{
    include('../../includes/database.php');

    // Primero recogemos los datos del ciclo para mostrarlos al inicio
    $result = mysqli_query($db, "SELECT * FROM ciclos WHERE id='" . $_REQUEST['idCiclo'] . "'");
    $resultado = mysqli_fetch_assoc($result);
    $nombreCiclo = $resultado['nombre'];
    mysqli_free_result($result);    

    echo "<h4>$nombreCiclo</h4>";

    // Ahora mostramos un listado con las unidades ya asociadas, junto a un botón para borrar cada una si se quiere
    $result = mysqli_query($db, "SELECT unidades_competencia.codigo AS codigo, unidades_competencia.texto AS texto FROM unidades_competencia, unidades_ciclos WHERE unidades_competencia.codigo = unidades_ciclos.codigoUnidad AND unidades_ciclos.idCiclo = " . $_REQUEST['idCiclo'] . " ORDER BY unidades_competencia.codigo");
    while ($fila = mysqli_fetch_assoc($result))
    {
        $codigo = $fila['codigo'];
        $texto = $fila['texto'];
        echo '<p><button class="btn btn-light" onclick="borrarAsociacion(' . $_REQUEST['idCiclo'] . ", '" . $codigo . "'" . ')"><i class="bi bi-trash"></i></button>';
        echo "$codigo - $texto</p>";
    }
    mysqli_free_result($result);

    // Finalmente mostramos un desplegable para añadir nuevas unidades al ciclo
    echo '<p>Asociar nuevas unidades</p>';
    $result = mysqli_query($db, "SELECT * FROM unidades_competencia ORDER BY codigo");
    echo '<select class="form-control" name="codigoAsociacion" id="codigoAsociacion">';
    echo '<option value="">--Selecciona una unidad --</option>';

    while ($fila = mysqli_fetch_assoc($result))
    {
        $codigo = $fila['codigo'];
        $texto = $fila['texto'];
        echo '<option value="' . $codigo . '">' . $codigo . " - " . $texto . '</option>';
    }

    mysqli_free_result($result);
    echo '</select>';
    echo '<div style="text-align:center"><button class="btn btn-light" onclick="nuevaAsociacion(' . $_REQUEST['idCiclo'] . ')">Añadir</button></div>';

    include ('../../includes/database2.php');
}

?>