<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'listar') {
                $db = getDBConnection();
                if (!$db) {
                    throw new Exception('Error de conexión');
                }
                
                $result = mysqli_query($db, "SELECT * FROM grupos ORDER BY nombre");
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
                
                $grupos = [];
                while ($fila = mysqli_fetch_assoc($result)) {
                    $grupos[] = $fila;
                }
                mysqli_free_result($result);
                mysqli_close($db);
                
                echo json_encode(['success' => true, 'data' => $grupos]);
            } elseif ($action === 'obtener' && isset($_GET['id'])) {
                $db = getDBConnection();
                if (!$db) {
                    throw new Exception('Error de conexión');
                }
                
                $id = intval($_GET['id']);
                $stmt = mysqli_prepare($db, "SELECT * FROM grupos WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $data = mysqli_fetch_assoc($result);
                
                mysqli_free_result($result);
                mysqli_close($db);
                
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
            $db = getDBConnection();
            
            if (!$db) {
                throw new Exception('Error de conexión');
            }
            
            if (isset($data['id']) && $data['id'] > 0) {
                // Actualizar
                $stmt = mysqli_prepare($db, "UPDATE grupos SET nombre = ?, cicloFormativo = ?, cursoAcademico = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "ssii", $data['nombre'], $data['cicloFormativo'], $data['cursoAcademico'], $data['id']);
                mysqli_stmt_execute($stmt);
                $id = $data['id'];
            } else {
                // Crear
                $stmt = mysqli_prepare($db, "INSERT INTO grupos (nombre, cicloFormativo, cursoAcademico) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssi", $data['nombre'], $data['cicloFormativo'], $data['cursoAcademico']);
                mysqli_stmt_execute($stmt);
                $id = mysqli_insert_id($db);
            }
            
            mysqli_close($db);
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $db = getDBConnection();
                if (!$db) {
                    throw new Exception('Error de conexión');
                }
                
                $id = intval($_GET['id']);
                $stmt = mysqli_prepare($db, "DELETE FROM grupos WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                
                mysqli_close($db);
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
