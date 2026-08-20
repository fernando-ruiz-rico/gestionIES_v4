<?php
// API endpoint para guardar (crear/actualizar) apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['titulo']) || empty($data['titulo'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El título es obligatorio']);
    exit;
}

$titulo = mysqli_real_escape_string($db, $data['titulo']);
$subapartado = isset($data['subapartado']) && $data['subapartado'] ? 1 : 0;
$requerido = isset($data['requerido']) && $data['requerido'] ? 1 : 0;
$contenidoDefecto = isset($data['contenido_defecto']) && $data['contenido_defecto'] ? 1 : 0;
$categoria = isset($data['categoria']) ? mysqli_real_escape_string($db, $data['categoria']) : '';
$tipo = isset($data['tipo']) ? intval($data['tipo']) : 0;
$id = isset($data['id']) && $data['id'] > 0 ? intval($data['id']) : 0;

if ($id > 0) {
    // Actualizar
    $sql = "UPDATE apartados_programaciones SET 
            titulo='$titulo', 
            subapartado=$subapartado, 
            requerido=$requerido, 
            contenido_defecto=$contenidoDefecto, 
            categoria='$categoria', 
            tipo=$tipo 
            WHERE id=$id";
    $result = mysqli_query($db, $sql);
    
    if ($result) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al actualizar: ' . mysqli_error($db)]);
    }
} else {
    // Crear nuevo
    // Obtener el último orden para asignar el siguiente
    $resultOrden = mysqli_query($db, "SELECT MAX(orden) as maxOrden FROM apartados_programaciones");
    $filaOrden = mysqli_fetch_assoc($resultOrden);
    $nuevoOrden = ($filaOrden['maxOrden'] ?? 0) + 1;
    mysqli_free_result($resultOrden);
    
    $sql = "INSERT INTO apartados_programaciones (titulo, subapartado, requerido, contenido_defecto, categoria, tipo, orden) 
            VALUES ('$titulo', $subapartado, $requerido, $contenidoDefecto, '$categoria', $tipo, $nuevoOrden)";
    $result = mysqli_query($db, $sql);
    
    if ($result) {
        $nuevoId = mysqli_insert_id($db);
        echo json_encode(['success' => true, 'id' => $nuevoId]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al insertar: ' . mysqli_error($db)]);
    }
}

mysqli_close($db);
?>
