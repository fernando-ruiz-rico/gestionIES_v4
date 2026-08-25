<?php
// FASE 2.1 — Texto de un apartado de una materia (v3/cargar_contenido_programacion).
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();

    $idMateria = getOptimoInt('idMateria');
    $idApartado = getOptimoInt('idApartado');
    if ($idMateria <= 0 || $idApartado <= 0) {
        throw new Exception('ID de materia o apartado inválido');
    }

    $fila = $db->fetchOne(
        "SELECT texto FROM contenidos_programaciones WHERE idMateria = ? AND idApartado = ?",
        $idMateria, $idApartado);
    $db->close();

    sendJSONSuccess(array('texto' => $fila ? $fila['texto'] : ''));
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
