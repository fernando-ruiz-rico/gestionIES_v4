<?php
// API de selección (Desideratas): reordenar las prioridades de selección del
// profesor (v3/ordenar_seleccion.php).
// v3: si el escenario está en modo rueda y no hay permisos, la operación no se hace
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

// "super" = jefe de departamento o admin
$super = in_array($usuario['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = cuerpoJson();
if ($datos === null) {
    sendJSONError('Faltan datos', 400);
}
$ids = isset($datos['ids']) && is_array($datos['ids']) ? $datos['ids'] : array();
$idEscenario = getOptimoInt('idEscenario');
if (count($ids) === 0 || $idEscenario <= 0) {
    sendJSONError('Faltan parámetros', 400);
}
try {
    $db = Db::open();
    $fila = $db->fetchOne("SELECT modo_rueda FROM escenarios_desideratas WHERE id = ?", $idEscenario);
    if ($fila && $fila['modo_rueda'] && !$super) {
        sendJSONError('El escenario está en modo rueda; no se pueden reordenar las selecciones', 400);
    }
    // "ids" lleva los ids de la selección en el orden nuevo; a cada uno se le asigna su posición
    foreach ($ids as $posicion => $id) {
        $db->execute("UPDATE seleccion SET orden = ? WHERE id = ?", $posicion + 1, intval($id));
    }
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
sendJSONSuccess(null, 'Reordenado');
