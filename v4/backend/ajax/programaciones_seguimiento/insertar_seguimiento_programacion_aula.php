<?php
// -------------------------------
// Inserta o actualiza los datos de seguimiento de aula en una sola instrucción
// -------------------------------
function guardarDatosSeguimientoAula($idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion, $temporalizacion, $resultados, $inclusion, $numAprobados, $numSuspensos, $numOtros)
{
    $sql = "INSERT INTO seguimiento_programaciones_aula 
                (idMateria, idGrupo, idProfesor, curso, evaluacion, temporalizacion, resultados, inclusion, num_aprobados, num_suspensos, num_otros) 
            VALUES 
                ($idMateria, $idGrupo, $idProfesor, '$curso', $idEvaluacion, '$temporalizacion', '$resultados', '$inclusion', $numAprobados, $numSuspensos, $numOtros)
            ON DUPLICATE KEY UPDATE 
                temporalizacion = VALUES(temporalizacion),
                resultados = VALUES(resultados),
                inclusion = VALUES(inclusion),
                num_aprobados = VALUES(num_aprobados),
                num_suspensos = VALUES(num_suspensos),
                num_otros = VALUES(num_otros)";

    return actualizarBaseDeDatos($sql); 
}

@session_start();

// Validamos que existan los datos necesarios
if (!empty($_SESSION['idUsuario']) && 
    !empty($_REQUEST['idMateria']) && !empty($_REQUEST['idGrupo']) &&
    !empty($_REQUEST['idProfesor']) && !empty($_REQUEST['curso']) && !empty($_REQUEST['idEvaluacion']))
{
    // Importamos las utilidades de base de datos
    require_once('../../includes/database.php');
    require_once('../../includes/utilidades.php');
    
    // Extraemos variables del REQUEST (mantenemos tu estilo $$key)
    foreach ($_REQUEST as $key => $value) $$key = $value;


    // Aseguramos que los números sean enteros
    $error = guardarDatosSeguimientoAula(
        (int)$idMateria, 
        (int)$idGrupo, 
        (int)$idProfesor, 
        $curso, 
        (int)$idEvaluacion,
        empty($temporalizacion) ? '' : $temporalizacion,
        empty($resultados) ? '' : $resultados,
        empty($inclusion) ? '' : $inclusion,
        (int)(empty($num_aprobados) ? 0 : $num_aprobados),
        (int)(empty($num_suspensos) ? 0 : $num_suspensos),
        (int)(empty($num_otros) ? 0 : $num_otros)
    );

    echo $error ? 'no' : 'si';
}
?>