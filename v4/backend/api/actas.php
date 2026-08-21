<?php
/**
 * API para la gestión de Actas de departamentos (Fase 6.1)
 * Fiel a v3: las actas de departamento se almacenan en actas_departamentos,
 * una fila por acta (con su texto y fecha).
 *
 * Acciones:
 *   - listar   : lista las actas de un departamento (más reciente primero)
 *   - obtener  : devuelve el texto y fecha de un acta
 *   - guardar  : inserta/actualiza un acta
 *
 * Permisos:
 *   - Los admins pueden trabajar sobre cualquier departamento.
 *   - Los jefes de departamento solo sobre su propio departamento.
 *   - Los profesores solo pueden revisar las actas de su departamento.
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

// Permisos de edición (admin o jefe de departamento)
function tienePermisoEdicion()
{
    return isset($_SESSION['rol']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'jefeDepartamento');
}

// El usuario puede editar solo si sea admin, o jefe de del departamento actual
function puedeEditarDepartamento($idDepartamento)
{
    if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') {
        return true;
    }
    if (isset($_SESSION['departamentoUsuario'])) {
        return intval($_SESSION['departamentoUsuario']) == intval($idDepartamento);
    }
    return false;
}

try {
    switch ($action) {
        // Lista las actas de un departamento (más reciente primero)
        case 'listar':
            $idDepartamento = isset($_GET['idDepartamento']) ? intval($_GET['idDepartamento']) : 0;
            if ($idDepartamento <= 0) {
                throw new Exception('ID de departamento inválido');
            }
            $sql = "SELECT id, fecha FROM actas_departamentos WHERE idDepartamento=$idDepartamento ORDER BY fecha DESC";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $actas = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $actas[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($actas);
            break;

        // Devuelve el texto y fecha de un acta
        case 'obtener':
            $idActa = isset($_GET['idActa']) ? intval($_GET['idActa']) : 0;
            if ($idActa <= 0) {
                throw new Exception('ID de acta inválido');
            }
            $result = mysqli_query($db, "SELECT texto, fecha FROM actas_departamentos WHERE id=$idActa");
            $fila = mysqli_fetch_assoc($result);
            if (!$fila) {
                throw new Exception('Acta no encontrada');
            }
            closeDBConnection($db);
            sendJSONSuccess($fila);
            break;

        // Inserta o actualiza un acta de departamento
        case 'guardar':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $idActa = isset($datos['idActa']) && !empty($datos['idActa']) ? intval($datos['idActa']) : 0;
            $idDepartamento = intval($datos['idDepartamento']);
            $texto = $datos['texto'];
            $fecha = $datos['fecha'];
            if ($idDepartamento <= 0 || empty($texto) || empty($fecha)) {
                throw new Exception('Datos incompletos para guardar el acta');
            }
            if (!puedeEditarDepartamento($idDepartamento)) {
                throw new Exception('No tiene permisos sobre este departamento');
            }
            // La fecha llega en formato d/m/Y (o, en algunos casos, ya Y-m-d);
            // la normalizamos a Y-m-d de forma tolerante.
            $fechaForm = $fecha;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                $ts = strtotime($fecha);
            } elseif (($dt = DateTime::createFromFormat('d/m/Y', $fecha)) !== false) {
                $ts = $dt->getTimestamp();
            } else {
                $ts = strtotime(str_replace('/', '-', $fecha));
            }
            if ($ts === false || $ts === -1) {
                throw new Exception('Fecha no válida: ' . $fecha);
            }
            $fechaForm = date('Y-m-d', $ts);
            if ($idActa > 0) {
                $texto = mysqli_real_escape_string($db, $texto);
                $fecha = mysqli_real_escape_string($db, $fechaForm);
                $query = "UPDATE actas_departamentos SET texto='$texto', fecha='$fecha' WHERE id=$idActa";
            } else {
                $texto = mysqli_real_escape_string($db, $texto);
                $fecha = mysqli_real_escape_string($db, $fechaForm);
                $query = "INSERT INTO actas_departamentos (idDepartamento, texto, fecha) VALUES ($idDepartamento, '$texto', '$fecha')";
            }
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            $nuevoId = ($idActa > 0) ? $idActa : mysqli_insert_id($db);
            closeDBConnection($db);
            sendJSONSuccess(array('id' => $nuevoId), 'Acta guardada');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
