<?php
// FASE 2.6 — Editar el flag «RA/CE clave» de un RA concreto.
// El porcentaje de evaluación (porcentaje_evaluacion) ya no se edita a
// mano: se calcula en recalcular_porcentajes.php a partir del peso de las
// unidades y de los criterios de evaluación de cada RA.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idRA    = datosOptimoInt($body, 'idRA');
    $esClave   = !empty($body['es_clave']) ? 1 : 0;

    if ($idRA <= 0) {
        throw new Exception('Debe indicar un resultado de aprendizaje');
    }

    $db->execute("UPDATE resultados_aprendizaje SET es_clave = ? WHERE id = ?",
        $esClave, $idRA);

    $db->close();
    sendJSONSuccess(null, 'Resultado de aprendizaje actualizado');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
