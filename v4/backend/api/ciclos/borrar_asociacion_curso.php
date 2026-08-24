<?php
// API para desasociar un curso de un ciclo (Fase 1)
// Equivalente a v3/ajax/ciclos/borrar_curso_ciclo.php
require_once '../../config.php';
cabeceraJson();

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
$idCiclo = datosOptimoInt($datos, 'idCiclo');
$idCurso = datasOptimoInt($datos, 'idCurso');
if ($idCiclo <= 0 || $idCurso <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();
    $afectadas = $db->execute("DELETE FROM cursos_ciclos WHERE idCiclo = ? AND idCurso = ?", $idCiclo, $idCurso);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

if ($afectadas > 0) {
    sendJSONSuccess(null, 'Asociación eliminada');
}

sendJSONError('La asociación no existe', 404);
?>
