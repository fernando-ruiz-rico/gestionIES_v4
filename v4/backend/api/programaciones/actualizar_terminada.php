<?php
// FASE 2.1 — Marcar la propuesta pedagógica de una materia como terminada o no.
// Dato propio de v4 (materias.terminada_programacion): indica que la propuesta
// pedagógica está terminada, y es lo que habilita importar la programación de
// aula a partir de ella.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $datos = cuerpoJson();
    $idMateria = datosOptimoInt($datos, 'idMateria');
    if ($idMateria <= 0) {
        throw new Exception('ID de materia inválido');
    }

    $terminada = datosOptimo($datos, 'terminada');
    if ((is_int($terminada) && $terminada >= 0 && $terminada <= 1) || ($terminada === '0' || $terminada === '1')) {
        $valor = intval($terminada) === 1 ? 1 : 0;
    } else {
        throw new Exception('Valor de "terminada" inválido');
    }

    $db->execute("UPDATE materias SET terminada_programacion = ? WHERE id = ?", $valor, $idMateria);
    $db->close();
    sendJSONSuccess(null, $valor ? 'Propuesta marcada como terminada' : 'Propuesta marcada como no terminada');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
