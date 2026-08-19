<?php

// Inserta/Modifica un grupo

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['nombre']))
{
    include('../../includes/database.php');
    $idCurso = $_REQUEST['idCurso'];
    $nombre = $_REQUEST['nombre'];
    $abreviatura = $_REQUEST['abreviatura'];
    $horasComplementariasDual = $_REQUEST['horasComplementariasDual'];
    $mostrar = "0";
    if(!empty($_REQUEST['mostrar']))
        $mostrar = "1";

    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO grupos (nombre, abreviatura, idCurso, mostrar, horas_complementarias_dual) VALUES ('$nombre', '$abreviatura', $idCurso, $mostrar, $horasComplementariasDual)");    
        // Insertamos la configuración de las materias para ese grupo, a partir de los datos de referencia de la materia
        // Primero obtenemos el id del nuevo grupo creado
        $result = mysqli_query($db, "SELECT max(id) AS idMax FROM grupos");
        $fila = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        $idGrupo = $fila['idMax'];
        // Ahora obtenemos todas las materias del curso
        $result = mysqli_query($db, "SELECT * FROM materias WHERE idCurso = $idCurso");
        while($fila = mysqli_fetch_assoc($result))
        {
            $idMateria = $fila['id'];
            $cantidad = $fila['cantidad'];
            $horas = $fila['horas'];
            $horasComplementarias = $fila['horas_complementarias'];
            $minProfesores = $fila['min_num_profesores'];
            $maxGruposProf = $fila['max_grupos_profesor'];
            mysqli_query($db, "INSERT INTO materias_grupos (idMateria, idGrupo, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor) VALUES ($idMateria, $idGrupo, $cantidad, $horas, $horasComplementarias, $minProfesores, $maxGruposProf)");
        }
        mysqli_free_result($result);
    } else {
        mysqli_query($db, "UPDATE grupos SET nombre='$nombre', abreviatura='$abreviatura', mostrar=$mostrar, horas_complementarias_dual=$horasComplementariasDual WHERE id = " . $_REQUEST['id']);            
    }    
    include ('../../includes/database2.php');
}

?>