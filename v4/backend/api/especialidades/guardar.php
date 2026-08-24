<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    sendJSONError('Datos inválidos', 400);
}

$id = trim(isset($datos['id']) ? $datos['id'] : '');
$descripcion = trim(isset($datos['descripcion']) ? $datos['descripcion'] : '');
$idDepartamento = intval(isset($datos['idDepartamento']) ? $datos['idDepartamento'] : 0);
$horasTutoria = intval(isset($datos['horasTutoria']) ? $datos['horasTutoria'] : 0);
$horasIngles = intval(isset($datos['horasIngles']) ? $datos['horasIngles'] : 0);

if (empty($id)) {
    sendJSONError('ID obligatorio', 400);
}

if (empty($descripcion)) {
    sendJSONError('Descripción obligatoria', 400);
}

try {
    $db = Db::open();

    // Verificar si existe para actualizar o insertar
    $existe = $db->fetchOne("SELECT id FROM especialidades WHERE id = ?", $id);

    if ($existe) {
        $db->execute("UPDATE especialidades SET descripcion=?, idDepartamento=?, horasTutoria=?, horasIngles=? WHERE id=?", $descripcion, $idDepartamento, $horasTutoria, $horasIngles, $id);
    } else {
        $db->execute("INSERT INTO especialidades (id, descripcion, idDepartamento, horasTutoria, horasIngles) VALUES (?, ?, ?, ?, ?)", $id, $descripcion, $idDepartamento, $horasTutoria, $horasIngles);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => $id), 'Guardado correctamente');
?>
