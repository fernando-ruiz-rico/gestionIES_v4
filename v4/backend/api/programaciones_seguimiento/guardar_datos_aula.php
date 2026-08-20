<?php
/**
 * API para guardar datos de seguimiento de aula
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idMateria = isset($_POST['idMateria']) ? intval($_POST['idMateria']) : 0;
$idGrupo = isset($_POST['idGrupo']) ? intval($_POST['idGrupo']) : 0;
$idProfesor = isset($_POST['idProfesor']) ? intval($_POST['idProfesor']) : 0;
$curso = isset($_POST['curso']) ? $_POST['curso'] : '';
$idEvaluacion = isset($_POST['idEvaluacion']) ? intval($_POST['idEvaluacion']) : 0;
$temporalizacion = isset($_POST['temporalizacion']) ? $_POST['temporalizacion'] : '';
$resultados = isset($_POST['resultados']) ? $_POST['resultados'] : '';
$inclusion = isset($_POST['inclusion']) ? $_POST['inclusion'] : '';
$numAprobados = isset($_POST['num_aprobados']) ? intval($_POST['num_aprobados']) : 0;
$numSuspensos = isset($_POST['num_suspensos']) ? intval($_POST['num_suspensos']) : 0;
$numOtros = isset($_POST['num_otros']) ? intval($_POST['num_otros']) : 0;

if ($idMateria <= 0 || $idGrupo <= 0 || $idProfesor <= 0 || empty($curso) || $idEvaluacion <= 0) {
    sendJSONError('Parámetros inválidos');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

$temporalizacion = mysqli_real_escape_string($conn, $temporalizacion);
$resultados = mysqli_real_escape_string($conn, $resultados);
$inclusion = mysqli_real_escape_string($conn, $inclusion);

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

mysqli_query($conn, $sql);

closeDBConnection($conn);

sendJSONSuccess(array('guardado' => true));
?>
