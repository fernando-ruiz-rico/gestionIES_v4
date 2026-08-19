<?php

// Carga las competencias profesionales asociadas a una materia

if (!empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');

    // Primero recogemos los datos de la materia para mostrarlos al inicio
    $result = mysqli_query($db, "SELECT nombre FROM materias WHERE id= " . $_REQUEST['idMateria']);
    $resultado = mysqli_fetch_assoc($result);
    $nombreMateria = $resultado['nombre'];
    mysqli_free_result($result);    

    echo "<h4>$nombreMateria</h4>";

    // Ahora mostramos un listado con las competencias ya asociadas, junto a un botón para borrar cada una si se quiere
    $result = mysqli_query($db, "SELECT competencias_ciclos.* FROM competencias_ciclos, competencias_materias WHERE competencias_ciclos.id = competencias_materias.idCompetencia AND competencias_materias.idMateria = " . $_REQUEST['idMateria'] . " ORDER BY competencias_ciclos.orden");
    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $codigo = $fila['codigo'];
        $texto = $fila['texto'];
        echo '<p><button class="btn btn-light" onclick="borrarCompetencia(' . $_REQUEST['idMateria'] . ',' . $id .  ')"><i class="bi bi-trash"></i></button>';
        echo "$codigo - $texto</p>";
    }
    mysqli_free_result($result);

    // Finalmente mostramos un desplegable para añadir nuevas competencias a la materia
    echo '<p>Asociar nuevas competencias</p>';
    // Primero obtenemos a qué ciclo pertenece la materia
    $result = mysqli_query($db, "SELECT ciclos.id AS id FROM ciclos, cursos, cursos_ciclos, materias WHERE ciclos.id = cursos_ciclos.idCiclo AND cursos.id = cursos_ciclos.idCurso AND materias.idCurso = cursos.id AND materias.id = " . $_REQUEST['idMateria']);
    $fila = mysqli_fetch_assoc($result);
    $idCiclo = $fila['id'];
    mysqli_free_result($result);
    $result = mysqli_query($db, "SELECT * FROM competencias_ciclos WHERE idCiclo = $idCiclo ORDER BY codigo");
    echo '<select class="form-control" name="idCompetencia" id="idCompetencia">';
    echo '<option value="">--Selecciona una competencia --</option>';

    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $codigo = $fila['codigo'];
        $texto = $fila['texto'];
        echo '<option value="' . $id . '">' . $codigo . " - " . $texto . '</option>';
    }

    mysqli_free_result($result);
    echo '</select>';
    echo '<div style="text-align:center"><button class="btn btn-light" onclick="asociarCompetencia(' . $_REQUEST['idMateria'] . ')">Añadir</button></div>';

    include ('../../includes/database2.php');
}

?>