<?php

// Devuelve el listado de materias del curso indicado, en formato HTML 
// para colocar en la vista principal

if (!empty($_REQUEST['idCurso']))
{
    include('../../includes/database.php');
    $idCurso = $_REQUEST['idCurso'];
    $result = mysqli_query($db, "SELECT * FROM materias WHERE idCurso = $idCurso ORDER BY nombre");
    $resultGrupos = mysqli_query($db, "SELECT * FROM grupos WHERE idCurso = $idCurso");
    $tieneGrupos = mysqli_num_rows($resultGrupos) > 0;
    mysqli_free_result($resultGrupos);

    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        echo '<div class="listado claro izquierda" id="gr' . $id . '">';
        echo '<div class="izquierda">'. $nombre . '</div>';
        echo '<div class="derecha">';
        echo'<button class="btn btn-light" title="Asociar competencias a materia" onclick="asociarCompetencias(' . $id . ')"><img src="img/capability.png"></button>';        
        // Botón para editar la información de la materia para cada grupo para borrar o editar la materia. Si tiene grupos, añadimos botón para editar información para cada grupo
        if($tieneGrupos)
            echo'<button class="btn btn-light" title="Gestionar datos específicos para cada grupo" onclick="cargarMateriasGrupos(' . $id . ',' . $idCurso . ')"><img src="img/conflicts.png"></button>';
        echo'<button class="btn btn-light" title="Borrar materia" onclick="borrarMateria(' . $id . ",'" . $nombre . "'" . ')"><img src="img/delete.png"></button><button class="btn btn-light" title="Editar datos de materia" onclick="cargarMateriaModal(' . $id . ')"><img src="img/edit.png"></button>';
        echo '</div>';
        echo '</div>';
    }

    mysqli_free_result($result);
    include ('../../includes/database2.php');
}

?>
