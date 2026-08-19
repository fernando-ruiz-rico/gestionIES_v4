<?php

// Carga los datos de los cursos asociados a un ciclo

if (!empty($_REQUEST['idCiclo']))
{
    include('../../includes/database.php');

    // Primero recogemos los datos del ciclo para mostrarlos al inicio
    $result = mysqli_query($db, "SELECT * FROM ciclos WHERE id='" . $_REQUEST['idCiclo'] . "'");
    $resultado = mysqli_fetch_assoc($result);
    $nombreCiclo = $resultado['nombre'];
    mysqli_free_result($result);    

    echo "<h4>$nombreCiclo</h4>";

    // Ahora mostramos un listado con los cursos ya asociados, junto a un botón para borrar cada una si se quiere
    $result = mysqli_query($db, "SELECT cursos.id AS id, cursos.nombre AS nombre, cursos_ciclos.orden AS orden FROM cursos, cursos_ciclos WHERE cursos.id = cursos_ciclos.idCurso AND cursos_ciclos.idCiclo = " . $_REQUEST['idCiclo'] . " ORDER BY orden");
    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        $orden = $fila['orden'];
        echo '<input type="number" min="1" max="10" size="2" id="orden' . $id .'" name="orden' . $id . '" value="' . $orden . '">&nbsp;';
        echo '<button class="btn btn-light" onclick="borrarCurso(' . $_REQUEST['idCiclo'] . "," . $id . ')"><img src="img/delete.png"></button>';
        echo '<button class="btn btn-light" onclick="actualizarCurso(' . $_REQUEST['idCiclo'] . "," . $id . ')"><img src="img/edit.png"></button>&nbsp;';
        echo $nombre . '&nbsp;';
        echo '<br>';

    }
    mysqli_free_result($result);

    // Finalmente mostramos un desplegable para añadir nuevos cursos al ciclo
    echo '<p>Asociar nuevos cursos</p>';
    $result = mysqli_query($db, "SELECT * FROM cursos ORDER BY nombre");
    echo '<select class="form-control" name="codigoAsociacionCurso" id="codigoAsociacionCurso">';
    echo '<option value="">--Selecciona un curso --</option>';

    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        echo '<option value="' . $id . '">' . $nombre . '</option>';
    }

    mysqli_free_result($result);
    echo '</select><br>';
    echo 'Orden: <input type="number" min="1" max="10" size="2" id="orden" name="orden">';
    echo '<div style="text-align:center"><button class="btn btn-light" onclick="nuevoCurso(' . $_REQUEST['idCiclo'] . ')">Añadir</button></div>';

    include ('../../includes/database2.php');
}

?>