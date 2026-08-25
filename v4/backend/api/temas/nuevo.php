<?php
// FASE 2.6 — Insertar un nuevo tema (solo nº + título; el resto por defecto).
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idMateria = datosOptimoInt($body, 'idMateria');
    $orden     = datosOptimoInt($body, 'orden');
    $titulo    = trim(datosOptimo($body, 'titulo'));

    if ($idMateria <= 0 || $orden <= 0 || $titulo === '') {
        throw new Exception('Indica el número y el título del tema');
    }

    // Nota: titulo se inserta como '' aquí; se actualiza a continuación para respetar el texto.
    $db->execute("INSERT INTO temas
                    (idMateria, orden, titulo, horas, trimestre, peso_evaluacion,
                     descripcion, justificacion, contexto, contenidos, secuenciacion,
                     recursos, evaluacion, metodologia, adaptaciones,
                     contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto)
                    VALUES (?, ?, '', 0, 0, 0, '', '', '', '', '', '', '', '', '', 1, 1, 1, 1)",
                    $idMateria, $orden);
    $nuevoId = $db->insertId();
    $db->execute("UPDATE temas SET titulo = ? WHERE id = ?", $titulo, $nuevoId);

    $db->close();
    sendJSONSuccess(['id' => $nuevoId], 'Tema creado correctamente');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
