<?php
// Programaciones de aula — Insertar una nueva unidad (tema) en la copia de
// aula de un (materia, grupo, profesor). Espejo de api/temas/nuevo.php.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idMateria  = datosOptimoInt($body, 'idMateria');
    $idGrupo    = datosOptimoInt($body, 'idGrupo');
    $idProfesor = datosOptimoInt($body, 'idProfesor');
    $orden      = datosOptimoInt($body, 'orden');
    $titulo     = trim(datosOptimo($body, 'titulo'));

    if ($idMateria <= 0 || $idGrupo <= 0 || $idProfesor <= 0 || $orden <= 0 || $titulo === '') {
        throw new Exception('Indica la materia, el grupo, el profesor, el número y el título del tema');
    }

    // Nota: como en el endpoint compartido, el título se inserta vacío y se
    // actualiza a continuación para respetar el texto introducido.
    $db->execute("INSERT INTO temas_aula
                    (idMateria, idGrupo, idProfesor, orden, titulo, horas, trimestre, peso_evaluacion,
                     descripcion, justificacion, contexto, contenidos, secuenciacion,
                     recursos, evaluacion, metodologia, adaptaciones,
                     contexto_defecto, recursos_defecto, metodologia_defecto, adaptaciones_defecto)
                    VALUES (?, ?, ?, ?, '', 0, 0, 0, '', '', '', '', '', '', '', '', '', 1, 1, 1, 1)",
                    $idMateria, $idGrupo, $idProfesor, $orden);
    $nuevoId = $db->insertId();
    $db->execute("UPDATE temas_aula SET titulo = ? WHERE id = ?", $titulo, $nuevoId);

    $db->close();
    sendJSONSuccess(['id' => $nuevoId], 'Tema creado correctamente');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
