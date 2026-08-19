<?php

    // Página con estadísticas de la selección de materias para un escenario concreto

    @session_start();
    include('includes/database.php');

    // Horas por profesor para el cálculo de horas
    define ('HORAS_PROFESOR', 18);

    
    // Marca en negrita las ocurrencias en profesores del que hay logueado
    function marcaProfesor($idProfesores, $nombresProfesores)
    {
        $result = array();
        for($i = 0; $i < count($idProfesores); $i++)
        {
            if (!empty($_SESSION['idUsuario']) && $_SESSION['idUsuario'] == $idProfesores[$i])
                $result[] = '<strong>' . $nombresProfesores[$i] . '</strong>';
            else
                $result[] = $nombresProfesores[$i];
        }
        return $result;
    }

    // Devuelve cuántas veces aparece el id de un profesor en un array de ids
    function contarVeces($idProf, $idProfesores)
    {
        $result = 0;
        foreach($idProfesores as $id)
        {
            if($id == $idProf)
                $result++;
        }
        return $result;
    }

    // Obtiene el nombre de un profesor dado su id, buscando en dos arrays paralelos de ids y nombres
    function obtenerNombre($idProf, $idProfesores, $nombresProfesores)
    {
        $nombre = "";
        for($i = 0; $i < count($idProfesores); $i++)
        {
            if($idProfesores[$i] == $idProf)
                $nombre = $nombresProfesores[$i];
        }
        return $nombre;
    }
