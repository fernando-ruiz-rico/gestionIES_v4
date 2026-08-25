<?php
// API de selección (Desideratas): vaciar la selección del profesor.
// v3: si no hay permisos, solo se quitan las materias que no haya
// asignado la directiva (v3/borrar_toda_seleccion.php)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

// "super" = jefe de departamento o admin
$super = in_array($usuario['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = cuerpoJson();
if ($datos === null) {
    sendJSONError('Faltan datos', 400);
}
$idProfesor = datosOptimoInt($datos, 'idProfesor');
$idEscenario = datosOptimoInt($datos, 'idEscenario', getOptimoInt('idEscenario'));
if ($idProfesor <= 0 || $idEscenario <= 0) {
    sendJSONError('Faltan parámetros', 400);
}
try {
    $db = Db::open();
    if ($super) {
        $db->execute("DELETE FROM seleccion WHERE idProfesor = ? AND idEscenario = ?", $idProfesor, $idEscenario);
    } else {
        $db->execute("DELETE FROM seleccion
                      WHERE idProfesor = ? AND idEscenario = ?
                        AND idMateria NOT IN (SELECT id FROM materias WHERE asignada_directiva = 1)", $idProfesor, $idEscenario);
    }
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
sendJSONSuccess(null, 'Selección vaciada');
