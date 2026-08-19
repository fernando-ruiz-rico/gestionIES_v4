<?php

if (!empty($_REQUEST['idDepartamento']))
{
    include('includes/database.php');
    
    $result = mysqli_query($db, "SELECT cursos.nombre AS nomCurso, materias.nombre AS nomMateria, materias.id FROM cursos, materias WHERE cursos.id = materias.idCurso AND materias.tiene_programacion = 1 AND materias.idDepartamento = " . $_REQUEST['idDepartamento'] . " ORDER BY cursos.orden");
    echo "<ul>";
    while($fila = mysqli_fetch_assoc($result))
    {
        echo '<li><a href="pdf_programaciones.php?idMateria=' . $fila['id'] . '">' . $fila['nomCurso'] . ': ' . $fila['nomMateria'] . '</a></li>';
    }
    echo "</ul>";
    mysqli_free_result($result);
    
    include('includes/database2.php');
}

