<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// asocia un nuevo criterio de evaluación a un resultado
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

    $idResultado = intval($datos['idResultado']);
    $codigo = $datos['codigo'];
    $texto = $datos['texto'];
    if ($idResultado <= 0 || empty($codigo)) {
        throw new Exception('Datos incompletos para guardar el criterio');
    }

    raComprobarDepartamento(raIdDepartamentoDeRA($db, $idResultado));

    $texto = empty($texto) ? '' : $texto;
    $db->execute("INSERT INTO criterios_evaluacion (idRA, codigo, texto) VALUES (?, ?, ?)", $idResultado, $codigo, $texto);
    $nuevoId = $db->insertId();
    $db->close();
    sendJSONSuccess(array('id' => $nuevoId), 'Criterio de evaluación guardado');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
