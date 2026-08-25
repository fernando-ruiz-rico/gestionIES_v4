<?php
// FASE 2.6 — Editar porcentaje/es_clave de un RA concreto.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idRA    = datosOptimoInt($body, 'idRA');
    $porcentaje = datosOptimoInt($body, 'porcentaje_evaluacion');
    $esClave   = !empty($body['es_clave']) ? 1 : 0;

    if ($idRA <= 0) {
        throw new Exception('Debe indicar un resultado de aprendizaje');
    }

    $db->execute("UPDATE resultados_aprendizaje SET porcentaje_evaluacion = ?, es_clave = ? WHERE id = ?",
        $porcentaje, $esClave, $idRA);

    $db->close();
    sendJSONSuccess(null, 'Resultado de aprendizaje actualizado');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
