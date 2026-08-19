<?php

// Esta página devuelve en formato HTML el listado de especialidades del departamento cuyo id se recibe
// El listado se vuelca al "div" con id "listaespecialidades" de la vista "especialidades.php".

if (!empty($_REQUEST['idDepartamento']))
{
    include('../../includes/database.php');
    $idDepartamento = $_REQUEST['idDepartamento'];
    $result = mysqli_query($db, "SELECT * FROM especialidades WHERE idDepartamento = $idDepartamento ORDER BY id");

    while ($fila = mysqli_fetch_assoc($result))
    {
        $id = $fila['id'];
        $descripcion = $fila['descripcion'];
        echo '<div class="listado claro izquierda">';
        echo '<div class="izquierda">('. $id . ') - ' . $descripcion . '</div>';
        // Enlaces para borrar o editar la especialidad
        echo '<div class="derecha"><button class="btn btn-light" onclick="borrarEspecialidad(\'' . $id . '\')"><i class="bi bi-trash"></i></button><button class="btn btn-light" onclick="cargarEspecialidadModal(\'' . $id . '\')"><i class="bi bi-pencil-square"></i></button></div>';
        echo '</div>';
    }

    mysqli_free_result($result);
    include ('../../includes/database2.php');
}


?>
