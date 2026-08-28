<?php
// Programaciones de aula — Copiar el campo "evaluación" a todas las unidades
// (temas) de la copia de aula de un (materia, grupo, profesor). Espejo de
// api/temas/repetir_evaluacion.php.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idMateria  = datosOptimoInt($body, 'idMateria');
    $idGrupo    = datosOptimoInt($body, 'idGrupo');
    $idProfesor = datosOptimoInt($body, 'idProfesor');
    $evaluacion = datosOptimo($body, 'evaluacion');
    if ($idMateria <= 0 || $idGrupo <= 0 || $idProfesor <= 0) {
        throw new Exception('Debe indicar la materia, el grupo y el profesor');
    }

    $afectados = $db->execute("UPDATE temas_aula SET evaluacion = ? WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?",
        $evaluacion, $idMateria, $idGrupo, $idProfesor);

    $db->close();
    sendJSONSuccess(['actualizados' => $afectados],
        'Campo de evaluación copiado en todos los temas de la copia');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
