<?php
// API para crear o modificar un curso (Fase 1)
// Equivalente a v3/ajax/cursos/insertar_curso.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
$nombre      = isset($datos['nombre']) ? trim($datos['nombre']) : '';
$abreviatura = isset($datos['abreviatura']) ? trim($datos['abreviatura']) : '';
$horas       = isset($datos['horas_semana']) ? intval($datos['horas_semana']) : 0;
$categoria   = isset($datos['categoria']) ? trim($datos['categoria']) : '';
$id          = isset($datos['id']) ? intval($datos['id']) : 0;

if ($nombre === '' || $abreviatura === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos obligatorios (nombre y abreviatura)']);
    exit;
}

// En v3 el campo "horas semanales" puede llegar vacío; en ese caso se guarda 0
try {
    $db = Db::open();
    if ($id > 0) {
        $db->execute("UPDATE cursos SET nombre = ?, abreviatura = ?, horas_semana = ?, categoria = ? WHERE id = ?", $nombre, $abreviatura, $horas, $categoria, $id);
        $nuevoId = $id;
    } else {
        $db->execute("INSERT INTO cursos (nombre, abreviatura, horas_semana, categoria) VALUES (?, ?, ?, ?)", $nombre, $abreviatura, $horas, $categoria);
        $nuevoId = $db->insertId();
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Curso guardado correctamente',
    'id' => (int)$nuevoId
]);
?>
