<?php

// Carga un listado de opciones <option> con las materias vinculadas al usuario actual
// Si es un jefe de departamento o un admin, verá todas las materias del departamento actual
// Si es un profesor sólo verá las materias que imparte

include('includes/database.php');

$todasMaterias = empty($_REQUEST['idProfesor']) && isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if ($todasMaterias) 
{
    $result = mysqli_query($db, "SELECT DISTINCT cursos.orden, cursos.nombre AS nomCurso, materias.nombre as nomMateria, materias.id FROM cursos, materias WHERE cursos.id = materias.idCurso AND materias.tiene_programacion = TRUE AND materias.idDepartamento = " . $_SESSION['departamentoUsuario'] . " ORDER BY orden, nomMateria");
} else {
    // Cargar únicamente las materias del profesor
    $idProfesor = empty($_REQUEST['idProfesor']) ? $_SESSION['idUsuario'] : intval($_REQUEST['idProfesor']);

    $result = mysqli_query($db, "SELECT DISTINCT cursos.nombre AS nomCurso, materias.nombre as nomMateria, materias.id FROM cursos, materias, seleccion, escenarios_desideratas WHERE escenarios_desideratas.id = seleccion.idEscenario AND cursos.id = materias.idCurso AND materias.id = seleccion.idMateria AND seleccion.idProfesor = " . $idProfesor . " AND materias.tiene_programacion = TRUE AND escenarios_desideratas.actual = TRUE ORDER BY nomMateria");
}

while ($fila = mysqli_fetch_assoc($result))
{
    echo '<option value="' . $fila['id'] . '">' . $fila['nomMateria'] . ' (' . $fila['nomCurso'] . ')</option>';
}

mysqli_free_result($result);

include('includes/database2.php');

?>