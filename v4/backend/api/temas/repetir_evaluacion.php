<?php
// FASE 2.6 — Copiar el campo "evaluación" a todos los temas de la materia.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idMateria  = datosOptimoInt($body, 'idMateria');
    $evaluacion = datosOptimo($body, 'evaluacion');
    if ($idMateria <= 0) {
        throw new Exception('Debe indicar una materia');
    }

    $afectados = $db->execute("UPDATE temas SET evaluacion = ? WHERE idMateria = ?",
        $evaluacion, $idMateria);

    $db->close();
    sendJSONSuccess(['actualizados' => $afectados],
        'Campo de evaluación copiado en todos los temas de la materia');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
