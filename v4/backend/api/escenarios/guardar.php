<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = json_decode(file_get_contents('php://input'), true);
$nombre = trim(isset($datos['nombre']) ? $datos['nombre'] : '');
$id = isset($datos['id']) ? intval($datos['id']) : 0;
$actual = isset($datos['actual']) ? intval($datos['actual']) : 0;
$activo_desideratas = isset($datos['activo_desideratas']) ? intval($datos['activo_desideratas']) : 0;
$modo_rueda = isset($datos['modo_rueda']) ? intval($datos['modo_rueda']) : 0;

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre obligatorio']);
    exit;
}

// La tabla real para escenarios es 'escenarios_desideratas'
try {
    $db = Db::open();
    if ($id > 0) {
        $db->execute("UPDATE escenarios_desideratas SET nombre=?, actual=?, activo_desideratas=?, modo_rueda=? WHERE id=?", $nombre, $actual, $activo_desideratas, $modo_rueda, $id);
    } else {
        $db->execute("INSERT INTO escenarios_desideratas (nombre, actual, activo_desideratas, modo_rueda) VALUES (?, ?, ?, ?)", $nombre, $actual, $activo_desideratas, $modo_rueda);
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Guardado']);
?>
