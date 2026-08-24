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

require_once '../config.php';
cabeceraJson();
@session_start();
 $datos = cuerpoJson();

$method = $_SERVER['REQUEST_METHOD'];
$action = getOptimo('action');

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
    $db = Db::open();

    switch ($action) {
        // Lista las actas de un departamento (más reciente primero)
        case 'listar':
            $idDepartamento = getOptimoInt('idDepartamento');
            if ($idDepartamento <= 0) {
                throw new Exception('ID de departamento inválido');
            }
            $actas = $db->fetchAll("SELECT id, fecha FROM actas_departamentos WHERE idDepartamento=? ORDER BY fecha DESC", $idDepartamento);
            sendJSONSuccess($actas);
            break;

        // Devuelve el texto y fecha de un acta
        case 'obtener':
            $idActa = getOptimoInt('idActa');
            if ($idActa <= 0) {
                throw new Exception('ID de acta inválido');
            }
            $fila = $db->fetchOne("SELECT texto, fecha FROM actas_departamentos WHERE id=?", $idActa);
            if (!$fila) {
                sendJSONError('Acta no encontrada', 404);
            }
            sendJSONSuccess($fila);
            break;

        // Inserta o actualiza un acta de departamento
        case 'guardar':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $idActa = datosOptimoInt($datos, 'idActa');
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
                $db->execute("UPDATE actas_departamentos SET texto=?, fecha=? WHERE id=?", $texto, $fechaForm, $idActa);
            } else {
                $db->execute("INSERT INTO actas_departamentos (idDepartamento, texto, fecha) VALUES (?, ?, ?)", $idDepartamento, $texto, $fechaForm);
            }
            $nuevoId = ($idActa > 0) ? $idActa : $db->insertId();
            sendJSONSuccess(array('id' => $nuevoId), 'Acta guardada');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
