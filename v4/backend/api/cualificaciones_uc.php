<?php
/**
 * API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3)
 * Fiel a v3: las cualificaciones profesionales (cualificaciones_profesionales)
 * pueden asociar unidades de competencia (unidades_competencia) a través de
 * la tabla cualificaciones_unidades.
 *
 * Acciones relativas a cualificaciones:
 *   - listar_cualificaciones : lista las cualificaciones profesionales
 *   - obtener_cualificacion  : devuelve una cualificación
 *   - guardar_cualificacion  : inserta/actualiza una cualificación
 *   - eliminar_cualificacion : elimina una cualificación (si no tiene UC asociadas)
 *
 * Acciones relativas a unidades de competencia:
 *   - listar_unidades       : lista las unidades de competencia
 *   - obtener_unidad        : devuelve una unidad
 *   - guardar_unidad        : inserta/actualiza una unidad
 *   - eliminar_unidad       : elimina una unidad (si no está asociada)
 *   - guardar_asociacion    : asocia una unidad a una cualificación
 *   - eliminar_asociacion   : disocia una unidad de una cualificación
 *
 * Permisos: solo el rol admin.
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
@session_start();
 $datos = json_decode(file_get_contents("php://input"), true) ?: [];

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos');
}

try {
    switch ($action) {
        // Lista las cualificaciones profesionales
        case 'listar_cualificaciones':
            $sql = "SELECT codigo, texto FROM cualificaciones_profesionales ORDER BY codigo";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $cualificaciones = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $cualificaciones[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($cualificaciones);
            break;

        // Devuelve una cualificación
        case 'obtener_cualificacion':
            $codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
            if (empty($codigo)) {
                throw new Exception('Código de cualificación inválido');
            }
            $codigo = mysqli_real_escape_string($db, $codigo);
            $result = mysqli_query($db, "SELECT * FROM cualificaciones_profesionales WHERE codigo='$codigo'");
            $fila = mysqli_fetch_assoc($result);
            if (!$fila) {
                throw new Exception('Cualificación no encontrada');
            }
            closeDBConnection($db);
            sendJSONSuccess($fila);
            break;

        // Inserta o actualiza una cualificación profesional
        case 'guardar_cualificacion':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigo = $datos['codigo'];
            $texto = $datos['texto'];
            $id = isset($datos['id']) && !empty($datos['id']) ? intval($datos['id']) : 0;
            if (empty($codigo) || empty($texto)) {
                throw new Exception('Datos incompletos para guardar la cualificación');
            }
            $codigo = mysqli_real_escape_string($db, $codigo);
            $texto = mysqli_real_escape_string($db, $texto);
            if ($id > 0) {
                $query = "UPDATE cualificaciones_profesionales SET codigo='$codigo', texto='$texto' WHERE codigo='$codigo'";
            } else {
                $query = "INSERT INTO cualificaciones_profesionales (codigo, texto) VALUES ('$codigo', '$texto')";
            }
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess($id > 0 ? array('codigo' => $codigo) : array('codigo' => $codigo), 'Cualificación guardada');
            break;

        // Elimina una cualificación (solo si no tiene UC asociadas)
        case 'eliminar_cualificacion':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigo = isset($datos['codigo']) ? $datos['codigo'] : '';
            if (empty($codigo)) {
                throw new Exception('Código de cualificación inválido');
            }
            $codigo = mysqli_real_escape_string($db, $codigo);
            $result = mysqli_query($db, "SELECT COUNT(*) AS total FROM cualificaciones_unidades WHERE codigoCualificacion='$codigo'");
            $fila = mysqli_fetch_assoc($result);
            if ($fila['total'] > 0) {
                closeDBConnection($db);
                sendJSONError('La cualificación tiene unidades de competencia asociadas');
            }
            if (!mysqli_query($db, "DELETE FROM cualificaciones_profesionales WHERE codigo='$codigo'")) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Cualificación eliminada');
            break;

        // Lista las unidades de competencia
        case 'listar_unidades':
            $sql = "SELECT codigo, texto FROM unidades_competencia ORDER BY codigo";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $unidades = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $unidades[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($unidades);
            break;

        // Devuelve una unidad de competencia
        case 'obtener_unidad':
            $codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
            if (empty($codigo)) {
                throw new Exception('Código de unidad inválido');
            }
            $codigo = mysqli_real_escape_string($db, $codigo);
            $result = mysqli_query($db, "SELECT * FROM unidades_competencia WHERE codigo='$codigo'");
            $fila = mysqli_fetch_assoc($result);
            if (!$fila) {
                throw new Exception('Unidad no encontrada');
            }
            closeDBConnection($db);
            sendJSONSuccess($fila);
            break;

        // Inserta o actualiza una unidad de competencia
        case 'guardar_unidad':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigo = $datos['codigo'];
            $texto = $datos['texto'];
            $id = isset($datos['id']) && !empty($datos['id']) ? intval($datos['id']) : 0;
            if (empty($codigo) || empty($texto)) {
                throw new Exception('Datos incompletos para guardar la unidad');
            }
            $codigo = mysqli_real_escape_string($db, $codigo);
            $texto = mysqli_real_escape_string($db, $texto);
            if ($id > 0) {
                $query = "UPDATE unidades_competencia SET codigo='$codigo', texto='$texto' WHERE codigo='$codigo'";
            } else {
                $query = "INSERT INTO unidades_competencia (codigo, texto) VALUES ('$codigo', '$texto')";
            }
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess($id > 0 ? array('codigo' => $codigo) : array('codigo' => $codigo), 'Unidad de competencia guardada');
            break;

        // Elimina una unidad de competencia (solo si no está asociada)
        case 'eliminar_unidad':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigo = isset($datos['codigo']) ? $datos['codigo'] : '';
            if (empty($codigo)) {
                throw new Exception('Código de unidad inválido');
            }
            $codigo = mysqli_real_escape_string($db, $codigo);
            $result = mysqli_query($db, "SELECT COUNT(*) AS total FROM cualificaciones_unidades WHERE codigoUnidad='$codigo'");
            $fila = mysqli_fetch_assoc($result);
            if ($fila['total'] > 0) {
                closeDBConnection($db);
                sendJSONError('La unidad está asociada a alguna cualificación');
            }
            if (!mysqli_query($db, "DELETE FROM unidades_competencia WHERE codigo='$codigo'")) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Unidad de competencia eliminada');
            break;

        // Lista las unidades asociadas a una cualificación
        case 'listar_asociaciones':
            $codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
            if (empty($codigo)) {
                throw new Exception('Código de cualificación inválido');
            }
            $codigo = mysqli_real_escape_string($db, $codigo);
            $sql = "SELECT cu.codigoUnidad, uc.texto AS texto
                    FROM cualificaciones_unidades cu
                    JOIN unidades_competencia uc ON uc.codigo = cu.codigoUnidad
                    WHERE cu.codigoCualificacion='$codigo'
                    ORDER BY cu.codigoUnidad";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $asociaciones = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $asociaciones[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($asociaciones);
            break;

        // Asocia una unidad a una cualificación
        case 'guardar_asociacion':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigoCualificacion = isset($datos['codigoCualificacion']) ? $datos['codigoCualificacion'] : '';
            $codigoUnidad = isset($datos['codigoUnidad']) ? $datos['codigoUnidad'] : '';
            if (empty($codigoCualificacion) || empty($codigoUnidad)) {
                throw new Exception('Datos incompletos para asociar la unidad');
            }
            $codigoCualificacion = mysqli_real_escape_string($db, $codigoCualificacion);
            $codigoUnidad = mysqli_real_escape_string($db, $codigoUnidad);
            $query = "INSERT INTO cualificaciones_unidades (codigoCualificacion, codigoUnidad) VALUES ('$codigoCualificacion', '$codigoUnidad')";
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Unidad de competencia asociada');
            break;

        // Disocia una unidad de una cualificación
        case 'eliminar_asociacion':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigoCualificacion = isset($datos['codigoCualificacion']) ? $datos['codigoCualificacion'] : '';
            $codigoUnidad = isset($datos['codigoUnidad']) ? $datos['codigoUnidad'] : '';
            if (empty($codigoCualificacion) || empty($codigoUnidad)) {
                throw new Exception('Datos incompletos para disociar la unidad');
            }
            $codigoCualificacion = mysqli_real_escape_string($db, $codigoCualificacion);
            $codigoUnidad = mysqli_real_escape_string($db, $codigoUnidad);
            if (!mysqli_query($db, "DELETE FROM cualificaciones_unidades WHERE codigoCualificacion='$codigoCualificacion' AND codigoUnidad='$codigoUnidad'")) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Unidad de competencia desasociada');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
