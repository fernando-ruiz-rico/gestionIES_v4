<?php
// API de selección (Desideratas): especialidades del departamento
// (para el filtro del panel de profesores)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

$idDepartamento = getOptimoInt('idDepartamento');
if ($idDepartamento <= 0) {
    sendJSONError('Departamento inválido', 400);
}
try {
    $db = Db::open();
    $filas = $db->fetchAll("SELECT id, descripcion
                            FROM especialidades
                            WHERE idDepartamento = ?
                            ORDER BY id", $idDepartamento);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
sendJSONSuccess($filas);
