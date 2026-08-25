<?php
// FASE 2.1 — Guardar el texto de un apartado editable (v3/insertar_contenido_programacion).
// Permiso: sesión válida (v3 lo permite a cualquier usuario con sesión, y la
// visibilidad del editor ya la controla la vista según rol/activación).
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $datos = cuerpoJson();
    $idMateria = datosOptimoInt($datos, 'idMateria');
    $idApartado = datosOptimoInt($datos, 'idApartado');
    if ($idMateria <= 0 || $idApartado <= 0) {
        throw new Exception('ID de materia o apartado inválido');
    }
    $texto = datosOptimo($datos, 'texto');

    $fila = $db->fetchOne(
        "SELECT id FROM contenidos_programaciones WHERE idMateria = ? AND idApartado = ?",
        $idMateria, $idApartado);

    // Fiel a v3/insertar_contenido_programacion.php: si el texto es
    // idéntico al ya guardado, MySQL no modifica filas → "sin cambios".
    $existia = (bool)$fila;
    if ($existia) {
        $modificadas = $db->execute(
            "UPDATE contenidos_programaciones SET texto = ? WHERE idMateria = ? AND idApartado = ?",
            $texto, $idMateria, $idApartado);
    } else {
        $modificadas = $db->execute(
            "INSERT INTO contenidos_programaciones (idMateria, idApartado, texto) VALUES (?, ?, ?)",
            $idMateria, $idApartado, $texto);
    }

    $db->close();

    $sinCambios = !$existia ? false : ($modificadas == 0);
    sendJSONSuccess(
        array('sin_cambios' => (bool)$sinCambios),
        $sinCambios ? 'El contenido ya estaba guardado así' : 'Contenido guardado correctamente');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
