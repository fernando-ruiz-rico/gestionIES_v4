<?php
// FASE 2.6 — Actualizar tema + reemplazar CE y competencias.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idTema = datosOptimoInt($body, 'idTema');
    if ($idTema <= 0) {
        throw new Exception('Debe indicar el tema a guardar');
    }

    $orden            = datosOptimoInt($body, 'orden');
    $titulo           = trim(datosOptimo($body, 'titulo'));
    $horas            = datosOptimoInt($body, 'horas');
    $trimestre        = datosOptimoInt($body, 'trimestre');
    $peso             = datosOptimoInt($body, 'peso_evaluacion');
    $descripcion      = datosOptimo($body, 'descripcion');
    $justificacion    = datosOptimo($body, 'justificacion');
    $contexto         = datosOptimo($body, 'contexto');
    $contenidos       = datosOptimo($body, 'contenidos');
    $secuenciacion    = datosOptimo($body, 'secuenciacion');
    $recursos         = datosOptimo($body, 'recursos');
    $evaluacion       = datosOptimo($body, 'evaluacion');
    $metodologia      = datosOptimo($body, 'metodologia');
    $adaptaciones     = datosOptimo($body, 'adaptaciones');
    $contextoDefecto  = !empty($body['contexto_defecto']) ? 1 : 0;
    $recursosDefecto  = !empty($body['recursos_defecto']) ? 1 : 0;
    $metodologiaDefecto = !empty($body['metodologia_defecto']) ? 1 : 0;
    $adaptacionesDefecto = !empty($body['adaptaciones_defecto']) ? 1 : 0;

    try {
        $db->begin();
        $afectados = $db->execute("UPDATE temas SET
                    orden = ?, titulo = ?, horas = ?, trimestre = ?, peso_evaluacion = ?,
                    descripcion = ?, justificacion = ?, contexto = ?, contenidos = ?,
                    secuenciacion = ?, recursos = ?, evaluacion = ?, metodologia = ?, adaptaciones = ?,
                    contexto_defecto = ?, recursos_defecto = ?, metodologia_defecto = ?, adaptaciones_defecto = ?
                    WHERE id = ?",
                    $orden, $titulo, $horas, $trimestre, $peso,
                    $descripcion, $justificacion, $contexto, $contenidos,
                    $secuenciacion, $recursos, $evaluacion, $metodologia, $adaptaciones,
                    $contextoDefecto, $recursosDefecto, $metodologiaDefecto, $adaptacionesDefecto,
                    $idTema);

        // Reemplazar criterios de evaluación (CE)
        $db->execute("DELETE FROM criterios_temas WHERE idTema = ?", $idTema);
        $criterios = isset($body['criterios']) && is_array($body['criterios']) ? $body['criterios'] : array();
        foreach ($criterios as $ce) {
            if (!is_array($ce) || !isset($ce['idRA'], $ce['codigo'])) {
                continue;
            }
            $db->execute("INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES (?, ?, ?)",
                intval($ce['idRA']), $ce['codigo'], $idTema);
        }

        // Reemplazar competencias
        $db->execute("DELETE FROM competencias_temas WHERE idTema = ?", $idTema);
        $competencias = isset($body['competencias']) && is_array($body['competencias']) ? $body['competencias'] : array();
        foreach ($competencias as $com) {
            $db->execute("INSERT INTO competencias_temas (idCompetencia, idTema) VALUES (?, ?)",
                intval($com), $idTema);
        }

        $db->commit();
    } catch (DbException $e) {
        $db->rollback();
        throw $e;
    }

    $db->close();
    sendJSONSuccess([
        'errorTema' => ($afectados == 0),
        'errorCriterios' => false,
        'errorCompetencias' => false
    ], 'Tema guardado correctamente');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
