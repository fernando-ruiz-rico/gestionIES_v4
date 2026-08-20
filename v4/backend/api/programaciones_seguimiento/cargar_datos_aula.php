<?php
/**
 * API para cargar datos de seguimiento de aula
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
$idGrupo = isset($_GET['idGrupo']) ? intval($_GET['idGrupo']) : 0;
$idProfesor = isset($_GET['idProfesor']) ? intval($_GET['idProfesor']) : 0;
$curso = isset($_GET['curso']) ? $_GET['curso'] : '';
$idEvaluacion = isset($_GET['idEvaluacion']) ? intval($_GET['idEvaluacion']) : 0;

if ($idMateria <= 0 || $idGrupo <= 0 || $idProfesor <= 0 || empty($curso) || $idEvaluacion <= 0) {
    sendJSONError('Parámetros inválidos');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

$sql = "SELECT temporalizacion, resultados, inclusion, num_aprobados, num_suspensos, num_otros 
        FROM seguimiento_programaciones_aula 
        WHERE idMateria = $idMateria AND idGrupo = $idGrupo AND idProfesor = $idProfesor AND curso = '$curso' AND evaluacion = $idEvaluacion";

$result = mysqli_query($conn, $sql);

$resultado = new stdClass();
if ($result && mysqli_num_rows($result) > 0) {
    $fila = mysqli_fetch_assoc($result);
    foreach ($fila as $key => $value) {
        $resultado->$key = $value;
    }
    mysqli_free_result($result);
}

closeDBConnection($conn);

sendJSONSuccess($resultado);
?>
