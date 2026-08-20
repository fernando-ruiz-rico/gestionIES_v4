<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    $pdo = getPDOConnection();
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    switch ($method) {
        case 'GET':
            if ($action === 'listar') {
                $stmt = $pdo->query("SELECT * FROM grupos ORDER BY nombre");
                $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $grupos]);
            } elseif ($action === 'obtener' && isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM grupos WHERE id = ?");
                $stmt->execute([intval($_GET['id'])]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($data) {
                    echo json_encode(['success' => true, 'data' => $data]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Grupo no encontrado']);
                }
            } else {
                throw new Exception('Acción no válida');
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['id']) && $data['id'] > 0) {
                // Actualizar
                $stmt = $pdo->prepare("UPDATE grupos SET nombre = ?, cicloFormativo = ?, cursoAcademico = ? WHERE id = ?");
                $stmt->execute([$data['nombre'], $data['cicloFormativo'], $data['cursoAcademico'], $data['id']]);
                $id = $data['id'];
            } else {
                // Crear
                $stmt = $pdo->prepare("INSERT INTO grupos (nombre, cicloFormativo, cursoAcademico) VALUES (?, ?, ?)");
                $stmt->execute([$data['nombre'], $data['cicloFormativo'], $data['cursoAcademico']]);
                $id = $pdo->lastInsertId();
            }
            
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM grupos WHERE id = ?");
                $stmt->execute([intval($_GET['id'])]);
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('ID no proporcionado');
            }
            break;

        default:
            throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
