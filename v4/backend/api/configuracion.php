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

$db = getDBConnection();
 $datos = json_decode(file_get_contents("php://input"), true) ?: [];
if (!$db) {
    sendJSONError('Error de conexión a la base de datos');
}

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

// Consulta un valor de la tabla config
function getConfigValue($db, $clave)
{
    $result = mysqli_query($db, "SELECT valor FROM config WHERE clave='" . mysqli_real_escape_string($db, $clave) . "'");
    $fila = $result ? mysqli_fetch_assoc($result) : null;
    return $fila ? $fila['valor'] : null;
}

try {
    switch ($action) {
        // Devuelve el estado de configuración.
        // El frontend espera {data: {activaciones: {evaluacionRA, seleccion}}};
        // la evaluación de RA la controla la fila "programaciones" y la
        // selección de materias la fila "desideratas" (mismo modelo que v3).
        case 'obtener':
            $programaciones = getConfigValue($db, 'programaciones');
            $desideratas = getConfigValue($db, 'desideratas');
            closeDBConnection($db);
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
            $nuevo = mysqli_real_escape_string($db, $nuevo);
            $antiguo = mysqli_real_escape_string($db, $antiguo);
            $result = mysqli_query($db, "UPDATE config SET valor='" . md5($nuevo) . "' WHERE clave='admin' AND valor='" . md5($antiguo) . "'");
            if (!$result || mysqli_affected_rows($db) == 0) {
                throw new Exception('La contraseña antigua no es correcta');
            }
            closeDBConnection($db);
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
            $result = mysqli_query($db, "UPDATE config SET valor='" . $activo . "' WHERE clave='" . $clave . "'");
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(array('clave' => $clave, 'valor' => $activo), 'Activación actualizada');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
