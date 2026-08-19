<?php

// Devuelve los criterios de evaluación asociados al resultado de aprendizaje indicado
// Carga para cada uno un mini-formulario para poderlo editar/borrar, y luego otro vacío
// para añadir más
if (!empty($_REQUEST['idResultado']))
{
    $id = $_REQUEST['idResultado'];
    include('../../includes/database.php');

    $result = mysqli_query($db, "SELECT * FROM criterios_evaluacion WHERE idRA = $id ORDER BY codigo");
    while ($fila = mysqli_fetch_assoc($result))
    {
        $codigo = $fila['codigo'];
        $texto = $fila['texto'];

        echo '<input type="text" maxlength="2" size="2" id="cce' . $codigo .'" name="cce' . $codigo . '" value="' . $codigo . '">&nbsp;';
        echo '<input type="text" size="60" id="tce' . $codigo .'" name="tce' . $codigo . '" value="' . $texto . '">&nbsp;';
        echo '<button class="btn btn-light" onclick="borrarCriterio(' . $id . ",'" . $codigo . "'" . ')"><i class="bi bi-trash"></i></button>';
        echo '<button class="btn btn-light" onclick="actualizarCriterio(' . $id . ",'" . $codigo . "'" . ')"><i class="bi bi-pencil-square"></i></button>';
        echo '<br>';
    }

    echo '<p>Añadir nuevo criterio (código y texto):</p>';
    echo '<input type="text" maxlength="2" size="2" id="codigoCE" name="codigoCE">&nbsp;';
    echo '<input type="text" size="60" id="textoCE" name="textoCE">&nbsp;';
    echo '<button class="btn btn-light" onclick="insertarCriterio(' . $id . ')">Añadir</button>';
    echo '<br>';

    include ('../../includes/database2.php');
}

?>