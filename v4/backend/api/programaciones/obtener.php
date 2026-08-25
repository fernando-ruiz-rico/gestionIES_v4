<?php
// FASE 2.1 — Ver programación (solo lectura): apartados + contenidos
// de la materia tal y como están guardados en v3.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();

    $idMateria = intval($_GET['idMateria']);
    if ($idMateria <= 0) {
        throw new Exception('ID de materia inválido');
    }

    $sql = "SELECT ap.id AS idApartado, ap.titulo, c.texto
                FROM apartados_programaciones ap
                JOIN contenidos_programaciones c ON c.idApartado = ap.id AND c.idMateria = ?
                ORDER BY ap.orden, c.id";

    // Un apartado puede tener varios contenidos; se agrupan por orden de id.
    $apartados = array();
    $posicion = array();
    foreach ($db->fetchAll($sql, $idMateria) as $fila) {
        $idA = $fila['idApartado'];
        if (!isset($posicion[$idA])) {
            $posicion[$idA] = count($apartados);
            $apartados[] = [
                'idApartado' => $idA,
                'titulo'     => $fila['titulo'],
                'texto'      => $fila['texto']
            ];
        } else {
            $apartados[$posicion[$idA]]['texto'] .= "\n" . $fila['texto'];
        }
    }

    $db->close();

    if (empty($apartados)) {
        sendJSONError('La materia no tiene programación', 404);
    } else {
        sendJSONSuccess($apartados);
    }
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
