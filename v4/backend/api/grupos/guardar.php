<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';
$db = getDBConnection();
if (!$db) { http_response_code(500); echo json_encode(['error' => 'Error conexión']); exit; }
$tabla = basename(dirname(__FILE__));
$idCampo = 'id' . ucfirst(rtrim($tabla, 's'));
$accion = basename(__FILE__, '.php');
if ($accion === 'listar') {
    $r = mysqli_query($db, "SELECT * FROM $tabla ORDER BY nombre");
    $d = []; while($fila = mysqli_fetch_assoc($r)) { $d[] = $fila; }
    echo json_encode($d);
} elseif ($accion === 'obtener') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'ID inválido']); exit; }
    $stmt = mysqli_prepare($db, "SELECT * FROM $tabla WHERE $idCampo = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) { http_response_code(404); echo json_encode(['error' => 'No encontrado']); exit; }
    echo json_encode($row);
} elseif ($accion === 'guardar') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $nombre = trim($datos['nombre'] ?? '');
    $id = isset($datos[$idCampo]) ? intval($datos[$idCampo]) : 0;
    if (empty($nombre)) { http_response_code(400); echo json_encode(['error' => 'Nombre obligatorio']); exit; }
    if ($id > 0) { $stmt = mysqli_prepare($db, "UPDATE $tabla SET nombre=? WHERE $idCampo=?"); mysqli_stmt_bind_param($stmt, "si", $nombre, $id); }
    else { $stmt = mysqli_prepare($db, "INSERT INTO $tabla (nombre) VALUES (?)"); mysqli_stmt_bind_param($stmt, "s", $nombre); }
    $ok = mysqli_stmt_execute($stmt);
    echo json_encode(['success' => $ok, 'message' => $ok ? 'Guardado' : 'Error']);
} elseif ($accion === 'eliminar') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $id = intval($datos[$idCampo] ?? 0);
    if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'ID inválido']); exit; }
    $stmt = mysqli_prepare($db, "DELETE FROM $tabla WHERE $idCampo = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_affected_rows($stmt) === 0) { http_response_code(404); echo json_encode(['error' => 'No encontrado']); exit; }
    echo json_encode(['success' => true, 'message' => 'Eliminado']);
}
mysqli_close($db);
?>
