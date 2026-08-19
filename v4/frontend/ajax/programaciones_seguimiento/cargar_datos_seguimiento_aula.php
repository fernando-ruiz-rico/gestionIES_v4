<?php
function cargarDatosSeguimientoAula($idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion)
{
    $sql = "SELECT temporalizacion, resultados, inclusion, num_aprobados, num_suspensos, num_otros 
            FROM seguimiento_programaciones_aula 
            WHERE idMateria = $idMateria AND idGrupo = $idGrupo AND idProfesor = $idProfesor AND curso = '$curso' AND evaluacion = $idEvaluacion";
    $seguimientoAula = consultarBaseDeDatos($sql);

    return empty($seguimientoAula) ? "{}" : json_encode($seguimientoAula[0]);
}

@session_start();

if (!empty($_SESSION['idUsuario']) && 
    !empty($_REQUEST['idMateria']) && !empty($_REQUEST['idGrupo']) &&
    !empty($_REQUEST['idProfesor']) && !empty($_REQUEST['curso']) && !empty($_REQUEST['idEvaluacion']))
{
    foreach ($_REQUEST as $key => $value) $$key = $value;

    require_once('../../includes/database.php');
    require_once('../../includes/utilidades.php');
    header('Content-type: application/json; charset=utf-8');
    echo cargarDatosSeguimientoAula((int)$idMateria, (int)$idGrupo, (int)$idProfesor, $curso, (int)$idEvaluacion);
    require_once('../../includes/database2.php');
}
?>