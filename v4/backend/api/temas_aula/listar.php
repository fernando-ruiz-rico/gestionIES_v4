<?php
// Programaciones de aula — Listar las unidades (temas) de la copia de aula de
// un (materia, grupo, profesor) (+ horas anuales para los totales).
// Espejo de api/temas/listar.php sobre temas_aula.
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

    $temas = array();
    foreach ($db->fetchAll("SELECT id, orden, titulo, horas, peso_evaluacion
                    FROM temas_aula
                    WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?
                    ORDER BY orden", $idMateria, $idGrupo, $idProfesor) as $fila) {
        $temas[] = [
            'id' => intval($fila['id']),
            'orden' => intval($fila['orden']),
            'titulo' => $fila['titulo'],
            'horas' => intval($fila['horas']),
            'peso_evaluacion' => intval($fila['peso_evaluacion'])
        ];
    }

    // Las horas anuales son las de la materia compartida (no cambia con la copia)
    $horasAnuales = temas_horas_anuales($db, $idMateria);
    $db->close();
    sendJSONSuccess([
        'temas' => $temas,
        'horas_anuales' => $horasAnuales
    ]);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
