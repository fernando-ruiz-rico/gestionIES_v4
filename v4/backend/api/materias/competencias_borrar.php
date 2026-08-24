<?php
// Quita la asociación de una competencia a una materia.
// Fiel a v3: v3/ajax/materias/borrar_competencia_materia.php (solo admin).
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
    sendJSONError('Parámetros inválids', 400);
}

try {
    $db = Db::open();

    $afectadas = $db->execute("DELETE FROM competencias_materias WHERE idMateria = ? AND idCompetencia = ?", $idMateria, $idCompetencia);
} catch (DbException $e) {
    sendJSONError('Error al borrar la competencia: ' . $e->getMessage(), 500);
}

if ($afectadas === 0) {
    sendJSONError('No encontrado', 404);
}

sendJSONSuccess(null, 'Competencia desvinculada');
?>
