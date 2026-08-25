<?php
// API para la gestión de Actas de departamentos (Fase 6.1):
// lista las actas de un departamento (más reciente primero)
// Fiel a v3: las actas se almacenan en actas_departamentos, una fila por acta.
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $idDepartamento = getOptimoInt('idDepartamento');
    if ($idDepartamento <= 0) {
        throw new Exception('ID de departamento inválido');
    }

    $actas = $db->fetchAll("SELECT id, fecha FROM actas_departamentos WHERE idDepartamento=? ORDER BY fecha DESC", $idDepartamento);
    sendJSONSuccess($actas);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
