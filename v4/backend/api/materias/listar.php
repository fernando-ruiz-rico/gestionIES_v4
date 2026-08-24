<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Filtro opcional por curso (fiel a v3: la vista principal de materias se
// filtra por idCurso; sin idCurso se devuelven todas las materias).
$idCurso = getOptimoInt('idCurso');

try {
    $db = Db::open();
    if ($idCurso > 0) {
        $materias = $db->fetchAll("SELECT * FROM materias WHERE idCurso = ? ORDER BY nombre", $idCurso);
    } else {
        $materias = $db->fetchAll("SELECT * FROM materias ORDER BY nombre");
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($materias);
?>
