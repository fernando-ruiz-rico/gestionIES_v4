<?php
// API endpoint para cargar contenido por defecto de un apartado
require_once '../../config.php';
cabeceraJson();

$idApartado = getOptimoInt('idApartado');
$idDepartamento = getOptimoInt('idDepartamento');

if ($idApartado <= 0 || $idDepartamento <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

$db = Db::open();

try {
    $fila = $db->fetchOne("SELECT texto FROM contenidos_defecto_programaciones WHERE idApartado = ? AND idDepartamento = ?", $idApartado, $idDepartamento);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

$db->close();

if ($fila !== null) {
    sendJSONSuccess(array('texto' => $fila['texto']));
} else {
    sendJSONSuccess(array('texto' => ''));
}
?>
