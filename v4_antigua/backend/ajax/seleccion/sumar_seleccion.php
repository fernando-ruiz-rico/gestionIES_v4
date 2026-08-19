<?php

// Suma el total de horas lectivas semanales de la selección del profesor para el escenario indicado

if(!empty($_REQUEST['idProfesor']) && !empty($_REQUEST['idEscenario']))
{
    include('../../includes/database.php');

    $idProfesor = $_REQUEST['idProfesor'];
    $idEscenario = $_REQUEST['idEscenario'];
    $resultado = mysqli_query($db, "SELECT SUM(seleccion.horas) as total FROM seleccion, materias, cursos, grupos WHERE seleccion.idProfesor=$idProfesor AND seleccion.idEscenario=$idEscenario AND seleccion.idMateria = materias.id AND cursos.id = materias.idCurso AND cursos.id = grupos.idCurso AND seleccion.idGrupo = grupos.id");
    $fila = mysqli_fetch_assoc($resultado);
    if ($fila['total'])
        $total = $fila['total'];
    else 
        $total = 0;
    echo "Total: $total h";
    
    mysqli_free_result($resultado);

    include ('../../includes/database2.php');
    
}

?>