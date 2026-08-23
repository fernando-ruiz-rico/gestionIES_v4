<?php
// API para crear o modificar un ciclo formativo (Fase 1)
// Equivalente a v3/ajax/ciclos/insertar_ciclo.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$nombre   = isset($datos['nombre']) ? trim($datos['nombre']) : '';
$familia  = isset($datos['familia']) ? trim($datos['familia']) : '';
$nivel   = isset($datos['nivel']) ? trim($datos['nivel']) : '';
$idCiclo = isset($datos['id']) ? intval($datos['id']) : 0;

if ($nombre === '' || $familia === '' || $nivel === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos obligatorios (nombre, familia y nivel)']);
    exit;
}

try {
    $db = Db::open();
    if ($idCiclo > 0) {
        $db->execute("UPDATE ciclos SET nombre = ?, familia = ?, nivel = ? WHERE id = ?", $nombre, $familia, $nivel, $idCiclo);
        $nuevoId = $idCiclo;
    } else {
        // La columna "horas" es NOT NULL sin valor por defecto en la tabla;
        // v3 no la pide en el formulario, así que se guarda 0
        $db->execute("INSERT INTO ciclos (nombre, familia, nivel, horas) VALUES (?, ?, ?, 0)", $nombre, $familia, $nivel);
        $nuevoId = $db->insertId();
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Ciclo guardado correctamente',
    'id' => (int)$nuevoId
]);
?>
