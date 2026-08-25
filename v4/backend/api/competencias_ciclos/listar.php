<?php
// API para la gestión de Competencias por Ciclo (Fase 4.2):
// lista las competencias de un ciclo
// Fiel a v3: las competencias se almacenan en competencias_ciclos, una fila
// por competencia (con su código, texto, tipo e id de ciclo).
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $idCiclo = getOptimoInt('idCiclo');
    if ($idCiclo <= 0) {
        throw new Exception('ID de ciclo inválido');
    }

    $sql = "SELECT * FROM competencias_ciclos WHERE idCiclo = ? ORDER BY orden";
    $competencias = $db->fetchAll($sql, $idCiclo);
    sendJSONSuccess($competencias);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
