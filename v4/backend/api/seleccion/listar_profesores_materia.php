<?php
// API de selección (Desideratas): nombres de los profesores que ya eligieron
// una materia (badge "X/Y" al pulsarlo)
// (v3/cargar_listado_profesores_materia.php)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

$idMateria = getOptimoInt('idMateria');
$idGrupo = getOptimoInt('idGrupo');
$idEscenario = getOptimoInt('idEscenario');
if ($idMateria <= 0 || $idGrupo <= 0 || $idEscenario <= 0) {
    sendJSONError('Faltan parámetros', 400);
}
try {
    $db = Db::open();
    $filas = $db->fetchAll("SELECT p.nombre
                            FROM seleccion s
                            JOIN profesores p ON p.id = s.idProfesor
                            WHERE s.idMateria = ? AND s.idGrupo = ? AND s.idEscenario = ?
                            ORDER BY s.orden, p.orden", $idMateria, $idGrupo, $idEscenario);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
$nombres = array();
foreach ($filas as $fila) {
    $nombres[] = $fila['nombre'];
}
sendJSONSuccess($nombres);
