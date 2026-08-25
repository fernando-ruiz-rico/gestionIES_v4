<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// carga los RA (y sus CE) de una materia. Solo lectura.
require_once '../../config.php';
require_once '../../lib/resultados_aprendizaje.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $idMateria = getOptimoInt('idMateria');
    if ($idMateria <= 0) {
        throw new Exception('ID de materia inválido');
    }

    // Horas de docencia en empresa para la materia
    $fila = $db->fetchOne("SELECT horas_empresa FROM materias WHERE id = ?", $idMateria);
    $horasEmpresa = $fila ? $fila['horas_empresa'] : 0;

    $resultados = $db->fetchAll("SELECT * FROM resultados_aprendizaje WHERE idMateria = ? ORDER BY orden", $idMateria);

    $db->close();
    sendJSONSuccess(array(
        'idMateria' => $idMateria,
        'horas_empresa' => $horasEmpresa,
        'permisos' => raTienePermisoEdicion(),
        'resultados' => $resultados
    ));
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
