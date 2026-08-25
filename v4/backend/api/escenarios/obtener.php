<?php
// API para obtener un escenario con sus departamentos asociados
// (para el modal de alta/edición, fiel a v3/cargar_escenario.php)
require_once '../../config.php';
cabeceraJson();

$id = getOptimoInt('id');
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $escenario = $db->fetchOne("SELECT id, nombre FROM escenarios_desideratas WHERE id = ?", $id);
    if (!$escenario) {
        sendJSONError('No encontrado', 404);
    }
    // Departamentos ya asociados (v3/cargar_departamentos_escenario.php los
    // deja marcados en el modal de edición)
    $escenario['departamentos'] = $db->fetchAll("SELECT d.id, d.nombre
                                                 FROM departamentos_escenarios de
                                                 JOIN departamentos d ON d.id = de.idDepartamento
                                                 WHERE de.idEscenario = ?
                                                 ORDER BY d.nombre", $id);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($escenario);
?>
