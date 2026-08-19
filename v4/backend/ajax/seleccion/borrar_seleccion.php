<?php

// Borra la materia indicada de la selección del profesor indicado, para el escenario elegido

@session_start();

// Necesitamos recibir el "id" de la materia, del profesor y del escenario en cuestión
if (!empty($_SESSION['idUsuario']) && !empty($_REQUEST['id']) && !empty($_REQUEST['idProfesor']) && !empty($_REQUEST['idEscenario']))
{
    include ('../../includes/database.php');

    $id = $_REQUEST['id'];
    $idProfesor = $_REQUEST['idProfesor'];
    $idEscenario = $_REQUEST['idEscenario'];
    $resultado = mysqli_query($db, "SELECT orden FROM seleccion WHERE id = $id");
    $fila = mysqli_fetch_assoc($resultado);
    mysqli_free_result($resultado);
    $orden = $fila['orden'];
    // Decrementamos una unidad el orden de todas las materias que iban detrás de esta en la selección del profesor
    mysqli_query($db, "UPDATE seleccion SET orden = orden - 1 WHERE orden > $orden AND idProfesor = $idProfesor AND idEscenario=$idEscenario");
    // Borramos la selección
    mysqli_query($db, "DELETE FROM seleccion WHERE id = $id");

    include ('../../includes/database2.php');

    // Redirigimos a "listar_seleccion" para que devuelva la selección actualizada por AJAX
    header("Location: listar_seleccion.php?idProfesor=$idProfesor&idEscenario=$idEscenario");    
}


?>