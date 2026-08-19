<?php

// Devuelve un contenido HTML con los datos del escenario de desideratas elegido
// Muestra para cada profesor una tabla con la elección de materias

@session_start();

// Total de horas lectivas vigente, para cuadrar las guardias asignadas
define('TOTAL_HORAS_LECTIVAS', 18);

if (!empty($_REQUEST['idEscenario']))
{
    include('../../includes/database.php');

    // Precalculamos materias con sobredemanda o conflictos con varios profesores

    $listadoMateriasConflictos = array();

    // Recorremos cada curso
    $resultCurso = mysqli_query($db, "SELECT * FROM cursos");
    while($filaCurso = mysqli_fetch_assoc($resultCurso))
    {
        // Recorremos cada grupo de ese curso
        $resultGrupo = mysqli_query($db, "SELECT * FROM grupos WHERE idCurso = " . $filaCurso['id']);
        while($filaGrupo = mysqli_fetch_assoc($resultGrupo))
        {
            // Recorremos cada materia de ese grupo que tenga cantidad > 0
            $resultMateria = mysqli_query($db, "SELECT materias.id, materias.nombre, materias_grupos.idGrupo, materias_grupos.cantidad, materias_grupos.horas, materias_grupos.min_num_profesores, materias_grupos.max_grupos_profesor, materias.divisible FROM materias, materias_grupos WHERE materias.id = materias_grupos.idMateria AND materias_grupos.cantidad > 0 AND materias_grupos.idGrupo = " . $filaGrupo['id'] . " AND materias.idDepartamento = " . $_SESSION['departamentoUsuario']);
            while($filaMateria = mysqli_fetch_assoc($resultMateria))
            {
                // Comprobamos si esa materia la ha elegido alguien
                $resultAux = mysqli_query($db, "SELECT seleccion.*, profesores.id AS profId, profesores.nombre AS profNombre FROM seleccion, profesores WHERE seleccion.idProfesor = profesores.id AND idMateria = " . $filaMateria['id'] . " AND idGrupo = " . $filaGrupo['id']  . " AND seleccion.idEscenario=" . $_REQUEST['idEscenario']);
                $totalPeticiones = mysqli_num_rows($resultAux);
                if($totalPeticiones > 0)
                {
                    // Comprobamos si tiene demasiadas peticiones
                    $sumHoras = 0;
                    $idProfesores = array();
                    $nombresProfesores = array();
                    while($filaAux = mysqli_fetch_assoc($resultAux))
                    {
                        $idProfesores[] = $filaAux['profId'];
                        $nombresProfesores[] = $filaAux['profNombre'];
                        $sumHoras += $filaAux['horas'];
                    }
                    mysqli_free_result($resultAux);
                    // Si no es divisible y hay más peticiones que cantidad, hay conflicto
                    if(!$filaMateria['divisible'] && $totalPeticiones > $filaMateria['cantidad'])
                    {
                        $listadoMateriasConflictos[] = array('idMateria' => $filaMateria['id'], 'idGrupo' => $filaMateria['idGrupo']);
                    }
                    // Si la suma de horas de las peticiones supera las horas de la materia, hay conflicto
                    else if($sumHoras > $filaMateria['horas'] * $filaMateria['cantidad'])
                    {
                        $listadoMateriasConflictos[] = array('idMateria' => $filaMateria['id'], 'idGrupo' => $filaMateria['idGrupo']);
                    }
                }
            }
            mysqli_free_result($resultMateria);
        }
        mysqli_free_result($resultGrupo);
    }
    mysqli_free_result($resultCurso);


    echo '<p>Listado de profesores/as con sus selecciones. Se muestran en <span style="color:red">rojo</span> las materias que tienen conflictos con otros profesores/as.</p>';

    // Obtenemos todos los profesores que eligieron en ese escenario, ordenados por orden de asignación
    $result = mysqli_query($db, "SELECT id, nombre FROM profesores WHERE (idDepartamento = " . $_SESSION['departamentoUsuario'] . " AND activo = 1) OR id IN (SELECT idProfesor FROM seleccion WHERE idEscenario =" . $_REQUEST['idEscenario'] . ") ORDER BY orden"); // Quitamos la idEspecialidad del ORDER BY
    while ($fila = mysqli_fetch_assoc($result))
    {
        $total = 0;
        echo '<h4><strong>' . $fila['nombre'] . '</strong></h4>';
        // Obtenemos las materias que eligió en ese escenario el profesor
        $result2 = mysqli_query($db, "SELECT materias.nombre as nombre, materias.id as idMateria, materias.tipo, seleccion.horas as horas, cursos.abreviatura as abr, grupos.id as idGrupo, grupos.abreviatura AS abrGrupo, grupos.mostrar FROM seleccion, materias, cursos, grupos WHERE seleccion.idProfesor=" . $fila['id'] . " AND seleccion.idEscenario = " . $_REQUEST['idEscenario'] . " AND seleccion.idMateria = materias.id AND cursos.id = materias.idCurso AND cursos.id = grupos.idCurso AND seleccion.idGrupo = grupos.id ORDER BY seleccion.orden");
        echo '<blockquote>';
        echo '<table width="100%" border="1">';
        $contadorTutorias = 0;
        while ($fila2 = mysqli_fetch_assoc($result2))
        {
            $total += $fila2['horas'];
            $conflicto = FALSE;
            if($fila2['tipo'] == 'TUTORIA')
            {
                $contadorTutorias++;
                if($contadorTutorias > 1)
                {
                    $conflicto = TRUE;
                }
            }
            foreach ($listadoMateriasConflictos as $mc)
            {
                if($mc['idMateria'] == $fila2['idMateria'] && $mc['idGrupo'] == $fila2['idGrupo'])
                {
                    $conflicto = TRUE;
                }
            }
            echo '<tr><td width="50%">';
            if($conflicto)
                echo '<span style="color:red">';
            echo $fila2['nombre']; 
            if($conflicto)
                echo '</span>';
            echo '</td><td width="25%" align="center">' . $fila2['abr'] . ($fila2['mostrar']?$fila2['abrGrupo']:"") . '</td><td width="25%" align="center">' . $fila2['horas'] . ' h.</td></tr>';
        }
        if($total < TOTAL_HORAS_LECTIVAS)
        {
            // Sumamos una guardia lectiva si no llega al mínimo de horas lectivas
            /* COMENTADO DE MOMENTO
            $total += 1;
            echo '<tr><td width="50%">' . Guardias . '</td><td width="25%" align="center">OTROS</td><td width="25%" align="center">1 h.</td></tr>';
            */
        }
        echo '</table>';
        echo '<div align="right"><strong>' . $total . ' horas</strong></div>';
        echo '</blockquote>';
    }
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>