<?php
// API de selección (Desideratas): vaciar todas las selecciones del escenario.
// Solo jefe de departamento o admin (v3/borrar_todas_selecciones.php)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

// "super" = jefe de departamento o admin
$super = in_array($usuario['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$idEscenario = getOptimoInt('idEscenario');

if (!$super) {
    sendJSONError('Permisos insuficientes', 403);
}
if ($idEscenario <= 0) {
    sendJSONError('Escenario inválido', 400);
}
try {
    $db = Db::open();
    $db->execute("DELETE FROM seleccion WHERE idEscenario = ?", $idEscenario);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
sendJSONSuccess(null, 'Escenario vaciado');