?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Estadísticas - Selección de asignaturas</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="css/estilos.css?v=3" />
    </head>
    <body>

    <div class="panelcentral">
        
        <h1>Estadísticas</h1>

        <div class="row">
            
            <!-- HORAS POR ESPECIALIDAD (TUTORIAS, ETC) -->
                
            <div class="panelst col-md-4" style="margin-top:10px;padding:10px">

                <h2 class="stats">Horas por especialidades</h2>
            
                <?php

                    // Primero obtenemos las horas estipuladas para las especialidades del departamento

                    $resultDepto = mysqli_query($db, "SELECT * FROM especialidades WHERE idDepartamento = " . $_SESSION['departamentoUsuario']);
                    $horas = array();
                    while ($fila = mysqli_fetch_assoc($resultDepto))
                    {
                        // Se cargan también las horas asignadas a inglés, aunque actualmente no se contabilizan en las estadísticas
                        // por estar asociadas a módulos concretos directamente
                        $horas[$fila['id']] = array('descripcion' => $fila['descripcion'], 'tutoria' => $fila['horasTutoria'], 'ingles' => $fila['horasIngles'], 'profesores' => $fila['profesores']);
                    }
                    mysqli_free_result($resultDepto);

                    // Recorremos las especialidades y sacamos las estadísticas
                    foreach($horas as $esp => $valores)
                    {
                        $idEsp = $esp;
                        $nomEsp = $valores['descripcion'];
                        $tutoria = $valores['tutoria'];
                        $profesores = $valores['profesores'];

                        echo '<div class="panelstats">';
                        echo "<h3>$nomEsp</h3>";
                        echo '<div>';

                        // Horas totales impartidas por la especialidad
                        $resultAux = mysqli_query($db, "SELECT SUM(seleccion.horas) AS suma FROM profesores, seleccion WHERE profesores.id = seleccion.idProfesor AND profesores.idEspecialidad='$idEsp' AND seleccion.idEscenario=" . $_REQUEST['idEscenario'] . " AND profesores.idDepartamento=" . $_SESSION['departamentoUsuario']);
                        $fila = mysqli_fetch_assoc($resultAux);
                        mysqli_free_result($resultAux);
                        echo '<p><strong>Horas totales impartidas: </strong>' . (empty($fila['suma'])?0:$fila['suma']) . ' / ' . ($profesores * HORAS_PROFESOR) . '</p>';

                        /* COMENTADO DE MOMENTO

                        // Horas de tutoria
                        $resultAux = mysqli_query($db, "SELECT SUM(seleccion.horas) FROM profesores, materias, seleccion WHERE materias.id = seleccion.idMateria AND profesores.id = seleccion.idProfesor AND profesores.idEspecialidad='$idEsp' AND materias.tipo = 'TUTORIA' AND seleccion.idEscenario=" . $_REQUEST['idEscenario'] . " AND profesores.idDepartamento=" . $_SESSION['departamentoUsuario']);
                        $fila = mysqli_fetch_row($resultAux);
                        mysqli_free_result($resultAux);
                        $tutoriasReales = empty($fila[0])?0:$fila[0];
                        $estilo = 'badge bg-success';
                        if ($tutoriasReales != $tutoria)
                            $estilo = 'badge bg-danger';
                        echo '<p><strong>Tutorías:</strong> <span class="' . $estilo . '">' . $tutoriasReales . ' / ' . $tutoria . '</span></p>';
                        
                        // Horas asumidas (que corresponden a otra especialidad)
                        $resultAux = mysqli_query($db, "SELECT SUM(seleccion.horas) FROM profesores, materias, seleccion WHERE materias.id = seleccion.idMateria AND profesores.id = seleccion.idProfesor AND profesores.idEspecialidad='$idEsp' AND materias.idEspecialidad IS NOT NULL AND materias.idEspecialidad <> '$idEsp' AND seleccion.idEscenario=" . $_REQUEST['idEscenario'] . " AND profesores.idDepartamento=" . $_SESSION['departamentoUsuario']);
                        $fila = mysqli_fetch_row($resultAux);
                        mysqli_free_result($resultAux);
                        echo '<p><strong>Horas asumidas:</strong> ' . (empty($fila[0])?0:$fila[0]) . '</p>';

                        // Horas cedidas (de esta especialidad que han tomado profesores de otras especialidades)
                        $resultAux = mysqli_query($db, "SELECT SUM(seleccion.horas) FROM profesores, materias, seleccion WHERE materias.id = seleccion.idMateria AND profesores.id = seleccion.idProfesor AND profesores.idEspecialidad<>'$idEsp' AND materias.idEspecialidad IS NOT NULL AND materias.idEspecialidad = '$idEsp' AND seleccion.idEscenario=" . $_REQUEST['idEscenario'] . " AND profesores.idDepartamento=" . $_SESSION['departamentoUsuario']);
                        $fila = mysqli_fetch_row($resultAux);
                        mysqli_free_result($resultAux);
                        echo '<p><strong>Horas cedidas:</strong> ' . (empty($fila[0])?0:$fila[0]) . '</p>';

                        */

                        echo '</div>';
                        echo '</div>';
                    }
                ?>
            </div>

            <!-- CONFLICTOS: ASIGNATURAS NO ESCOGIDAS, ASIGNATURAS CON MÁS DE UN PROFESOR, ETC. -->
                
            <div class="panelst col-md-8" style="margin-top:10px;padding:10px">
                
                <h2>Conflictos</h2>
                            
                <?php

                // Calculamos y rellenamos los arrays: materias no escogidas, materias con sobredemanda, restricciones de mínimo número de profesores...
            
                $listadoMateriasNoEscogidas = array();
                $listadoMateriasConflictos = array();
                $listadoRestricciones = array();  // Puede que no haga falta
                $tienesConflictos = FALSE;

                // Recorremos cada curso
                $resultCurso = mysqli_query($db, "SELECT * FROM cursos");
                while($filaCurso = mysqli_fetch_assoc($resultCurso))
                {
                    // Recorremos cada grupo de ese curso
                    $resultGrupo = mysqli_query($db, "SELECT * FROM grupos WHERE idCurso = " . $filaCurso['id']);
                    while($filaGrupo = mysqli_fetch_assoc($resultGrupo))
                    {
                        // Recorremos cada materia de ese grupo que tenga cantidad > 0
                        $resultMateria = mysqli_query($db, "SELECT materias.id, materias.nombre, materias.idEspecialidad, materias_grupos.cantidad, materias_grupos.horas, materias_grupos.min_num_profesores, materias_grupos.max_grupos_profesor, materias.divisible FROM materias, materias_grupos WHERE materias.id = materias_grupos.idMateria AND materias_grupos.cantidad > 0 AND materias_grupos.idGrupo = " . $filaGrupo['id'] . " AND materias.idDepartamento = " . $_SESSION['departamentoUsuario']);
                        while($filaMateria = mysqli_fetch_assoc($resultMateria))
                        {
                            $especialidad = isset($filaMateria['idEspecialidad'])?$filaMateria['idEspecialidad']:'';

                            // String auxiliar con el nombre del curso y la materia, para los listados de conflictos
                            $datosMateria = $filaMateria['nombre'] . " (" . $filaCurso['nombre'] . " " . $filaGrupo['nombre'] . ")";
                            $arrayMateria = array("nombre" => $filaMateria['nombre'], "curso" => $filaCurso['nombre'], "grupo" => $filaGrupo['nombre'], "horas" => $filaMateria['horas'], "especialidad" => $especialidad);

                            // Comprobamos si esa materia la ha elegido alguien
                            $resultAux = mysqli_query($db, "SELECT seleccion.*, profesores.id AS profId, profesores.nombre AS profNombre FROM seleccion, profesores WHERE seleccion.idProfesor = profesores.id AND idMateria = " . $filaMateria['id'] . " AND idGrupo = " . $filaGrupo['id']  . " AND seleccion.idEscenario=" . $_REQUEST['idEscenario']);
                            $totalPeticiones = mysqli_num_rows($resultAux);
                            if($totalPeticiones == 0)
                            {
                                $listadoMateriasNoEscogidas[] = $arrayMateria;
                            }
                            else
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
                                    $listadoMateriasConflictos[] = $datosMateria . " no es divisible y tiene más peticiones de las permitidas";
                                    if (!empty($_SESSION['idUsuario']) && in_array($_SESSION['idUsuario'], $idProfesores))
                                    {
                                        $tienesConflictos = TRUE;
                                    }
                                }
                                // Si la suma de horas de las peticiones supera las horas de la materia, hay conflicto
                                else if($sumHoras > $filaMateria['horas'] * $filaMateria['cantidad'])
                                {
                                    $listadoMateriasConflictos[] = $datosMateria . " tiene demasiadas peticiones ($totalPeticiones): " . implode(', ', marcaProfesor($idProfesores, $nombresProfesores));
                                    if (!empty($_SESSION['idUsuario']) && in_array($_SESSION['idUsuario'], $idProfesores))
                                    {
                                        $tienesConflictos = TRUE;
                                    }
                                }
                                // Si no llega al número de horas, también hay un conflicto
                                else if($sumHoras < $filaMateria['horas'] * $filaMateria['cantidad'])
                                {
                                    $listadoMateriasNoEscogidas[] = $arrayMateria;
                                    $listadoMateriasConflictos[] = $datosMateria . " tiene pocas peticiones ($totalPeticiones)";
                                }
                                // Vemos ahora si hay menos profesores que el número mínimo requerido
                                else if($filaMateria['min_num_profesores'] > 0 && count(array_unique($idProfesores)) < $filaMateria['min_num_profesores'])
                                {
                                    $listadoMateriasNoEscogidas[] = $arrayMateria;
                                    $listadoMateriasConflictos[] = $datosMateria . " requiere más profesores para impartirse";
                                }
                                // Vemos si algún profesor ha cogido más grupos de los permitidos
                                else if($filaMateria['max_grupos_profesor'] > 0)
                                {
                                    foreach(array_unique($idProfesores) as $idProf)
                                    {
                                        if(contarVeces($idProf, $idProfesores) > $filaMateria['max_grupos_profesor'])
                                        {
                                            $nombreProfesor = obtenerNombre($idProf, $idProfesores, $nombresProfesores);
                                            if (!empty($_SESSION['idUsuario']) && $_SESSION['idUsuario'] == $idProf)
                                            {
                                                $listadoMateriasConflictos[] = $datosMateria . ' ha sido elegida demasiadas veces por <strong>' . $nombreProfesor . '</strong>';
                                                $tienesConflictos = TRUE;
                                            }
                                            else
                                                $listadoMateriasConflictos[] = $datosMateria . ' ha sido elegida demasiadas veces por ' . $nombreProfesor ;
                                        }
                                    }
                                }
                            }
                        }
                        mysqli_free_result($resultMateria);
                    }
                    mysqli_free_result($resultGrupo);
                }
                mysqli_free_result($resultCurso);
                
                // Obtenemos profesores que tengan más de una tutoría
                $resultado = mysqli_query($db, "SELECT p.id, p.nombre FROM seleccion s, materias m, profesores p, grupos g WHERE s.idMateria = m.id AND s.idProfesor = p.id AND s.idGrupo = g.id AND m.tipo = 'TUTORIA' AND s.idEscenario = " . $_REQUEST['idEscenario'] . " GROUP BY p.id, p.nombre HAVING COUNT(DISTINCT s.idMateria, s.idGrupo) >= 2");
                $profTutorias = array();
                while($fila = mysqli_fetch_assoc($resultado))
                {
                    if($fila['id'] == $_SESSION['idUsuario'])
                    {
                        $tienesConflictos = TRUE;
                    }
                    $profTutorias[] = array("nombre" => $fila['nombre'], "id" => $fila['id']);
                }
                mysqli_free_result($resultado);

                if (!empty($_SESSION['nombreUsuario']) && $_SESSION['rol'] != 'admin')
                {
                    if ($tienesConflictos)
                        echo '<div class="alert alert-danger">Tienes conflictos. Revisa la sección de "Materias con conflictos" para más información</div>';
                    else
                        echo '<div class="alert alert-success">No tienes conflictos</div>';
                }

                ?>
                            
                <h3>Materias sin escoger</h3>
                
                <?php
                    if (count($listadoMateriasNoEscogidas) == 0)
                        echo '<p>No hay materias sin escoger.</p>';
                    else
                    {
                        // Ordenamos array por especialidad (asc) y horas (desc)

                        usort($listadoMateriasNoEscogidas, function($a, $b) {
                            $cmp = strcmp($a['especialidad'], $b['especialidad']);
                            if ($cmp === 0) {
                                return strcmp($a['curso'] . ' ' . $a['grupo'], $b['curso'] . ' ' . $b['grupo']);
                            }
                            return $cmp;
                        });

                        echo '<ul>';
                        foreach($listadoMateriasNoEscogidas as $materia)
                        {
                            $especialidad = $materia['especialidad'];
                            if ($materia['especialidad'] == '')
                                $especialidad = 'Todos';
                            echo "<li>[" . $especialidad . "] " .  $materia['nombre'] . " (" . $materia['curso'] . " " . $materia['grupo'] . ", " . $materia['horas'] . "h)";
                        }
                        echo '</ul>';
                    }
                ?>
                
                <h3>Materias con conflictos</h3>
                
                <?php

                    // Conclictos por pedir demasiadas tutorías
                    echo "<ul>";
                    foreach($profTutorias as $prof)
                    {
                        if($prof['id'] == $_SESSION['idUsuario'])
                            echo '<li><strong>' . $prof['nombre'] . "</strong> ha escogido más de una tutoría.</li>";
                        else
                            echo '<li>' . $prof['nombre'] . " ha escogido más de una tutoría.</li>";
                    }
                    echo "</ul>";

                    // Resto de conflictos

                    if (count($listadoMateriasConflictos) == 0)
                        echo '<p>No hay materias con conflictos.</p>';
                    else
                    {
                        echo '<ul>';
                        foreach($listadoMateriasConflictos as $materia)
                        {
                            echo "<li>$materia</li>";
                        }
                        echo '</ul>';
                    }
                ?>
                
            </div>
        </div>
    </div>

<?php
    include('includes/database2.php');
?>        
    </body>
</html>
