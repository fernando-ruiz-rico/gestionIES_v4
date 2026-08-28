<?php
// Programaciones de aula — Datos de una unidad (tema) de la copia de aula
// (prefill del formulario) + CE/competencias. Espejo de api/temas/obtener.php
// sobre temas_aula / criterios_temas_aula / competencias_temas_aula.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $idTema = getOptimoInt('idTema');
    if ($idTema <= 0) {
        throw new Exception('Debe indicar un tema');
    }

    $fila = $db->fetchOne("SELECT * FROM temas_aula WHERE id = ?", $idTema);
    if (!$fila) {
        $db->close();
        sendJSONError('Tema no encontrado', 404);
    }

    $criterios = array();
    foreach ($db->fetchAll("SELECT idRA, codigo FROM criterios_temas_aula WHERE idTema = ?", $idTema) as $c) {
        $criterios[] = ['idRA' => intval($c['idRA']), 'codigo' => $c['codigo']];
    }

    $competencias = array();
    foreach ($db->fetchAll("SELECT idCompetencia FROM competencias_temas_aula WHERE idTema = ?", $idTema) as $c) {
        $competencias[] = intval($c['idCompetencia']);
    }

    $idMateria = intval($fila['idMateria']);

    // El contenido por defecto compartido del departamento de la materia:
    // el formulario muestra en cada apartado con «dejar valores por
    // defecto» exactamente lo que se imprime (PDF: pgGenerarContenidoTemas)
    // — si la unidad no tiene contenido propio, el texto compartido del
    // catálogo (contenidos_defecto_temas).
    $defecto = $db->fetchOne(
        "SELECT cd.contexto, cd.recursos, cd.metodologia, cd.adaptaciones
           FROM materias m
           LEFT JOIN contenidos_defecto_temas cd ON cd.idDepartamento = m.idDepartamento
          WHERE m.id = ?", $idMateria);
    $db->close();

    sendJSONSuccess([
        'tema' => [
            'id' => intval($fila['id']),
            'idMateria' => $idMateria,
            'orden' => intval($fila['orden']),
            'titulo' => $fila['titulo'],
            'horas' => intval($fila['horas']),
            'trimestre' => intval($fila['trimestre']),
            'peso_evaluacion' => intval($fila['peso_evaluacion']),
            'descripcion' => $fila['descripcion'],
            'justificacion' => $fila['justificacion'],
            'contexto' => $fila['contexto'],
            'contenidos' => $fila['contenidos'],
            'secuenciacion' => $fila['secuenciacion'],
            'recursos' => $fila['recursos'],
            'evaluacion' => $fila['evaluacion'],
            'metodologia' => $fila['metodologia'],
            'adaptaciones' => $fila['adaptaciones'],
            'contexto_defecto' => intval($fila['contexto_defecto']),
            'recursos_defecto' => intval($fila['recursos_defecto']),
            'metodologia_defecto' => intval($fila['metodologia_defecto']),
            'adaptaciones_defecto' => intval($fila['adaptaciones_defecto'])
        ],
        'criterios' => $criterios,
        'competencias' => $competencias,
        'contenidosDefecto' => array(
            'contexto' => $defecto ? $defecto['contexto'] : '',
            'recursos' => $defecto ? $defecto['recursos'] : '',
            'metodologia' => $defecto ? $defecto['metodologia'] : '',
            'adaptaciones' => $defecto ? $defecto['adaptaciones'] : ''
        )
    ]);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
