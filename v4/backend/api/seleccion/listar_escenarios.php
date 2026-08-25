<?php
// API de selección (Desideratas): escenarios elegibles para el desplegable.
// Si es super ve los del departamento; si no, solo los activos en este momento.
// (v3/cargar_escenarios.php y v3/cargar_escenarios_profesor.php)
require_once '../../config.php';
cabeceraJson();

// Fiel a v3: el módulo de Desideratas exige sesión iniciada
$usuario = checkSession();

// "super" = jefe de departamento o admin (v3 lo usa en casi todas las páginas)
$super = in_array($usuario['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$idDepartamento = getOptimoInt('idDepartamento');
if ($idDepartamento <= 0) {
    sendJSONError('Departamento inválido', 400);
}
try {
    $db = Db::open();
    if ($super) {
        $filas = $db->fetchAll("SELECT id, nombre
                                FROM escenarios_desideratas
                                WHERE id IN (SELECT idEscenario
                                             FROM departamentos_escenarios
                                             WHERE idDepartamento = ?)
                                ORDER BY nombre", $idDepartamento);
    } else {
        // v3/cargar_escenarios_profesor.php: solo los elegibles (activo_desideratas = 1)
        $filas = $db->fetchAll("SELECT id, nombre
                                FROM escenarios_desideratas
                                WHERE id IN (SELECT idEscenario
                                             FROM departamentos_escenarios
                                             WHERE idDepartamento = ?)
                                AND activo_desideratas = 1
                                ORDER BY nombre", $idDepartamento);
    }
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
sendJSONSuccess($filas);
