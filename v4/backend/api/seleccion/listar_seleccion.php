<?php
// API de selección (Desideratas): materias que ya eligió el profesor, con su orden
// actual (v3/listar_seleccion.php)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

$idProfesor = getOptimoInt('idProfesor');
$idEscenario = getOptimoInt('idEscenario');
if ($idProfesor <= 0 || $idEscenario <= 0) {
    sendJSONError('Faltan parámetros', 400);
}
try {
    $db = Db::open();
    $filas = $db->fetchAll("SELECT s.id, s.horas, s.orden, m.nombre, m.asignada_directiva,
                                c.abreviatura AS abrevCurso, g.abreviatura AS abrevGrupo, g.mostrar
                        FROM seleccion s
                        JOIN materias m ON m.id = s.idMateria
                        JOIN cursos c ON c.id = m.idCurso
                        JOIN grupos g ON g.id = s.idGrupo
                        WHERE s.idProfesor = ? AND s.idEscenario = ?
                        ORDER BY s.orden", $idProfesor, $idEscenario);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
sendJSONSuccess($filas);
