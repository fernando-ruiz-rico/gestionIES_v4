<?php

// Carga los datos de las unidades asociadas a una cualificación profesional

if (!empty($_REQUEST['codigo']))
{
    include('../../includes/database.php');

    // Primero recogemos los datos de la cualificación para mostrarlos al inicio
    $result = mysqli_query($db, "SELECT * FROM cualificaciones_profesionales WHERE codigo='" . $_REQUEST['codigo'] . "'");
    $resultado = mysqli_fetch_assoc($result);
    $codigoCualificacion = $resultado['codigo'];
    $textoCualificacion = $resultado['texto'];
    mysqli_free_result($result);    

    echo "<h4>$codigoCualificacion - $textoCualificacion</h4>";

    // Ahora mostramos un listado con las unidades ya asociadas, junto a un botón para borrar cada una si se quiere
    $result = mysqli_query($db, "SELECT unidades_competencia.codigo AS codigo, unidades_competencia.texto AS texto FROM unidades_competencia, cualificaciones_unidades WHERE unidades_competencia.codigo = cualificaciones_unidades.codigoUnidad AND cualificaciones_unidades.codigoCualificacion = '" . $_REQUEST['codigo'] . "' ORDER BY unidades_competencia.codigo");
    while ($fila = mysqli_fetch_assoc($result))
    {
        $codigo = $fila['codigo'];
        $texto = $fila['texto'];
        echo '<p><button class="btn btn-light" onclick="borrarAsociacion(' . "'" . $codigoCualificacion . "', '" . $codigo . "'" . ')"><img src="img/delete.png"></button>';
        echo "$codigo - $texto</p>";
    }
    mysqli_free_result($result);

    // Finalmente mostramos un desplegable para añadir nuevas unidades a la cualificación
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
    echo '<div style="text-align:center"><button class="btn btn-light" onclick="nuevaAsociacion(' . "'" . $codigoCualificacion . "'" . ')">Añadir</button></div>';

    include ('../../includes/database2.php');
}

?>