<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Filtro opcional por curso (fiel a v3: la vista principal de materias se
// filtra por idCurso; sin idCurso se devuelven todas las materias).
$idCurso = isset($_GET['idCurso']) ? intval($_GET['idCurso']) : 0;

try {
    $db = Db::open();
    if ($idCurso > 0) {
        $materias = $db->fetchAll("SELECT * FROM materias WHERE idCurso = ? ORDER BY nombre", $idCurso);
    } else {
        $materias = $db->fetchAll("SELECT * FROM materias ORDER BY nombre");
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode($materias);
?>
