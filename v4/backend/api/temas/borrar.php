<?php
// FASE 2.6 — Borrar tema + relaciones (competencias, criterios, programaciones_aula).
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idTema = datosOptimoInt($body, 'id');
    if ($idTema <= 0) {
        throw new Exception('Debe indicar el tema a borrar');
    }

    try {
        $db->begin();
        foreach (['competencias_temas', 'criterios_temas', 'programaciones_aula_temas'] as $tabla) {
            $db->execute("DELETE FROM {$tabla} WHERE idTema = ?", $idTema);
        }
        $db->execute("DELETE FROM temas WHERE id = ?", $idTema);
        $db->commit();
    } catch (DbException $e) {
        $db->rollback();
        throw $e;
    }

    $db->close();
    sendJSONSuccess(null, 'Tema eliminado correctamente');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
