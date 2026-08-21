<?php
/**
 * API para la gestión de Competencias por Ciclo (Fase 4.2)
 * Fiel a v3: las competencias se almacenan en competencias_ciclos, una fila
 * por competencia (con su código, texto, tipo e id de ciclo).
 *
 * Acciones:
 *   - listar_ciclos : lista los ciclos disponibles
 *   - listar        : lista las competencias de un ciclo
 *   - obtener       : devuelve una competencia concreta
 *   - guardar       : inserta/actualiza una competencia
 *   - ordenar       : reordena las competencias de un ciclo
 *   - eliminar      : elimina una competencia
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
        // Lista los ciclos para el selector de la vista
        case 'listar_ciclos':
            $sql = "SELECT id, nombre, nivel FROM ciclos ORDER BY nombre";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $ciclos = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $ciclos[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($ciclos);
            break;

        // Lista las competencias de un ciclo
        case 'listar':
            $idCiclo = isset($_GET['idCiclo']) ? intval($_GET['idCiclo']) : 0;
            if ($idCiclo <= 0) {
                throw new Exception('ID de ciclo inválido');
            }
            $sql = "SELECT * FROM competencias_ciclos WHERE idCiclo = $idCiclo ORDER BY orden";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $competencias = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $competencias[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($competencias);
            break;

        // Devuelve una competencia concreta
        case 'obtener':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) {
                throw new Exception('ID de competencia inválido');
            }
            $result = mysqli_query($db, "SELECT * FROM competencias_ciclos WHERE id=$id");
            $fila = mysqli_fetch_assoc($result);
            if (!$fila) {
                throw new Exception('Competencia no encontrada');
            }
            closeDBConnection($db);
            sendJSONSuccess($fila);
            break;

        // Inserta o actualiza una competencia de un ciclo
        case 'guardar':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $id = isset($datos['id']) && !empty($datos['id']) ? intval($datos['id']) : 0;
            $codigo = $datos['codigo'];
            $texto = $datos['texto'];
            $tipo = intval($datos['tipo'] ?? 1);
            $idCiclo = intval($datos['idCiclo']);
            if ($id > 0) {
                $codigo = mysqli_real_escape_string($db, $codigo);
                $texto = mysqli_real_escape_string($db, $texto);
                $query = "UPDATE competencias_ciclos SET codigo='$codigo', texto='$texto', tipo=$tipo WHERE id=$id";
            } else {
                if (empty($codigo) || empty($texto) || $idCiclo <= 0) {
                    throw new Exception('Datos incompletos para guardar la competencia');
                }
                $codigo = mysqli_real_escape_string($db, $codigo);
                $texto = mysqli_real_escape_string($db, $texto);
                $query = "INSERT INTO competencias_ciclos (codigo, texto, tipo, idCiclo) VALUES ('$codigo', '$texto', $tipo, $idCiclo)";
            }
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            $nuevoId = ($id > 0) ? $id : mysqli_insert_id($db);
            closeDBConnection($db);
            sendJSONSuccess(array('id' => $nuevoId), 'Competencia guardada');
            break;

        // Reordena las competencias de un ciclo
        case 'ordenar':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $orden = isset($datos['orden']) ? $datos['orden'] : '';
            $ids = explode(",", $orden);
            foreach ($ids as $pos => $cod) {
                $idComp = intval(substr($cod, 2));
                if ($idComp > 0) {
                    mysqli_query($db, "UPDATE competencias_ciclos SET orden=" . ($pos + 1) . " WHERE id=$idComp");
                }
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Orden actualizado');
            break;

        // Elimina una competencia
        case 'eliminar':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $id = intval($datos['id']);
            if ($id <= 0) {
                throw new Exception('ID de competencia inválido');
            }
            if (!mysqli_query($db, "DELETE FROM competencias_ciclos WHERE id=$id")) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Competencia eliminada');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
