<?php
// Programaciones de aula — Acordeón RA/CE + competencias (nivel materia)
// de la copia de aula. Espejo de api/temas/accordion_ra_ce.php: los RA y sus
// CE salen de las tablas de aula (resultados_aprendizaje_aula /
// criterios_evaluacion_aula); las competencias siguen leyendo el catálogo
// compartido (competencias_ciclos), que no se copia.
require_once '../../config.php';
require_once '../../lib/temas.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $idMateria  = getOptimoInt('idMateria');
    $idGrupo    = getOptimoInt('idGrupo');
    $idProfesor = getOptimoInt('idProfesor');
    if ($idMateria <= 0 || $idGrupo <= 0 || $idProfesor <= 0) {
        throw new Exception('Debe indicar la materia, el grupo y el profesor');
    }

    $idCiclo = temas_id_ciclo_por_materia($db, $idMateria);

    $ra = array();
    $total = 0;
    foreach ($db->fetchAll("SELECT id, orden, texto, porcentaje_evaluacion, es_clave
                    FROM resultados_aprendizaje_aula
                    WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?
                    ORDER BY orden", $idMateria, $idGrupo, $idProfesor) as $fila) {
        $idRA = intval($fila['id']);
        $total += intval($fila['porcentaje_evaluacion']);

        $ce = array();
        foreach ($db->fetchAll("SELECT codigo, texto FROM criterios_evaluacion_aula WHERE idRA = ? ORDER BY codigo", $idRA) as $c) {
            $ce[] = ['idRA' => $idRA, 'codigo' => $c['codigo'], 'texto' => $c['texto']];
        }

        $ra[] = [
            'id' => $idRA,
            'orden' => intval($fila['orden']),
            'texto' => $fila['texto'],
            'porcentaje_evaluacion' => intval($fila['porcentaje_evaluacion']),
            'es_clave' => intval($fila['es_clave']),
            'ce' => $ce
        ];
    }

    $competencias = temas_competencias_materia($db, $idMateria, $idCiclo);
    $db->close();

    sendJSONSuccess([
        'idCiclo' => $idCiclo,
        'ra' => $ra,
        'total' => $total,
        'competencias' => $competencias
    ]);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
