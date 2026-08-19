<?php

// Muestra un listado HTML de los profesores del departamento seleccionado para el escenario seleccionado

@session_start();

if (isset($_SESSION['departamentoUsuario']) && !empty($_REQUEST['idEscenario']))
{
    $idEscenario = $_REQUEST['idEscenario'];
    
    include('../../includes/database.php');
    // Constantes para definir el rango de horas lectivas que un profesor puede seleccionar como razonable
    define('MIN_HORAS', 17);
    define('MAX_HORAS', 22);

    if (empty($_REQUEST['idEspecialidad']) || $_REQUEST['idEspecialidad'] == 'Todos')
        $resultado = mysqli_query($db, "SELECT * FROM profesores WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . " AND activo=1 ORDER BY orden");
    else
        $resultado = mysqli_query($db, "SELECT * FROM profesores WHERE idDepartamento = " . $_SESSION['departamentoUsuario'] . " AND activo=1 AND idEspecialidad='" . $_REQUEST['idEspecialidad'] . "' ORDER BY orden");

    while ($fila = mysqli_fetch_assoc($resultado))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        $especialidad = $fila['idEspecialidad'];
        // Calculamos las horas elegidas del profesor
        $resultado2 = mysqli_query($db, "SELECT seleccion.horas FROM materias, seleccion, cursos, grupos WHERE materias.id = seleccion.idMateria AND seleccion.idProfesor = $id AND seleccion.idEscenario = $idEscenario AND cursos.id = materias.idCurso AND cursos.id = grupos.idCurso AND seleccion.idGrupo = grupos.id");
        $totalHoras = 0;
        while ($fila2 = mysqli_fetch_assoc($resultado2))
        {
            $totalHoras += $fila2['horas'];
        }
        mysqli_free_result($resultado2);
        // Mostraremos un "badge" de un color u otro según las horas que ha elegido
        // Verde si está en el rango, amarillo si no llega y rojo si se pasa
        if ($totalHoras < MIN_HORAS)
            $clase = 'badge bg-warning';
        else if ($totalHoras > MAX_HORAS)
            $clase = 'badge bg-danger';
        else
            $clase = 'badge bg-success';
        $datos = '<label class="' . $clase . '">' . $totalHoras . ' h</label>'; 

        echo '<div class="profesor izquierda claro" id="prof' . $id . '" onclick="seleccionarProfesor(' . $id . ",'$especialidad'" . ')"><div class="izquierda">' . $nombre . '</div><div class="derecha">' . $datos . '</div></div>';
    }

    mysqli_free_result($resultado);

    include ('../../includes/database2.php');
}

?>