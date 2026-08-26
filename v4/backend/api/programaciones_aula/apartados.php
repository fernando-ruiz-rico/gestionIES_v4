<?php
// FASE 2.4 — Apartados de una materia, para la opción de programaciones de
// aula (mismo catálogo que la propuesta pedagógica: apartados por categoría,
// con numeración). La lógica vive en lib/programaciones_compartidas.php.
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

try {
    $db = Db::open();

    $idMateria = getOptimoInt('idMateria');
    if ($idMateria <= 0) {
        throw new Exception('ID de materia inválido');
    }

    $apartados = pcCmp_cargarApartados($db, $idMateria);
    $db->close();
    sendJSONSuccess($apartados);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
