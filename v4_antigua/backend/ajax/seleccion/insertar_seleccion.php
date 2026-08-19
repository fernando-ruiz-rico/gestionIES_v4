<?php

// Añade una nueva selección al profesor indicado

@session_start();

// Necesitamos recibir el id del profesor, de la materia, del grupo y del escenario elegidos, junto con las horas elegidas de la materia
if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['idProfesor']) && !empty($_REQUEST['idMateria']) && !empty($_REQUEST['idGrupo']) && !empty($_REQUEST['idEscenario']) && !empty($_REQUEST['horas']))
{
    include('../../includes/database.php');

    $idProfesor = $_REQUEST['idProfesor'];
    $idMateria = $_REQUEST['idMateria'];    
    $idGrupo = $_REQUEST['idGrupo'];
    $idEscenario = $_REQUEST['idEscenario'];
    $horas = $_REQUEST['horas'];
    
    // Vemos si la materia en cuestión la asigna la directiva o la elige libremente el profesor
    $resultado = mysqli_query($db, "SELECT asignada_directiva FROM materias WHERE id = " . $_REQUEST['idMateria']);
    $fila = mysqli_fetch_assoc($resultado);
    mysqli_free_result($resultado);
    $asignadaDirectiva = $fila['asignada_directiva'];
    
    // Si la asigna la directiva no hay peligro de conflicto con otro profesor, así que se le da un orden inferior
    $resultado = mysqli_query($db, "SELECT COUNT(*) AS total FROM seleccion WHERE idProfesor = $idProfesor AND idEscenario = $idEscenario");
    $fila = mysqli_fetch_assoc($resultado);
    mysqli_free_result($resultado);    
    $orden = $asignadaDirectiva?100:$fila['total'] + 1;

    // Añadimos la nueva selección
    mysqli_query($db, "INSERT INTO seleccion (idProfesor, idGrupo, idMateria, idEscenario, horas, orden) VALUES ($idProfesor, $idGrupo, $idMateria, $idEscenario, $horas, $orden)");    

    include ('../includes/database2.php');

    // Redirigimos a "listar_seleccion" para que devuelva la selección actualizada por AJAX
    header("Location: listar_seleccion.php?idProfesor=$idProfesor&idEscenario=$idEscenario");
}

?>