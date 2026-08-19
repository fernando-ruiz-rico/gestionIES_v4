<?php

// Inserta/Modifica la materia recibida por POST

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

// Campos obligatorios: nombre de la materia, número de unidades ofertadas y horas semanales
if ($permisos && !empty($_REQUEST['nombre']) && !empty($_REQUEST['cantidad']) && !empty($_REQUEST['horas']))
{
    include('../../includes/database.php');
    $nombre = $_REQUEST['nombre'];
    $codigoOficial = $_REQUEST['codigoOficial'];
    $nombreOficial = $_REQUEST['nombreOficial'];
    $creditosECTS = $_REQUEST['creditosECTS'];
    $horasAnuales = $_REQUEST['horasAnuales'];
    $cantidad = $_REQUEST['cantidad'];
    $horas = $_REQUEST['horas'];
    $horasComplementarias = $_REQUEST['horasComplementarias'];
    $tipo = $_REQUEST['tipo'];
    $departamento = empty($_REQUEST['departamento'])?"NULL":$_REQUEST['departamento'];
    $especialidad = $_REQUEST['especialidad'];
    $idCurso = $_REQUEST['idCurso'];
    $computablesHorasGrupo = empty($_REQUEST['computablesHorasGrupo'])?0:1;
    $asignadaDirectiva = empty($_REQUEST['asignadaDirectiva'])?0:1;
    $tieneProgramacion = empty($_REQUEST['tieneProgramacion'])?0:1;
    $divisible = empty($_REQUEST['divisible'])?0:1;
    $minNumProfesores = empty($_REQUEST['minNumProfesores'])?0:$_REQUEST['minNumProfesores'];
    $maxGruposProfesor = empty($_REQUEST['maxGruposProfesor'])?0:$_REQUEST['maxGruposProfesor'];

    if (!empty($especialidad))
        $especialidad = "'$especialidad'";
    else
        $especialidad = "NULL";

    if (!empty($codigoOficial))
        $codigoOficial = "'$codigoOficial'";
    else
        $codigoOficial = "NULL";

    if (!empty($nombreOficial))
        $nombreOficial = "'$nombreOficial'";
    else
        $nombreOficial = "NULL";

    if(empty($creditosECTS))
        $creditosECTS = "NULL";
    if(empty($horasAnuales))
        $horasAnuales = "NULL";

    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO materias (nombre, idCurso, cantidad, horas, horas_complementarias, idDepartamento, idEspecialidad, computables_horas_grupo, asignada_directiva, min_num_profesores, max_grupos_profesor, tiene_programacion, divisible, tipo, codigo_oficial, nombre_oficial, creditos_ects, horas_anuales) VALUES ('$nombre', $idCurso, $cantidad, $horas, $horasComplementarias, $departamento, $especialidad, $computablesHorasGrupo, $asignadaDirectiva, $minNumProfesores, $maxGruposProfesor, $tieneProgramacion, $divisible, '$tipo', $codigoOficial, $nombreOficial, $creditosECTS, $horasAnuales)");    
        // Insertamos también la configuración por defecto para esa materia en todos los grupos
        // Primero obtenemos el id de la materia introducida
        $result = mysqli_query($db, "SELECT max(id) AS idMax FROM materias");
        $fila = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        $idMateria = $fila['idMax'];
        // Ahora recorremos los grupos del curso en cuestión y les añadimos la configuración para esta materia
        $result = mysqli_query($db, "SELECT * FROM grupos WHERE idCurso = $idCurso");
        while($fila = mysqli_fetch_assoc($result))
        {
            $idGrupo = $fila['id'];
            mysqli_query($db, "INSERT INTO materias_grupos (idMateria, idGrupo, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor) VALUES ($idMateria, $idGrupo, $cantidad, $horas, $horasComplementarias, $minNumProfesores, $maxGruposProfesor)");
        }
        mysqli_free_result($result);
    }
    else
        mysqli_query($db, "UPDATE materias SET nombre='$nombre', cantidad=$cantidad, horas=$horas, horas_complementarias=$horasComplementarias, idDepartamento=$departamento, idEspecialidad=$especialidad, computables_horas_grupo=$computablesHorasGrupo, asignada_directiva=$asignadaDirectiva, min_num_profesores=$minNumProfesores, max_grupos_profesor=$maxGruposProfesor, tiene_programacion=$tieneProgramacion, divisible=$divisible, tipo='$tipo', codigo_oficial=$codigoOficial, nombre_oficial=$nombreOficial, creditos_ects=$creditosECTS, horas_anuales=$horasAnuales WHERE id = " . $_REQUEST['id']);            
    include ('../../includes/database2.php');
}

?>