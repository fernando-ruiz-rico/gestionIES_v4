<?php

// Esta página devuelve en formato HTML el listado de profesores del departamento cuyo id se recibe
// El listado se vuelca al "div" con id "listaprofesores" de la vista "profesores.php".

if (!empty($_REQUEST['idDepartamento']))
{
    include('../../includes/database.php');
    $idDepartamento = $_REQUEST['idDepartamento'];
    $result = mysqli_query($db, "SELECT * FROM profesores WHERE idDepartamento = $idDepartamento ORDER BY orden");

    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $nombre = $fila['nombre'];
        // Booleanos para indicar si el profesor actual es el jefe de departamento, y si está actualmente activo o no
        $jefe = $fila['jefe_departamento'];
        $activo = $fila['activo'];
        echo '<div class="listado claro izquierda profesor" id="pr' . $id . '">';
        echo '<div class="izquierda">'. $nombre . '</div>';
        echo '<div class="derecha">';
        // Botón para activar/desactivar al profesor
        echo '<button title="Activar/Desactivar profesor" class="btn btn-light" onclick="cambiarActivo(' . $id . ')"><i class="bi ' . ($activo?'bi-toggle-on':'bi-toggle-off') . '"></i></button>';
        // Botón para asignar la jefatura de departamento
        echo '<button title="Elegir jefe de departamento" class="btn ' . ($jefe?'btn-success':'btn-light') . '" onclick="cambiarJefe(' . $id . ', ' . $idDepartamento . ')"><i class="bi bi-award"></i></button>';
        // Botones para borrar profesor o cargar el modal para editar sus datos
        echo '<button class="btn btn-light" onclick="borrarProfesor(' . $id . ",'" . $nombre . "'" . ')"><i class="bi bi-trash"></i></button>';
        echo '<button class="btn btn-light" onclick="cargarPerfil(' . $id . ',' . $idDepartamento . ')"><i class="bi bi-pencil-square"></i></button></div>';
        echo '</div>';
    }

    mysqli_free_result($result);
    include ('../../includes/database2.php');
}

?>
