<?php
// FASE 2.7 — Cargar los contenidos por defecto de un departamento
// (contexto, recursos, metodología, acciones).
// Equivalente a v3: temas_contenidos_defecto.php + ajax/temas_contenidos_defecto/.
require_once '../../config.php';
cabeceraJson();

$session = checkSession();
$permisos = in_array($session['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));
if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

try {
    $db = Db::open();

    $idDepartamento = getOptimoInt('idDepartamento');
    if ($idDepartamento <= 0) {
        throw new Exception('Debe indicar un departamento');
    }

    $fila = $db->fetchOne("SELECT contexto, recursos, metodologia, adaptaciones
                FROM contenidos_defecto_temas WHERE idDepartamento = ?", $idDepartamento);

    $db->close();
    sendJSONSuccess(array(
        'contexto' => $fila ? $fila['contexto'] : '',
        'recursos' => $fila ? $fila['recursos'] : '',
        'metodologia' => $fila ? $fila['metodologia'] : '',
        'adaptaciones' => $fila ? $fila['adaptaciones'] : ''
    ));
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
