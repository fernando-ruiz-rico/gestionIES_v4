<?php
// API de Configuración: devuelve el estado de configuración (Fase 7.3)
// Fiel a v3: expone la tabla 'config'.
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

try {
    $db = Db::open();

    // El frontend espera {data: {activaciones: {evaluacionRA, seleccion}}};
    // la evaluación de RA la controla la fila "programaciones" y la
    // selección de materias la fila "desideratas" (mismo modelo que v3).
    $programaciones = getConfigValue($db, 'programaciones');
    $desideratas = getConfigValue($db, 'desideratas');
    $db->close();

    sendJSONSuccess(array(
        'activaciones' => array(
            'evaluacionRA' => ($programaciones === 'ON'),
            'seleccion'    => ($desideratas === 'ON')
        )
    ));
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}

// Consulta un valor de la tabla config
function getConfigValue($db, $clave)
{
    $fila = $db->fetchOne("SELECT valor FROM config WHERE clave = ?", $clave);
    return $fila ? $fila['valor'] : null;
}
