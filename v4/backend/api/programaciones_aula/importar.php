<?php
// FASE 2.4 — Importar la programación de aula: hace una copia, para el
// profesor y el grupo elegidos, de la propuesta pedagógica (contenidos
// de apartados) de la materia elegida. Opción propia de v4.
//
// Requisitos:
//   - La propuesta pedagógica de la materia debe estar marcada como
//     terminada (materias.terminada_programacion = 1).
//   - El profesor debe impartir la materia en el grupo en el escenario
//     actual.
// Si ya existía una programación de aula para ese profesor+grupo+materia,
// se reemplaza por la copia nueva.
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

        // Borrar una copia previa (si la hay) y copiar los contenidos de la
        // propuesta pedagógica de la materia elegida.
        $db->execute("DELETE FROM contenidos_programaciones_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ?", $idMateria, $idGrupo, $idProfesor);
        $db->execute(
            "INSERT INTO contenidos_programaciones_aula (idMateria, idApartado, idGrupo, idProfesor, texto)
                 SELECT idMateria, idApartado, ?, ?, texto
                 FROM contenidos_programaciones
                 WHERE idMateria = ?",
            $idGrupo, $idProfesor, $idMateria);

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
