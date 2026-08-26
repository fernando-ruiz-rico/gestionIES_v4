<?php
// FASE 2.4 — Cargar el texto de un apartado de la programación de aula
// (materia + apartado + grupo + profesor), para el editor TinyMCE.
// La programación de aula es una copia, por profesor y grupo, de la
// propuesta pedagógica (contenidos_programaciones) de la materia.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();

    $session = checkSession();
    $idMateria = getOptimoInt('idMateria');
    $idApartado = getOptimoInt('idApartado');
    $idGrupo = getOptimoInt('idGrupo');
    if ($idMateria <= 0 || $idApartado <= 0 || $idGrupo <= 0) {
        throw new Exception('Parámetros no válidos');
    }

    // Un superusuario (admin/jefe) puede ver la de cualquier profesor; un
    // profesor, solo la suya.
    $rol = $session['rol'];
    $idUsuarioSesion = (int)$session['idUsuario'];
    if (esUsuarioSuper($rol)) {
        $idProfesor = getOptimoInt('idProfesor', $idUsuarioSesion);
    } else {
        $idProfesor = $idUsuarioSesion;
    }

    $fila = $db->fetchOne(
        "SELECT texto FROM contenidos_programaciones_aula
          WHERE idMateria = ? AND idApartado = ? AND idGrupo = ? AND idProfesor = ?",
        $idMateria, $idApartado, $idGrupo, $idProfesor);

    $db->close();
    sendJSONSuccess(array('texto' => ($fila !== null) ? $fila['texto'] : ''));
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
