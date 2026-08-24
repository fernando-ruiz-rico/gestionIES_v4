<?php
/**
 * API para la Configuración de la aplicación (Fase 7.3)
 * Fiel a v3: expone la tabla 'config' para establecer los parámetros de
 * configuración: contraseña del administrador y activación de los periodos
 * (desideratas / programaciones).
 *
 * Acciones:
 *   - obtener              : devuelve el estado de configuración
 *   - actualizar_password  : cambia la contraseña del administrador
 *   - actualizar_activacion : activa/desactiva un período
 *
 * Permisos: solo el rol admin.
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$datos = json_decode(file_get_contents("php://input"), true) ?: [];

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

// Consulta un valor de la tabla config
function getConfigValue($db, $clave)
{
    $fila = $db->fetchOne("SELECT valor FROM config WHERE clave = ?", $clave);
    return $fila ? $fila['valor'] : null;
}

try {
    $db = Db::open();

    switch ($action) {
        // Devuelve el estado de configuración.
        // El frontend espera {data: {activaciones: {evaluacionRA, seleccion}}};
        // la evaluación de RA la controla la fila "programaciones" y la
        // selección de materias la fila "desideratas" (mismo modelo que v3).
        case 'obtener':
            $programaciones = getConfigValue($db, 'programaciones');
            $desideratas = getConfigValue($db, 'desideratas');
            $db->close();
            sendJSONSuccess(array(
                'activaciones' => array(
                    'evaluacionRA' => ($programaciones === 'ON'),
                    'seleccion'    => ($desideratas === 'ON')
                )
            ));
            break;

        // Cambia la contraseña del administrador
        case 'actualizar_password':
            $antiguo = isset($datos['passwordActual']) ? $datos['passwordActual'] : '';
            $nuevo = isset($datos['nuevaPassword']) ? $datos['nuevaPassword'] : '';
            $repetirNuevo = isset($datos['passwordConfirmacion']) ? $datos['passwordConfirmacion'] : '';
            if ($nuevo !== $repetirNuevo) {
                throw new Exception('La nueva contraseña y la repetición no coinciden');
            }
            $afectadas = $db->execute("UPDATE config SET valor=md5(?) WHERE clave='admin' AND valor=md5(?)", $nuevo, $antiguo);
            if ($afectadas == 0) {
                throw new Exception('La contraseña antigua no es correcta');
            }
            $db->close();
            sendJSONSuccess(null, 'Contraseña de administrador actualizada');
            break;

        // Activa/desactiva un período.
        // El frontend envía "evaluacionRA" (fila programaciones) o "seleccion"
        // (fila desideratas); también aceptamos los nombres de las filas.
        case 'actualizar_activacion':
            $clave = isset($datos['clave']) ? $datos['clave'] : '';
            $valor = isset($datos['valor']) ? $datos['valor'] : '';
            $claves = array('evaluacionRA' => 'programaciones', 'seleccion' => 'desideratas', 'programaciones' => 'programaciones', 'desideratas' => 'desideratas');
            if (!isset($claves[$clave])) {
                throw new Exception('Clave de activación no válida');
            }
            $clave = $claves[$clave];
            // Aceptamos el valor como booleano JSON o como texto ON/OFF
            if ($valor === true || $valor === 'ON' || $valor === '1' || $valor === 'true') {
                $activo = 'ON';
            } elseif ($valor === false || $valor === 'OFF' || $valor === '0' || $valor === 'false') {
                $activo = 'OFF';
            } else {
                throw new Exception('Valor de activación no válido');
            }
            $db->execute("UPDATE config SET valor=? WHERE clave=?", $activo, $clave);
            $db->close();
            sendJSONSuccess(array('clave' => $clave, 'valor' => $activo), 'Activación actualizada');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
