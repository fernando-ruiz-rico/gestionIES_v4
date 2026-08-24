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
$action = getOptimo('action');

$db = Db::open();

try {
    switch ($action) {
        // Lista los ciclos para el selector de la vista
        case 'listar_ciclos':
            $sql = "SELECT id, nombre, nivel FROM ciclos ORDER BY nombre";
            $ciclos = $db->fetchAll($sql);
            sendJSONSuccess($ciclos);
            break;

        // Lista las competencias de un ciclo
        case 'listar':
            $idCiclo = getOptimoInt('idCiclo');
            if ($idCiclo <= 0) {
                throw new Exception('ID de ciclo inválido');
            }
            $sql = "SELECT * FROM competencias_ciclos WHERE idCiclo = ? ORDER BY orden";
            $competencias = $db->fetchAll($sql, $idCiclo);
            sendJSONSuccess($competencias);
            break;

        // Devuelve una competencia concreta
        case 'obtener':
            $id = getOptimoInt('id');
            if ($id <= 0) {
                throw new Exception('ID de competencia inválido');
            }
            $fila = $db->fetchOne("SELECT * FROM competencias_ciclos WHERE id=?", $id);
            if (!$fila) {
                sendJSONError('Competencia no encontrada', 404);
            }
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
            $tipo = intval(isset($datos['tipo']) ? $datos['tipo'] : 1);
            $idCiclo = intval($datos['idCiclo']);
            if ($id > 0) {
                $db->execute("UPDATE competencias_ciclos SET codigo=?, texto=?, tipo=? WHERE id=?", $codigo, $texto, $tipo, $id);
            } else {
                if (empty($codigo) || empty($texto) || $idCiclo <= 0) {
                    throw new Exception('Datos incompletos para guardar la competencia');
                }
                // "orden" es NOT NULL sin valor por defecto; v3 no la pedía, así que la ponemos al final de la lista
                $filaMax = $db->fetchOne("SELECT MAX(orden) AS maxo FROM competencias_ciclos WHERE idCiclo = ?", $idCiclo);
                $orden = ($filaMax && $filaMax['maxo'] !== null) ? intval($filaMax['maxo']) + 1 : 1;
                $db->execute("INSERT INTO competencias_ciclos (codigo, texto, tipo, idCiclo, orden) VALUES (?, ?, ?, ?, ?)", $codigo, $texto, $tipo, $idCiclo, $orden);
            }
            $nuevoId = ($id > 0) ? $id : $db->insertId();
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
                    $db->execute("UPDATE competencias_ciclos SET orden=? WHERE id=?", $pos + 1, $idComp);
                }
            }
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
            $db->execute("DELETE FROM competencias_ciclos WHERE id=?", $id);
            sendJSONSuccess(null, 'Competencia eliminada');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}