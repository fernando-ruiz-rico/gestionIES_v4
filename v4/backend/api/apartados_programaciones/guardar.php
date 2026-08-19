<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos) {
        echo json_encode(['success' => false, 'error' => 'Datos no válidos']);
        exit;
    }
    
    if (isset($datos['id']) && !empty($datos['id'])) {
        // Actualizar
        $stmt = $pdo->prepare("UPDATE apartados_programaciones SET 
            id_programacion = ?, 
            titulo = ?, 
            contenido = ?, 
            orden = ? 
            WHERE id = ?");
        $stmt->execute([
            $datos['id_programacion'],
            $datos['titulo'],
            $datos['contenido'],
            $datos['orden'],
            $datos['id']
        ]);
        echo json_encode(['success' => true, 'message' => 'Apartado actualizado correctamente']);
    } else {
        // Crear
        $stmt = $pdo->prepare("INSERT INTO apartados_programaciones 
            (id_programacion, titulo, contenido, orden, activo) 
            VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([
            $datos['id_programacion'],
            $datos['titulo'],
            $datos['contenido'],
            $datos['orden']
        ]);
        echo json_encode(['success' => true, 'message' => 'Apartado creado correctamente', 'id' => $pdo->lastInsertId()]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
}
?>
