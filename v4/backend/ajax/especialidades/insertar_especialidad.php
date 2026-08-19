<?php

// Esta página inserta el departamento que recibe en la petición, o lo actualiza si viene con un 
// "id" antiguo ya asignado.
// Devuelve "si" si ha habido algún error en la inserción/actualización, junto con el mensaje producido

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';
$result = FALSE;
$resultError = "";

// Debemos recibir el "id" de la especialidad, el del departamento y le descripción larga de la especialidad
if ($permisos && !empty($_REQUEST['id']) && !empty($_REQUEST['descripcion']) && !empty($_REQUEST['idDepartamento']))
{
    include('../../includes/database.php');
    $id = $_REQUEST['id'];
    $descripcion = $_REQUEST['descripcion'];
    $idDepartamento = $_REQUEST['idDepartamento'];
    // El resto de datos pueden ser NULL, así que debemos distinguir si llegan o no
    $horasTutoria = "NULL";
    if (!empty($_REQUEST['horasTutoria']))
        $horasTutoria = $_REQUEST['horasTutoria'];
    $horasIngles = "NULL";
    if (!empty($_REQUEST['horasIngles']))
        $horasIngles = $_REQUEST['horasIngles'];
    $profesores = 0;
    if (!empty($_REQUEST['profesores']))
        $profesores = $_REQUEST['profesores'];

    // Si no lleg aun "idAntiguo" (campo "hidden" del formulario) es una inserción
    if (empty($_REQUEST['idAntiguo']))
    {
        $result = mysqli_query($db, "INSERT INTO especialidades (id, descripcion, idDepartamento, horasTutoria, horasIngles, profesores) VALUES ('$id', '$descripcion', $idDepartamento, $horasTutoria, $horasIngles, $profesores)");    
        if(!$result)
            $resultError = mysqli_error($db);
    // En caso contrario es una actualización
    } else {
        // Actualizamos también el nuevo "id" en los profesores vinculados a la especialidad y las materias
        mysqli_query($db, "UPDATE profesores SET idEspecialidad='$id' WHERE idEspecialidad='" . $_REQUEST['idAntiguo'] . "'");            
        mysqli_query($db, "UPDATE materias SET idEspecialidad='$id' WHERE idEspecialidad='" . $_REQUEST['idAntiguo'] . "'");            
        // Ahora actualizamos la especialidad en sí
        $result = mysqli_query($db, "UPDATE especialidades SET id='$id', descripcion='$descripcion', horasTutoria=$horasTutoria, horasIngles=$horasIngles, profesores=$profesores WHERE id='" . $_REQUEST['idAntiguo'] . "'");            
        if(!$result)
            $resultError = mysqli_error($db);
    }
    include ('../../includes/database2.php');
}

if ($result)
    echo "no";
else
    echo "si" . $resultError;

?>