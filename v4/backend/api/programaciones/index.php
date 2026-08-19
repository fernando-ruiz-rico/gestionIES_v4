<?php
header('Content-Type: application/json');
require_once '../../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'listar') {
                $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
                
                if ($idMateria > 0) {
                    $sql = "SELECT p.*, m.titulo as materia, g.nombre as grupo 
                            FROM programaciones p 
                            LEFT JOIN materias m ON p.idMateria = m.id 
                            LEFT JOIN grupos g ON p.idGrupo = g.id 
                            WHERE p.idMateria = ? 
                            ORDER BY p.curso DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$idMateria]);
                } else {
                    $sql = "SELECT p.*, m.titulo as materia, g.nombre as grupo 
                            FROM programaciones p 
                            LEFT JOIN materias m ON p.idMateria = m.id 
                            LEFT JOIN grupos g ON p.idGrupo = g.id 
                            ORDER BY p.curso DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                }
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            } elseif ($action === 'obtener' && isset($_GET['id'])) {
                $sql = "SELECT * FROM programaciones WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['id']]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($data) {
                    echo json_encode(['success' => true, 'data' => $data]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Programación no encontrada']);
                }
            } else {
                throw new Exception('Acción no válida');
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['id']) && $data['id'] > 0) {
                // Actualizar
                $sql = "UPDATE programaciones SET 
                        idMateria = ?, 
                        idGrupo = ?, 
                        curso = ?, 
                        anyo = ?, 
                        profesor = ?, 
                        objetivos = ?, 
                        metodologia = ?, 
                        evaluacion = ?, 
                        atencion_diversidad = ?, 
                        materiales = ?, 
                        bibliografia = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['idMateria'],
                    $data['idGrupo'] ?: null,
                    $data['curso'],
                    $data['anyo'] ?: '',
                    $data['profesor'] ?: '',
                    $data['objetivos'] ?: '',
                    $data['metodologia'] ?: '',
                    $data['evaluacion'] ?: '',
                    $data['atencion_diversidad'] ?: '',
                    $data['materiales'] ?: '',
                    $data['bibliografia'] ?: '',
                    $data['id']
                ]);
                $id = $data['id'];
            } else {
                // Crear
                $sql = "INSERT INTO programaciones (idMateria, idGrupo, curso, anyo, profesor, objetivos, metodologia, evaluacion, atencion_diversidad, materiales, bibliografia) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['idMateria'],
                    $data['idGrupo'] ?: null,
                    $data['curso'],
                    $data['anyo'] ?: '',
                    $data['profesor'] ?: '',
                    $data['objetivos'] ?: '',
                    $data['metodologia'] ?: '',
                    $data['evaluacion'] ?: '',
                    $data['atencion_diversidad'] ?: '',
                    $data['materiales'] ?: '',
                    $data['bibliografia'] ?: ''
                ]);
                $id = $pdo->lastInsertId();
            }
            
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $sql = "DELETE FROM programaciones WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['id']]);
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
