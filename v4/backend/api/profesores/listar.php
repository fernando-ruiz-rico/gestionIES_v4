<?php
// API endpoint para cargar todos los profesores de un departamento
// Recibe: idDepartamento (requerido)
// Devuelve: array JSON con los profesores ordenados por campo 'orden'

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

if (empty($_GET['idDepartamento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de departamento no proporcionado']);
    exit;
}

$idDepartamento = intval($_GET['idDepartamento']);
$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    $db = new Db($conn);
    $profesores = $db->fetchAll("SELECT * FROM profesores WHERE idDepartamento = $idDepartamento ORDER BY orden");
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode($profesores);
?>
