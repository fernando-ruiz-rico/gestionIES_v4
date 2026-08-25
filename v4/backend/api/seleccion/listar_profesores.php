<?php
// API de selección (Desideratas): panel de profesores del departamento,
// con el total de horas que ya eligieron (v3/listar_profesores.php)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

$idDepartamento = getOptimoInt('idDepartamento');
$idEscenario = getOptimoInt('idEscenario');
if ($idDepartamento <= 0 || $idEscenario <= 0) {
    sendJSONError('Faltan parámetros', 400);
}
$idEspecialidad = datosOptimo($_REQUEST, 'idEspecialidad', 'Todos');
try {
    $db = Db::open();
    $extra = ($idEspecialidad == 'Todos') ? '' : ' AND p.idEspecialidad = ?';
    if ($idEspecialidad == 'Todos') {
        $filas = $db->fetchAll("SELECT p.id, p.nombre, p.idEspecialidad,
                                    (SELECT COALESCE(SUM(horas), 0)
                                     FROM seleccion s
                                     WHERE s.idEscenario = ? AND s.idProfesor = p.id) AS horas
                            FROM profesores p
                            WHERE p.idDepartamento = ? AND p.activo = 1
                            ORDER BY p.orden", $idEscenario, $idDepartamento);
    } else {
        $filas = $db->fetchAll("SELECT p.id, p.nombre, p.idEspecialidad,
                                    (SELECT COALESCE(SUM(horas), 0)
                                     FROM seleccion s
                                     WHERE s.idEscenario = ? AND s.idProfesor = p.id) AS horas
                            FROM profesores p
                            WHERE p.idDepartamento = ? AND p.activo = 1" . $extra . "
                            ORDER BY p.orden", $idEscenario, $idDepartamento, $idEspecialidad);
    }
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
sendJSONSuccess($filas);
