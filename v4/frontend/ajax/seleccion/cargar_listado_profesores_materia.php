<?php

// Carga un listado de profesores que han elegido la materia indicada para el grupo y escenario indicados

// Necesitamos recibir el id de la materia, del grupo y del escenario en cuestión
if (!empty($_REQUEST['idMateria']) && !empty($_REQUEST['idGrupo']) && !empty($_REQUEST['idEscenario']))
{
    include('../../includes/database.php');
    $idMateria = $_REQUEST['idMateria'];
    $idGrupo = $_REQUEST['idGrupo'];
    $idEscenario = $_REQUEST['idEscenario'];
    $resultado = mysqli_query($db, "SELECT profesores.id AS idprof, seleccion.id AS idsel, profesores.nombre, seleccion.orden AS ordensel, profesores.orden as ordenprof, seleccion.horas FROM profesores, seleccion WHERE profesores.id = seleccion.idProfesor AND seleccion.idMateria = $idMateria AND seleccion.idGrupo = $idGrupo AND seleccion.idEscenario = $idEscenario ORDER BY seleccion.orden, profesores.orden");
    while ($fila = mysqli_fetch_assoc($resultado))
    {
        echo '<div style="margin-bottom:10px">' . $fila['nombre'] /* . ' (' . $fila['horas'] . 'h), ' . $fila['ordenprof'] . 'º prof., ' . $fila['ordensel'] . 'º sel.' */ . '</div>';
    }
    mysqli_free_result($resultado);    
    include ('../../includes/database2.php');
}

?>