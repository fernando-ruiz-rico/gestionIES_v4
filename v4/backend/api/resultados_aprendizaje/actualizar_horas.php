<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// actualiza las horas de docencia en empresa de la materia
require_once '../../config.php';
require_once '../../lib/resultados_aprendizaje.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    if (!raTienePermisoEdicion()) {
        throw new Exception('No tiene permisos para realizar esta acción');
    }

    $idMateria = intval($datos['idMateria']);
    $horas = intval($datos['horas']);
    if ($idMateria <= 0) {
        throw new Exception('ID de materia inválido');
    }

    raComprobarDepartamento(raIdDepartamentoDeMateria($db, $idMateria));

    $db->execute("UPDATE materias SET horas_empresa = ? WHERE id = ?", $horas, $idMateria);
    $db->close();
    sendJSONSuccess(null, 'Horas de empresa actualizadas');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
