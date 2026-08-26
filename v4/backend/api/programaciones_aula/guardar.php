<?php
// FASE 2.4 — Guardar el texto de un apartado de la programación de aula
// (materia + apartado + grupo + profesor). Mismo patrón que el guardado de
// la propuesta pedagógica: si no hay cambios (sin_cambios = true), no
// toca la base de datos.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();

    $session = checkSession();
    $rol = $session['rol'];
    $idUsuarioSesion = (int)$session['idUsuario'];

    $datos = cuerpoJson();

    // Un superusuario (admin/jefe) puede guardar para cualquier profesor; un
    // profesor solo puede guardar para sí mismo y debe estar activo.
    if (esUsuarioSuper($rol)) {
        $idProfesor = datosOptimoInt($datos, 'idProfesor', $idUsuarioSesion);
    } else {
        if (!isset($session['activo']) || $session['activo'] != 1) {
            throw new Exception('No tiene permisos para realizar esta acción');
        }
        $idProfesor = $idUsuarioSesion;
    }

    $idMateria = datosOptimoInt($datos, 'idMateria');
    $idApartado = datosOptimoInt($datos, 'idApartado');
    $idGrupo = datosOptimoInt($datos, 'idGrupo');
    $sinCambios = datosOptimo($datos, 'sin_cambios');
    $texto = datosOptimo($datos, 'texto');

    if ($idMateria <= 0 || $idApartado <= 0 || $idGrupo <= 0 || $idProfesor <= 0) {
        throw new Exception('Parámetros no válidos');
    }

    if ($sinCambios !== true && $sinCambios !== 'true') {
        $fila = $db->fetchOne(
            "SELECT texto FROM contenidos_programaciones_aula
             WHERE idMateria = ? AND idApartado = ? AND idGrupo = ? AND idProfesor = ?",
            $idMateria, $idApartado, $idGrupo, $idProfesor);

        if ($fila === null) {
            $db->execute(
                "INSERT INTO contenidos_programaciones_aula (idMateria, idApartado, idGrupo, idProfesor, texto) VALUES (?, ?, ?, ?, ?)",
                $idMateria, $idApartado, $idGrupo, $idProfesor, $texto);
        } else {
            $db->execute(
                "UPDATE contenidos_programaciones_aula SET texto = ? WHERE idMateria = ? AND idApartado = ? AND idGrupo = ? AND idProfesor = ?",
                $texto, $idMateria, $idApartado, $idGrupo, $idProfesor);
        }
    }

    $db->close();
    sendJSONSuccess(array('sin_cambios' => ($sinCambios === true || $sinCambios === 'true')), 'Contenido guardado');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
