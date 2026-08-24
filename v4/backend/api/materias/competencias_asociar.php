<?php
// Asocia una competencia profesional a una materia.
// Fiel a v3: v3/ajax/materias/nueva_competencia_materia.php (solo admin).
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    sendJSONError('Datos inválidos', 400);
}

$idMateria = intval(isset($datos['idMateria']) ? $datos['idMateria'] : 0);
$idCompetencia = intval(isset($datos['idCompetencia']) ? $datos['idCompetencia'] : 0);

if ($idMateria <= 0 || $idCompetencia <= 0) {
    sendJSONError('Parámetros inválidos', 400);
}

try {
    $db = Db::open();

    // Evita duplicados (PK de competencias_materias es (idMateria, idCompetencia))
    $yaAsociada = $db->fetchOne("SELECT * FROM competencias_materias WHERE idMateria = ? AND idCompetencia = ?", $idMateria, $idCompetencia) !== null;

    if ($yaAsociada) {
        sendJSONSuccess(null, 'La competencia ya está asociada');
    }

    $db->execute("INSERT INTO competencias_materias (idCompetencia, idMateria) VALUES (?, ?)", $idCompetencia, $idMateria);

    sendJSONSuccess(null, 'Competencia asociada');
} catch (DbException $e) {
    sendJSONError('Error al asociar la competencia: ' . $e->getMessage(), 500);
}
?>
