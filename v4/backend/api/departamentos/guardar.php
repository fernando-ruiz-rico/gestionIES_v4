<?php
// API endpoint para insertar o actualizar un departamento
// Requiere sesión iniciada y rol de admin
// Recibe: nombre (requerido), id (opcional - si existe actualiza, si no inserta)

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../../config.php';

// Solo admin (fiel a v3)
checkPermission(array(ROLE_ADMIN));

if (empty($_POST['nombre'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre del departamento es requerido']);
    exit;
}

$nombre = $_POST['nombre'];
$id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;

try {
    $db = Db::open();
    if ($id === null) {
        // Insertar nuevo departamento
        $db->execute("INSERT INTO departamentos (nombre) VALUES (?)", $nombre);
        $idNuevo = $db->insertId();
    } else {
        // Actualizar departamento existente
        $db->execute("UPDATE departamentos SET nombre = ? WHERE id = ?", $nombre, $id);
        $idNuevo = $id;
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

if ($id === null) {
    echo json_encode(['success' => true, 'id' => (int)$idNuevo, 'mensaje' => 'Departamento creado correctamente']);
} else {
    echo json_encode(['success' => true, 'mensaje' => 'Departamento actualizado correctamente']);
}
?>
