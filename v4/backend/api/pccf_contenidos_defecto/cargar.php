<?php
// API para cargar el contenido por defecto de un apartado del PCCF (Fase 3.3)
// Devuelve el texto asociado a un apartado y un departamento concretos.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idApartado = isset($_GET['idApartado']) ? intval($_GET['idApartado']) : 0;
$idDepartamento = isset($_GET['idDepartamento']) ? intval($_GET['idDepartamento']) : 0;

if ($idApartado <= 0 || $idDepartamento <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();

    $fila = $db->fetchOne("SELECT texto FROM contenidos_defecto_pccf WHERE idApartado = ? AND idDepartamento = ?", $idApartado, $idDepartamento);

    $texto = '';
    if ($fila) {
        $texto = $fila['texto'];
    }

    sendJSONSuccess(['texto' => $texto]);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
