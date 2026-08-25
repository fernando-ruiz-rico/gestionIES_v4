<?php
// FASE 2.1 — Listar materias con programación activa y su estado actual.
// Modelo fiel a v3: no existe una fila única de "programación"; la
// programación vive en apartados + contenidos asociados a cada materia.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();

    $idMateria = getOptimoInt('idMateria');

    $sql = "SELECT m.id AS id, m.nombre AS materia, c.nombre AS curso,
                    m.horas AS horas,
                    (SELECT COUNT(DISTINCT cp.idApartado)
                       FROM contenidos_programaciones cp
                       WHERE cp.idMateria = m.id) AS num_apartados
             FROM materias m
             LEFT JOIN cursos c ON c.id = m.idCurso";
    $params = array();

    if ($idMateria > 0) {
        $sql .= " WHERE m.id = ? AND m.tiene_programacion = 1";
        $params[] = $idMateria;
    } else {
        $sql .= " WHERE m.tiene_programacion = 1";
    }

    $sql .= " ORDER BY c.orden, c.nombre, m.nombre";

    $programaciones = $db->fetchAll($sql, ...$params);
    sendJSONSuccess($programaciones);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage(), 400);
}
