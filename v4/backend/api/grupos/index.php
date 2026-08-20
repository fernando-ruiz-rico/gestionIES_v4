<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            if ($action === 'listar') {
                $result = mysqli_query($db, "SELECT * FROM grupos ORDER BY nombre");
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
                $grupos = [];
                while ($fila = mysqli_fetch_assoc($result)) {
                    $grupos[] = $fila;
                }
                mysqli_free_result($result);
                echo json_encode(['success' => true, 'data' => $grupos]);
            } elseif ($action === 'obtener' && isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $result = mysqli_query($db, "SELECT * FROM grupos WHERE id = $id");
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
                $data = mysqli_fetch_assoc($result);
                mysqli_free_result($result);
                
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
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (isset($data['id']) && $data['id'] > 0) {
                // Actualizar
                $nombre = mysqli_real_escape_string($db, $data['nombre']);
                $cicloFormativo = mysqli_real_escape_string($db, $data['cicloFormativo']);
                $cursoAcademico = mysqli_real_escape_string($db, $data['cursoAcademico']);
                $id = intval($data['id']);
                $sql = "UPDATE grupos SET nombre = '$nombre', cicloFormativo = '$cicloFormativo', cursoAcademico = '$cursoAcademico' WHERE id = $id";
                $result = mysqli_query($db, $sql);
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
                $idRetorno = $id;
            } else {
                // Crear
                $nombre = mysqli_real_escape_string($db, $data['nombre']);
                $cicloFormativo = mysqli_real_escape_string($db, $data['cicloFormativo']);
                $cursoAcademico = mysqli_real_escape_string($db, $data['cursoAcademico']);
                $sql = "INSERT INTO grupos (nombre, cicloFormativo, cursoAcademico) VALUES ('$nombre', '$cicloFormativo', '$cursoAcademico')";
                $result = mysqli_query($db, $sql);
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
                $idRetorno = mysqli_insert_id($db);
            }
            
            echo json_encode(['success' => true, 'id' => $idRetorno]);
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $result = mysqli_query($db, "DELETE FROM grupos WHERE id = $id");
                if (!$result) {
                    throw new Exception(mysqli_error($db));
                }
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
} finally {
    closeDBConnection($db);
}
?>
