<?php
// FASE 2.1 — Importar la programación de otra materia (fiel a v3):
// borra la de la destino y copia apartados, contenidos, temas, RA y CE
// de la materia origen. Permiso fiel a v3: solo admin.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();

    // Permiso fiel a v3 (importar_programacion.php): solo admin
    checkPermission(array(ROLE_ADMIN));

    $datos = cuerpoJson();

    if (!isset($datos['idMateriaOrigen']) || !isset($datos['idMateriaDestino'])) {
        throw new Exception('Debe especificar materia origen y destino');
    }

    $idMateriaOrigen = intval($datos['idMateriaOrigen']);
    $idMateriaDestino = intval($datos['idMateriaDestino']);

    if ($idMateriaOrigen <= 0 || $idMateriaDestino <= 0) {
        throw new Exception('IDs de materia inválidos');
    }

    try {
        $db->begin();

        // Borrar contenidos previos de programación destino
        $db->execute("DELETE FROM contenidos_programaciones WHERE idMateria = ?", $idMateriaDestino);
        $db->execute("DELETE FROM competencias_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = ?)", $idMateriaDestino);
        $db->execute("DELETE FROM criterios_temas WHERE idTema IN (SELECT id FROM temas WHERE idMateria = ?)", $idMateriaDestino);
        $db->execute("DELETE FROM temas WHERE idMateria = ?", $idMateriaDestino);

        // Insertar contenidos de la materia origen en la destino
        $db->execute("INSERT INTO contenidos_programaciones(idMateria, idApartado, texto)
                        SELECT ? AS idMateria, idApartado, texto
                        FROM contenidos_programaciones WHERE idMateria = ?",
            $idMateriaDestino, $idMateriaOrigen);

        // Insertar temas
        $db->execute("INSERT INTO temas(idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto)
                        SELECT ? AS idMateria, orden, titulo, horas, trimestre, peso_evaluacion, descripcion, justificacion, contexto, contenidos, secuenciacion, recursos, evaluacion, metodologia, adaptaciones, contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto
                        FROM temas WHERE idMateria = ?",
            $idMateriaDestino, $idMateriaOrigen);

        // Insertar RA y CE asociados
        foreach ($db->fetchAll("SELECT criterios_temas.codigo as CE, temas.orden as tema, resultados_aprendizaje.orden as RA
                                FROM criterios_temas, temas, resultados_aprendizaje
                                WHERE criterios_temas.idRA = resultados_aprendizaje.id
                                  AND criterios_temas.idTema = temas.id
                                  AND temas.idMateria = ?", $idMateriaOrigen) as $fila) {
            $codigoCE = $fila['CE'];
            $ordenRA = intval($fila['RA']);
            $numTema = intval($fila['tema']);

            // Buscar el id del RA para la materia destino
            $row2 = $db->fetchOne("SELECT id FROM resultados_aprendizaje WHERE idMateria = ? AND orden = ?",
                $idMateriaDestino, $ordenRA);
            $idRA = $row2 ? $row2['id'] : null;

            // Buscar el id del tema para la materia destino
            $row2 = $db->fetchOne("SELECT id FROM temas WHERE idMateria = ? AND orden = ?",
                $idMateriaDestino, $numTema);
            $idTema = $row2 ? $row2['id'] : null;

            if ($idRA && $idTema) {
                $db->execute("INSERT INTO criterios_temas (idRA, codigo, idTema) VALUES (?, ?, ?)",
                    $idRA, $codigoCE, $idTema);
            }
        }

        $db->commit();
    } catch (DbException $e) {
        $db->rollback();
        throw $e;
    }

    $db->close();
    sendJSONSuccess(null, 'Programación importada correctamente');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
