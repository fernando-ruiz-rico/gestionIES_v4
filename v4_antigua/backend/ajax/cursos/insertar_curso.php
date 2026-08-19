<?php

// Inserta o actualiza el curso que se recibe

@session_start();
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if ($permisos && !empty($_REQUEST['nombre']))
{
    include('../../includes/database.php');
    $nombre = $_REQUEST['nombre'];
    $abreviatura = $_REQUEST['abreviatura'];
    $horas = $_REQUEST['horasSemana'];
    $categoria = $_REQUEST['categoria'];
    if (!empty($categoria))
        $categoria = "'$categoria'";
    else
        $categoria = "NULL";
    if (empty($horas))
        $horas = 0;

    // Si no llega id de curso es una inserción
    if (empty($_REQUEST['id']))
    {
        mysqli_query($db, "INSERT INTO cursos (nombre, abreviatura, horas_semana, categoria) VALUES ('$nombre', '$abreviatura', $horas, $categoria)");    
    } else {
        mysqli_query($db, "UPDATE cursos SET nombre='$nombre', abreviatura='$abreviatura', horas_semana=$horas, categoria = $categoria WHERE id = " . $_REQUEST['id']);            
    }
    include ('../../includes/database2.php');
}

?>