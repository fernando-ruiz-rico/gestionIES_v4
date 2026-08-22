<?php
// API endpoint para guardar (crear/actualizar) apartados de programaciones
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

@session_start();
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
if (!$permisos) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['titulo']) || trim($data['titulo']) === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El título es obligatorio']);
    exit;
}

// Las categorías válidas son las mismas que en v3
$categoriasValidas = array('ESO/BACH', 'FP', 'TODOS');
$categoria = isset($data['categoria']) ? $data['categoria'] : '';
if (!in_array($categoria, $categoriasValidas)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Categoría no válida']);
    exit;
}

$titulo = mysqli_real_escape_string($db, $data['titulo']);
$subapartado = isset($data['subapartado']) && $data['subapartado'] ? 1 : 0;
$requerido = isset($data['requerido']) && $data['requerido'] ? 1 : 0;
$contenidoDefecto = isset($data['contenido_defecto']) && $data['contenido_defecto'] ? 1 : 0;
$tipo = isset($data['tipo']) ? intval($data['tipo']) : 0;
if ($tipo < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tipo no válido']);
    exit;
}
$id = isset($data['id']) && $data['id'] > 0 ? intval($data['id']) : 0;

if ($id > 0) {
    // Actualizar
    $stmt = mysqli_prepare($db, "UPDATE apartados_programaciones SET titulo=?, subapartado=?, requerido=?, contenido_defecto=?, categoria=?, tipo=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "siiisii", $titulo, $subapartado, $requerido, $contenidoDefecto, $categoria, $tipo, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al actualizar: ' . mysqli_error($db)]);
    }
} else {
    // Crear nuevo: se asigna el siguiente orden disponible
    $resultOrden = mysqli_query($db, "SELECT COALESCE(MAX(orden), 0) + 1 AS nuevoOrden FROM apartados_programaciones");
    $filaOrden = mysqli_fetch_assoc($resultOrden);
    $nuevoOrden = intval($filaOrden['nuevoOrden']);
    mysqli_free_result($resultOrden);

    $stmt = mysqli_prepare($db, "INSERT INTO apartados_programaciones (titulo, subapartado, requerido, contenido_defecto, categoria, tipo, orden) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siiisii", $titulo, $subapartado, $requerido, $contenidoDefecto, $categoria, $tipo, $nuevoOrden);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'id' => mysqli_insert_id($db)]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al insertar: ' . mysqli_error($db)]);
    }
}

mysqli_close($db);
?>
