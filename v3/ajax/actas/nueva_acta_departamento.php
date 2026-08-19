<?php

// Devuelve el texto inicial a añadir a una nueva acta de departamento, formado por:
// - Apartado "Asistentes" relleno con listado completo de profesores del departamento, ordenado alfabéticamente por nombre
// - Inicio del apartado "Orden del día"

@session_start();

if (isset($_SESSION['departamentoUsuario']))
{
    include('../../includes/database.php');
    $result = mysqli_query($db, "SELECT nombre FROM profesores WHERE idDepartamento = ". $_SESSION['departamentoUsuario'] . " ORDER BY nombre");
    echo '<h3>Asistentes</h3>';
    echo '<ol>';
    while ($fila = mysqli_fetch_assoc($result))
    {
        echo '<li>' . $fila['nombre'] . '</li>';
    }
    echo '</ol>';
    echo '<h3>Orden del día</h3>';
    echo '<p>Por completar</p>';
    mysqli_free_result($result);    
    include ('../../includes/database2.php');
}

?>