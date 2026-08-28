<?php
// Programaciones de aula — Borrar una unidad (tema) de la copia de aula +
// sus relaciones (competencias, criterios). Espejo de api/temas/borrar.php
// sobre las tablas de aula.
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
        foreach (['competencias_temas_aula', 'criterios_temas_aula'] as $tabla) {
            $db->execute("DELETE FROM {$tabla} WHERE idTema = ?", $idTema);
        }
        $db->execute("DELETE FROM temas_aula WHERE id = ?", $idTema);
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
