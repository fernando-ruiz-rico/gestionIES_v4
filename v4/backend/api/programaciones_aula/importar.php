<?php
// FASE 2.4 — Importar la programación de aula: hace una copia COMPLETA e
// INDEPENDIENTE, para el profesor y el grupo elegidos, de la propuesta
// pedagógica de la materia elegida. Opción propia de v4.
//
// Se copian (borrando una copia previa, si la hay):
//   - contenidos de apartados: contenidos_programaciones ->
//     contenidos_programaciones_aula
//   - unidades (temas):        temas -> temas_aula
//   - resultados de aprendizaje: resultados_aprendizaje ->
//     resultados_aprendizaje_aula
//   - criterios de evaluación: criterios_temas -> criterios_temas_aula
//     (re-vinculados por orden, pues los ids de RA y tema cambian)
//
// Requisitos:
//   - La propuesta pedagógica de la materia debe estar marcada como
//     terminada (materias.terminada_programacion = 1).
//   - El profesor debe impartir la materia en el grupo en el escenario
//     actual.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();

    $session = checkSession();
    $rol = $session['rol'];
    $idUsuarioSesion = (int)$session['idUsuario'];

    // Un superusuario (admin/jefe) puede importar para cualquier profesor;
    // un profesor, solo para sí mismo (y debe estar activo).
    $datos = cuerpoJson();
    if (esUsuarioSuper($rol)) {
        $idProfesor = datosOptimoInt($datos, 'idProfesor', $idUsuarioSesion);
    } else {
        if (!isset($session['activo']) || $session['activo'] != 1) {
            throw new Exception('No tiene permisos para realizar esta acción');
        }
        $idProfesor = $idUsuarioSesion;
    }

    $idMateria = datosOptimoInt($datos, 'idMateria');
    $idGrupo = datosOptimoInt($datos, 'idGrupo');
    if ($idMateria <= 0 || $idGrupo <= 0 || $idProfesor <= 0) {
        throw new Exception('Parámetros no válidos');
    }

    // Verificar que la propuesta pedagógica esté marcada como terminada
    $fila = $db->fetchOne("SELECT terminada_programacion FROM materias WHERE id = ?", $idMateria);
    if ($fila === null || $fila['terminada_programacion'] != 1) {
        throw new Exception('La propuesta pedagógica de esta materia no está marcada como terminada; no se puede importar la programación de aula');
    }

    // Verificar que el profesor imparta la materia en el grupo (escenario actual)
    $fila = $db->fetchOne(
        "SELECT s.id FROM seleccion s
            JOIN escenarios_desideratas e ON e.id = s.idEscenario
           WHERE s.idMateria = ? AND s.idGrupo = ? AND s.idProfesor = ?
             AND e.actual = 1
             LIMIT 1", $idMateria, $idGrupo, $idProfesor);
    if ($fila === null) {
        throw new Exception('El profesor no imparte esta materia en el grupo elegido');
    }

    try {
        $db->begin();

        // Borrar una copia previa (si la hay) para el (profesor, grupo) elegido.
        // El orden importa: primero las tablas de vínculo (CE, CE-cálogo,
        // competencias), luego RA y temas.
        $db->execute("DELETE FROM criterios_temas_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?", $idMateria, $idGrupo, $idProfesor);
        $db->execute("DELETE FROM criterios_evaluacion_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?", $idMateria, $idGrupo, $idProfesor);
        $db->execute("DELETE FROM competencias_temas_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?", $idMateria, $idGrupo, $idProfesor);
        $db->execute("DELETE FROM resultados_aprendizaje_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?", $idMateria, $idGrupo, $idProfesor);
        $db->execute("DELETE FROM temas_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?", $idMateria, $idGrupo, $idProfesor);
        $db->execute("DELETE FROM contenidos_programaciones_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?", $idMateria, $idGrupo, $idProfesor);

        // --- Copia de los contenidos de apartados (como antes) ---
        $db->execute(
            "INSERT INTO contenidos_programaciones_aula (idMateria, idApartado, idGrupo, idProfesor, texto)
                 SELECT idMateria, idApartado, ?, ?, texto
                 FROM contenidos_programaciones
                 WHERE idMateria = ?",
            $idGrupo, $idProfesor, $idMateria);

        // --- Copia de los resultados de aprendizaje (RA) ---
        // Se inserta con el idMateria de origen y se deja el id original en un
        // mapa orden->nuevoId para re-vincular los CE.
        $db->execute(
            "INSERT INTO resultados_aprendizaje_aula (idMateria, idGrupo, idProfesor, orden, texto, porcentaje_empresa, porcentaje_evaluacion, es_clave)
                 SELECT idMateria, ?, ?, orden, texto, porcentaje_empresa, porcentaje_evaluacion, es_clave
                 FROM resultados_aprendizaje
                 WHERE idMateria = ?
                 ORDER BY orden",
            $idGrupo, $idProfesor, $idMateria);

        // --- Copia de las unidades (temas) ---
        $db->execute(
            "INSERT INTO temas_aula (idMateria, idGrupo, idProfesor, orden, titulo, horas, trimestre, peso_evaluacion,
                                     descripcion, justificacion, contexto, contenidos, secuenciacion,
                                     recursos, evaluacion, metodologia, adaptaciones,
                                     contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto)
                 SELECT idMateria, ?, ?, orden, titulo, horas, trimestre, peso_evaluacion,
                        descripcion, justificacion, contexto, contenidos, secuenciacion,
                        recursos, evaluacion, metodologia, adaptaciones,
                        contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto
                 FROM temas
                 WHERE idMateria = ?
                 ORDER BY orden",
            $idGrupo, $idProfesor, $idMateria);

        $db->commit();
    } catch (DbException $e) {
        $db->rollback();
        throw $e;
    }

    // --- Re-vincular los criterios de evaluación (CE) por orden ---
    // Tras el commit, los ids nuevos ya están en resultados_aprendizaje_aula y
    // temas_aula. Se reconstruye el mapa orden->id de cada tabla y se inserta
    // cada CE con los nuevos ids.
    try {
        $db->begin();

        // Mapa orden -> id de RA de aula (la copia que acabamos de hacer)
        $raAula = $db->fetchAll(
            "SELECT id, orden FROM resultados_aprendizaje_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?",
            $idMateria, $idGrupo, $idProfesor);
        $raPorOrden = array();
        foreach ($raAula as $r) {
            $raPorOrden[(int)$r['orden']] = (int)$r['id'];
        }

        // Mapa orden -> id de temas de aula
        $temasAula = $db->fetchAll(
            "SELECT id, orden FROM temas_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?",
            $idMateria, $idGrupo, $idProfesor);
        $temaPorOrden = array();
        foreach ($temasAula as $t) {
            $temaPorOrden[(int)$t['orden']] = (int)$t['id'];
        }

        // Mapas de id compartido (propuesta) -> id de aula, para copiar los
        // textos de CE (claveados en RA) y las competencias (claveadas en
        // tema) traduciendo sus ids a los de la copia.
        $sharedRAIdToNewId = array();
        foreach ($db->fetchAll("SELECT id, orden FROM resultados_aprendizaje WHERE idMateria = ?", $idMateria) as $r) {
            $ordenRA = (int)$r['orden'];
            if (isset($raPorOrden[$ordenRA])) {
                $sharedRAIdToNewId[(int)$r['id']] = $raPorOrden[$ordenRA];
            }
        }
        $sharedTemaIdToNewId = array();
        foreach ($db->fetchAll("SELECT id, orden FROM temas WHERE idMateria = ?", $idMateria) as $t) {
            $ordenTema = (int)$t['orden'];
            if (isset($temaPorOrden[$ordenTema])) {
                $sharedTemaIdToNewId[(int)$t['id']] = $temaPorOrden[$ordenTema];
            }
        }

        // CE de la propuesta (origen), con su orden de RA y de tema para re-vincular
        $ces = $db->fetchAll(
            "SELECT c.idRA, c.codigo, c.idTema,
                   ra.orden AS ordenRA, t.orden AS ordenTema
                 FROM criterios_temas c
                 JOIN resultados_aprendizaje ra ON ra.id = c.idRA
                 JOIN temas t ON t.id = c.idTema
                 WHERE ra.idMateria = ? AND t.idMateria = ?
                 ORDER BY ra.orden, t.orden",
            $idMateria, $idMateria);
        foreach ($ces as $ce) {
            $nuevoIdRA = isset($raPorOrden[(int)$ce['ordenRA']]) ? $raPorOrden[(int)$ce['ordenRA']] : 0;
            $nuevoIdTema = isset($temaPorOrden[(int)$ce['ordenTema']]) ? $temaPorOrden[(int)$ce['ordenTema']] : 0;
            if ($nuevoIdRA > 0 && $nuevoIdTema > 0) {
                $db->execute(
                    "INSERT INTO criterios_temas_aula (idMateria, idRA, codigo, idTema, idGrupo, idProfesor) VALUES (?, ?, ?, ?, ?, ?)",
                    $idMateria, $nuevoIdRA, $ce['codigo'], $nuevoIdTema, $idGrupo, $idProfesor);
            }
        }

        // --- Copia de los textos de los criterios de evaluación (CE) ---
        // Se re-vinculan al RA de aula (los ids de RA cambian).
        foreach ($db->fetchAll(
            "SELECT ce.idRA AS idRA, ce.codigo AS codigo, ce.texto AS texto
                  FROM criterios_evaluacion ce
                  INNER JOIN resultados_aprendizaje ra ON ra.id = ce.idRA
                  WHERE ra.idMateria = ?", $idMateria) as $ceCat) {
            $nuevoIdRA = isset($sharedRAIdToNewId[(int)$ceCat['idRA']]) ? $sharedRAIdToNewId[(int)$ceCat['idRA']] : 0;
            if ($nuevoIdRA > 0) {
                $db->execute(
                    "INSERT INTO criterios_evaluacion_aula (idMateria, idRA, codigo, texto, idGrupo, idProfesor) VALUES (?, ?, ?, ?, ?, ?)",
                    $idMateria, $nuevoIdRA, $ceCat['codigo'], $ceCat['texto'], $idGrupo, $idProfesor);
            }
        }

        // --- Copia de las competencias de cada unidad ---
        // Se re-vinculan al tema de aula (los ids de tema cambian).
        foreach ($db->fetchAll(
            "SELECT ct.idCompetencia AS idCompetencia, ct.idTema AS idTema
                  FROM competencias_temas ct
                  INNER JOIN temas t ON t.id = ct.idTema
                  WHERE t.idMateria = ?", $idMateria) as $com) {
            $nuevoIdTema = isset($sharedTemaIdToNewId[(int)$com['idTema']]) ? $sharedTemaIdToNewId[(int)$com['idTema']] : 0;
            if ($nuevoIdTema > 0) {
                $db->execute(
                    "INSERT INTO competencias_temas_aula (idMateria, idCompetencia, idTema, idGrupo, idProfesor) VALUES (?, ?, ?, ?, ?)",
                    $idMateria, $com['idCompetencia'], $nuevoIdTema, $idGrupo, $idProfesor);
            }
        }

        $db->commit();
    } catch (DbException $e) {
        $db->rollback();
        throw $e;
    }

    $db->close();
    sendJSONSuccess(null, 'Programación de aula creada a partir de la propuesta pedagógica');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
