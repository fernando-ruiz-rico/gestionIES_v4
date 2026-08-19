<?php

session_start();

// Guardamos si el usuario tiene permisos superiores (jefe de departamento o admin)
$super = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if(!empty($_REQUEST['idProfesor']) && !empty($_REQUEST['idEscenario']))
{
    include('../../includes/database.php');

    $idProfesor = $_REQUEST['idProfesor'];
    $idEscenario = $_REQUEST['idEscenario'];
    $resultado = mysqli_query($db, "SELECT seleccion.id as id, seleccion.orden as orden, materias.nombre as nombre, seleccion.horas as horas, materias.asignada_directiva as asignada_directiva, cursos.abreviatura as abr, grupos.abreviatura AS abrGrupo, grupos.mostrar FROM seleccion, materias, cursos, grupos WHERE idProfesor=$idProfesor AND idEscenario = $idEscenario AND seleccion.idMateria = materias.id AND cursos.id = materias.idCurso AND cursos.id = grupos.idCurso AND seleccion.idGrupo = grupos.id ORDER BY orden");

    while ($fila = mysqli_fetch_assoc($resultado))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        $horas = $fila['horas'];
        $abr = $fila['abr'];
        $mostrar = $fila['mostrar'];
        $abrGrupo = $mostrar?$fila['abrGrupo']:'';
        $asignadaDirectiva = $fila['asignada_directiva'];
        // Si la ha asignado la directiva y no tenemos permisos de edición, no podremos seleccionarla para borrarla
        if ($asignadaDirectiva && !$super)
            echo '<div class="seleccion izquierda claro" id="sel' . $id . '">' . $nombre . ' (' . $abr . $abrGrupo . ', ' . $horas . 'h)</div>';
        else
            echo '<div class="seleccion izquierda claro" id="sel' . $id . '" onclick="seleccionarSeleccion(' . $id . ')">' . $nombre . ' (' . $abr . $abrGrupo . ', ' . $horas . 'h)</div>';
    }
    
    mysqli_free_result($resultado);
    
    include ('../../includes/database2.php');
}

?>