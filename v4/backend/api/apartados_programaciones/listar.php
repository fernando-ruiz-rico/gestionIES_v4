<?php
/**
 * Listar apartados de programaciones
 */
require_once '../../config.php';

@session_start();

if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$conn = getDBConnection();
if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

// Obtener parámetros
$idProgramacion = isset($_GET['idProgramacion']) ? intval($_GET['idProgramacion']) : 0;

$query = "SELECT ap.*, p.nombre as nombreProgramacion 
          FROM apartados_programaciones ap
          LEFT JOIN programaciones p ON ap.idProgramacion = p.id
          WHERE 1=1";

$params = [];
$types = '';

if ($idProgramacion > 0) {
    $query .= " AND ap.idProgramacion = ?";
    $params[] = $idProgramacion;
    $types .= 'i';
}

$query .= " ORDER BY ap.orden, ap.nombre";

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $apartados = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $apartados[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    
    sendJSONSuccess($apartados);
} else {
    closeDBConnection($conn);
    sendJSONError('Error en la consulta: ' . mysqli_error($conn));
}
?>
