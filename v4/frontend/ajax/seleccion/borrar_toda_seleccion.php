<?php

// Borramos todas las selecciones del profesor indicado para el escenario indicado

// Comprobamos si hay usuario en sesión, y si no lo hay redirigimos a login
if (!isset($_SESSION))
    @session_start();
if (empty($_SESSION['idUsuario']))
    header("Location: ../../login.php");  

// Guardamos si el usuario tiene permisos superiores (jefe de departamento o admin)
$super = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if (!empty($_REQUEST['idProfesor']) && !empty($_REQUEST['idEscenario']))
{
    include ('../../includes/database.php');    
    $idProfesor = $_REQUEST['idProfesor'];
    $idEscenario = $_REQUEST['idEscenario'];

    // Si no tenemos permisos suficientes, sólo podemos eliminar las materias que no nos haya asignado la directiva
    if (!$super)
        mysqli_query($db, "DELETE FROM seleccion WHERE idProfesor = $idProfesor AND idEscenario = $idEscenario AND idMateria NOT IN (SELECT id FROM materias WHERE asignada_directiva = 1)");
    else
        mysqli_query($db, "DELETE FROM seleccion WHERE idProfesor = $idProfesor AND idEscenario = $idEscenario");

    include ('../includes/database2.php');

    // Redirigimos a "listar_seleccion" para que devuelva la selección actualizada por AJAX
    header("Location: listar_seleccion.php?idProfesor=$idProfesor&idEscenario=$idEscenario");    
}

?>