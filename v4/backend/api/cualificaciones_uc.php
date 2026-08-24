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

require_once '../config.php';
cabeceraJson();
@session_start();
 $datos = cuerpoJson();

$method = $_SERVER['REQUEST_METHOD'];
$action = getOptimo('action');

try {
    $db = Db::open();

    switch ($action) {
        // Lista las cualificaciones profesionales
        case 'listar_cualificaciones':
            $sql = "SELECT codigo, texto FROM cualificaciones_profesionales ORDER BY codigo";
            $cualificaciones = $db->fetchAll($sql);
            $db->close();
            sendJSONSuccess($cualificaciones);
            break;

        // Devuelve una cualificación
        case 'obtener_cualificacion':
            $codigo = getOptimo('codigo');
            if (empty($codigo)) {
                throw new Exception('Código de cualificación inválido');
            }
            $fila = $db->fetchOne("SELECT * FROM cualificaciones_profesionales WHERE codigo=?", $codigo);
            if (!$fila) {
                $db->close();
                sendJSONError('Cualificación no encontrada', 404);
            }
            $db->close();
            sendJSONSuccess($fila);
            break;

        // Inserta o actualiza una cualificación profesional
        case 'guardar_cualificacion':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigo = $datos['codigo'];
            $texto = $datos['texto'];
            // Fiel a v3: "idCualificacion" es el código ANTERIOR (clave de edición),
            // no la llave primaria de la tabla.
            $id = trim(datosOptimo($datos, 'id'));
            if (empty($codigo) || empty($texto)) {
                throw new Exception('Datos incompletos para guardar la cualificación');
            }
            if ($id !== '') {
                $query = "UPDATE cualificaciones_profesionales SET codigo=?, texto=? WHERE codigo=?";
                $db->execute($query, $codigo, $texto, $id);
                // Si el código ha cambiado, las unidades asociadas siguen al nuevo (v3)
                $query = "UPDATE cualificaciones_unidades SET codigoCualificacion=? WHERE codigoCualificacion=?";
                $db->execute($query, $codigo, $id);
            } else {
                $query = "INSERT INTO cualificaciones_profesionales (codigo, texto) VALUES (?, ?)";
                $db->execute($query, $codigo, $texto);
            }
            $db->close();
            sendJSONSuccess(array('codigo' => $codigo), 'Cualificación guardada');
            break;

        // Elimina una cualificación (solo si no tiene UC asociadas)
        case 'eliminar_cualificacion':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigo = datosOptimo($datos, 'codigo');
            if (empty($codigo)) {
                throw new Exception('Código de cualificación inválido');
            }
            $fila = $db->fetchOne("SELECT COUNT(*) AS total FROM cualificaciones_unidades WHERE codigoCualificacion=?", $codigo);
            if ($fila['total'] > 0) {
                $db->close();
                sendJSONError('La cualificación tiene unidades de competencia asociadas');
            }
            $db->execute("DELETE FROM cualificaciones_profesionales WHERE codigo=?", $codigo);
            $db->close();
            sendJSONSuccess(null, 'Cualificación eliminada');
            break;

        // Lista las unidades de competencia
        case 'listar_unidades':
            $sql = "SELECT codigo, texto FROM unidades_competencia ORDER BY codigo";
            $unidades = $db->fetchAll($sql);
            $db->close();
            sendJSONSuccess($unidades);
            break;

        // Devuelve una unidad de competencia
        case 'obtener_unidad':
            $codigo = getOptimo('codigo');
            if (empty($codigo)) {
                throw new Exception('Código de unidad inválido');
            }
            $fila = $db->fetchOne("SELECT * FROM unidades_competencia WHERE codigo=?", $codigo);
            if (!$fila) {
                $db->close();
                sendJSONError('Unidad no encontrada', 404);
            }
            $db->close();
            sendJSONSuccess($fila);
            break;

        // Inserta o actualiza una unidad de competencia
        case 'guardar_unidad':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigo = $datos['codigo'];
            $texto = $datos['texto'];
            // Fiel a v3: "idUnidad" es el código ANTERIOR (clave de edición).
            $id = trim(datosOptimo($datos, 'id'));
            if (empty($codigo) || empty($texto)) {
                throw new Exception('Datos incompletos para guardar la unidad');
            }
            if ($id !== '') {
                $query = "UPDATE unidades_competencia SET codigo=?, texto=? WHERE codigo=?";
                $db->execute($query, $codigo, $texto, $id);
                // Si el código ha cambiado, las asociaciones siguen al nuevo (v3)
                $query = "UPDATE unidades_ciclos SET codigoUnidad=? WHERE codigoUnidad=?";
                $db->execute($query, $codigo, $id);
            } else {
                $query = "INSERT INTO unidades_competencia (codigo, texto) VALUES (?, ?)";
                $db->execute($query, $codigo, $texto);
            }
            $db->close();
            sendJSONSuccess(array('codigo' => $codigo), 'Unidad de competencia guardada');
            break;

        // Elimina una unidad de competencia (solo si no está asociada)
        case 'eliminar_unidad':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigo = datosOptimo($datos, 'codigo');
            if (empty($codigo)) {
                throw new Exception('Código de unidad inválido');
            }
            $fila = $db->fetchOne("SELECT COUNT(*) AS total FROM cualificaciones_unidades WHERE codigoUnidad=?", $codigo);
            if ($fila['total'] > 0) {
                $db->close();
                sendJSONError('La unidad está asociada a alguna cualificación');
            }
            $db->execute("DELETE FROM unidades_competencia WHERE codigo=?", $codigo);
            $db->close();
            sendJSONSuccess(null, 'Unidad de competencia eliminada');
            break;

        // Lista las unidades asociadas a una cualificación
        case 'listar_asociaciones':
            $codigo = getOptimo('codigo');
            if (empty($codigo)) {
                throw new Exception('Código de cualificación inválido');
            }
            $sql = "SELECT cu.codigoUnidad, uc.texto AS texto
                    FROM cualificaciones_unidades cu
                    JOIN unidades_competencia uc ON uc.codigo = cu.codigoUnidad
                    WHERE cu.codigoCualificacion=?
                    ORDER BY cu.codigoUnidad";
            $asociaciones = $db->fetchAll($sql, $codigo);
            $db->close();
            sendJSONSuccess($asociaciones);
            break;

        // Asocia una unidad a una cualificación
        case 'guardar_asociacion':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigoCualificacion = datosOptimo($datos, 'codigoCualificacion');
            $codigoUnidad = datosOptimo($datos, 'codigoUnidad');
            if (empty($codigoCualificacion) || empty($codigoUnidad)) {
                throw new Exception('Datos incompletos para asociar la unidad');
            }
            $query = "INSERT INTO cualificaciones_unidades (codigoCualificacion, codigoUnidad) VALUES (?, ?)";
            $db->execute($query, $codigoCualificacion, $codigoUnidad);
            $db->close();
            sendJSONSuccess(null, 'Unidad de competencia asociada');
            break;

        // Disocia una unidad de una cualificación
        case 'eliminar_asociacion':
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $codigoCualificacion = datosOptimo($datos, 'codigoCualificacion');
            $codigoUnidad = datosOptimo($datos, 'codigoUnidad');
            if (empty($codigoCualificacion) || empty($codigoUnidad)) {
                throw new Exception('Datos incompletos para disociar la unidad');
            }
            $db->execute("DELETE FROM cualificaciones_unidades WHERE codigoCualificacion=? AND codigoUnidad=?", $codigoCualificacion, $codigoUnidad);
            $db->close();
            sendJSONSuccess(null, 'Unidad de competencia desasociada');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
