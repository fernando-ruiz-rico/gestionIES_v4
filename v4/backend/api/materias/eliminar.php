<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = json_decode(file_get_contents('php://input'), true);
$id = intval(isset($datos['id']) ? $datos['id'] : 0);

if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();

    // Compruebo que existe antes de borrar (fiel a v3)
    $materia = $db->fetchOne("SELECT id FROM materias WHERE id = ?", $id);

    if (!$materia) {
        sendJSONError('No encontrado', 404);
    }

    // Borrado en cascada (fiel a v3/borrar_materia.php): las tablas que la huérfanan
    // antes que la propia materia, para no dejar filas huérfanas (ver B-6).
    $db->execute("DELETE FROM seleccion WHERE idMateria = ?", $id);
    $db->execute("DELETE FROM materias_grupos WHERE idMateria = ?", $id);
    $db->execute("DELETE FROM contenidos_programaciones WHERE idMateria = ?", $id);

    $db->execute("DELETE FROM materias WHERE id = ?", $id);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(null, 'Eliminado correctamente');
?>
