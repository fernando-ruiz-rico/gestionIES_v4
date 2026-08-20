<?php
/**
 * API para insertar/actualizar un tema
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idMateria = isset($_POST['idMateria']) ? intval($_POST['idMateria']) : 0;
$orden = isset($_POST['orden']) ? intval($_POST['orden']) : 0;
$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';

if ($idMateria <= 0 || $orden <= 0 || empty($titulo)) {
    sendJSONError('Parámetros inválidos');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

// Campos de texto con valor por defecto ''
$descripcion = isset($_POST['descripcion']) ? mysqli_real_escape_string($conn, $_POST['descripcion']) : '';
$justificacion = isset($_POST['justificacion']) ? mysqli_real_escape_string($conn, $_POST['justificacion']) : '';
$contexto = isset($_POST['contexto']) ? mysqli_real_escape_string($conn, $_POST['contexto']) : '';
$contenidos = isset($_POST['contenidos']) ? mysqli_real_escape_string($conn, $_POST['contenidos']) : '';
$secuenciacion = isset($_POST['secuenciacion']) ? mysqli_real_escape_string($conn, $_POST['secuenciacion']) : '';
$recursos = isset($_POST['recursos']) ? mysqli_real_escape_string($conn, $_POST['recursos']) : '';
$evaluacion = isset($_POST['evaluacion']) ? mysqli_real_escape_string($conn, $_POST['evaluacion']) : '';
$metodologia = isset($_POST['metodologia']) ? mysqli_real_escape_string($conn, $_POST['metodologia']) : '';
$adaptaciones = isset($_POST['adaptaciones']) ? mysqli_real_escape_string($conn, $_POST['adaptaciones']) : '';

// Campos numéricos con valor por defecto 0
$horas = isset($_POST['horas']) ? intval($_POST['horas']) : 0;
$trimestre = isset($_POST['trimestre']) ? intval($_POST['trimestre']) : 0;
$peso_evaluacion = isset($_POST['peso_evaluacion']) ? intval($_POST['peso_evaluacion']) : 0;

// Flags con valor por defecto 1
$contexto_defecto = isset($_POST['contexto_defecto']) ? intval($_POST['contexto_defecto']) : 1;
$recursos_defecto = isset($_POST['recursos_defecto']) ? intval($_POST['recursos_defecto']) : 1;
$metodologia_defecto = isset($_POST['metodologia_defecto']) ? intval($_POST['metodologia_defecto']) : 1;
$adaptaciones_defecto = isset($_POST['adaptaciones_defecto']) ? intval($_POST['adaptaciones_defecto']) : 1;

$sql = "INSERT INTO temas (
    idMateria, orden, titulo, horas, trimestre, peso_evaluacion,
    descripcion, justificacion, contexto, contenidos, secuenciacion,
    recursos, evaluacion, metodologia, adaptaciones,
    contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto
) VALUES (
    $idMateria, $orden, '$titulo', $horas, $trimestre, $peso_evaluacion,
    '$descripcion', '$justificacion', '$contexto', '$contenidos', '$secuenciacion',
    '$recursos', '$evaluacion', '$metodologia', '$adaptaciones',
    $contexto_defecto, $recursos_defecto, $metodologia_defecto, $adaptaciones_defecto
)";

mysqli_query($conn, $sql);
$insertId = mysqli_insert_id($conn);

closeDBConnection($conn);

sendJSONSuccess(array('id' => $insertId));
?>
