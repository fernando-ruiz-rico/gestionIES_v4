<?php
// Asocia una competencia profesional a una materia.
// Fiel a v3: v3/ajax/materias/nueva_competencia_materia.php (solo admin).
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
if (!$datos) {
    sendJSONError('Datos inválidos', 400);
}

$idMateria = datosOptimoInt($datos, 'idMateria');
$idCompetencia = datosOptimoInt($datos, 'idCompetencia');

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
