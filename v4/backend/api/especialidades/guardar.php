<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$id = trim(isset($datos['id']) ? $datos['id'] : '');
$descripcion = trim(isset($datos['descripcion']) ? $datos['descripcion'] : '');
$idDepartamento = intval(isset($datos['idDepartamento']) ? $datos['idDepartamento'] : 0);
$horasTutoria = intval(isset($datos['horasTutoria']) ? $datos['horasTutoria'] : 0);
$horasIngles = intval(isset($datos['horasIngles']) ? $datos['horasIngles'] : 0);

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID obligatorio']);
    exit;
}

if (empty($descripcion)) {
    http_response_code(400);
    echo json_encode(['error' => 'Descripción obligatoria']);
    exit;
}

try {
    $db = Db::open();

    // Verificar si existe para actualizar o insertar
    $existe = $db->fetchOne("SELECT id FROM especialidades WHERE id = ?", $id);

    if ($existe) {
        $db->execute("UPDATE especialidades SET descripcion=?, idDepartamento=?, horasTutoria=?, horasIngles=? WHERE id=?", $descripcion, $idDepartamento, $horasTutoria, $horasIngles, $id);
    } else {
        $db->execute("INSERT INTO especialidades (id, descripcion, idDepartamento, horasTutoria, horasIngles) VALUES (?, ?, ?, ?, ?)", $id, $descripcion, $idDepartamento, $horasTutoria, $horasIngles);
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Guardado correctamente'
]);
?>
