<?php

// Inserta/Modifica los datos de una materia para un grupo determinado

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
$error = TRUE;

if ($permisos)
{
    $idMateria = $_REQUEST['idMateria'];
    $idGrupo = $_REQUEST['idGrupo'];
    $cantidad = $_REQUEST['cantidad'];
    $horas = $_REQUEST['horas'];
    $horasComplementarias = $_REQUEST['horasComplementarias'];
    $minProfesores = $_REQUEST['minNumProfesores'];
    $maxGruposProf = $_REQUEST['maxGruposProfesor'];

    include ('../../includes/database.php');

    // Comprobamos si ya existe un registro para ese grupo o materia
    $existeGrupo = mysqli_query($db, "SELECT * FROM materias_grupos WHERE idMateria = $idMateria AND idGrupo = $idGrupo");
    if(mysqli_num_rows($existeGrupo) > 0)
    {
        // Existe grupo: es una modificación
        $result = mysqli_query($db, "UPDATE materias_grupos SET cantidad = $cantidad, horas = $horas, horas_complementarias = $horasComplementarias, min_num_profesores = $minProfesores, max_grupos_profesor = $maxGruposProf WHERE idMateria = $idMateria AND idGrupo = $idGrupo");
    }
    else
    {
        // No existe grupo: es una inserción nueva
        $result = mysqli_query($db, "INSERT INTO materias_grupos(idMateria, idGrupo, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor) VALUES ($idMateria, $idGrupo, $cantidad, $horas, $horasComplementarias, $minProfesores, $maxGruposProf)");
    }
    if($result)
        $error = FALSE;

    echo $error?'si':'no';
    include ('../../includes/database2.php');
}
?>