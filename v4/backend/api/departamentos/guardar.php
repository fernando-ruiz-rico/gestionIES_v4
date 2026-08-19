<?php
// API endpoint para insertar o actualizar un departamento
// Requiere sesión iniciada y rol de admin
// Recibe: nombre (requerido), id (opcional - si existe actualiza, si no inserta)

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../../config.php';

// Verificar permisos de administrador
$permisos = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

if (!$permisos) {
    http_response_code(403);
    echo json_encode(['error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

if (empty($_POST['nombre'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre del departamento es requerido']);
    exit;
}

$nombre = mysqli_real_escape_string(getDBConnection(), $_POST['nombre']);
$id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

if ($id === null) {
    // Insertar nuevo departamento
    $query = "INSERT INTO departamentos (nombre) VALUES ('$nombre')";
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al insertar el departamento: ' . mysqli_error($db)]);
        exit;
    }
    
    $id_nuevo = mysqli_insert_id($db);
    mysqli_close($db);
    echo json_encode(['success' => true, 'id' => $id_nuevo, 'mensaje' => 'Departamento creado correctamente']);
} else {
    // Actualizar departamento existente
    $query = "UPDATE departamentos SET nombre='$nombre' WHERE id = $id";
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el departamento: ' . mysqli_error($db)]);
        exit;
    }
    
    mysqli_close($db);
    echo json_encode(['success' => true, 'mensaje' => 'Departamento actualizado correctamente']);
}
?>
