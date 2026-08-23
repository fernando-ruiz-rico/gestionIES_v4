<?php
// API endpoint para ordenar profesores de un departamento
// Requiere sesión iniciada y rol de admin o jefeDepartamento
// Recibe: orden (cadena con ids separados por comas, prefijo "pr")
// Devuelve: success (true/false), mensaje

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../../config.php';

// Verificar permisos (admin o jefe de departamento)
$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if (!$permisos) {
    http_response_code(403);
    echo json_encode(['error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

if (empty($_POST['orden'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Orden no proporcionado']);
    exit;
}

$orden = $_POST['orden'];
$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Lo que se recibe en el parámetro "orden" son los id de los profesores en el orden en que
// se quieren asignar. Cada profesor en el listado viene con id "pr" seguido de su código.
$partes = explode(",", $orden);
try {
    $db = new Db($conn);
    for ($i = 1; $i <= count($partes); $i++) {
        // Quitar prefijo "pr" para obtener el código del profesor
        $codProfesor = substr($partes[$i-1], 2);
        $db->execute("UPDATE profesores SET orden=$i WHERE id=$codProfesor");
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el orden de los profesores: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true, 'mensaje' => 'Orden de profesores actualizado correctamente']);
?>
