<?php
// API para duplicar un escenario de desideratas (fiel a v3/duplicar_escenario.php):
// - nuevo escenario con el mismo nombre y el sufijo " bis"
// - los mismos departamentos que el original
// - las mismas selecciones de materias que el original
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = cuerpoJson();
$id = datosOptimoInt($datos, 'id');
if ($id <= 0) {
    sendJSONError('ID inválido', 400);
}

try {
    $db = Db::open();
    $fila = $db->fetchOne("SELECT nombre FROM escenarios_desideratas WHERE id = ?", $id);
    if (!$fila) {
        sendJSONError('No encontrado', 404);
    }
    // Copia del escenario (los campos actual/activo/modo_rueda quedan a 0 por defecto)
    $db->execute("INSERT INTO escenarios_desideratas (nombre) VALUES (?)", $fila['nombre'] . ' bis');
    $idNuevo = $db->insertId();
    // Mismos departamentos y mismas selecciones que el original
    $db->execute("INSERT INTO departamentos_escenarios (idEscenario, idDepartamento)
                  (SELECT ?, idDepartamento FROM departamentos_escenarios WHERE idEscenario = ?)", $idNuevo, $id);
    $db->execute("INSERT INTO seleccion (idProfesor, idMateria, idGrupo, horas, orden, idEscenario)
                  (SELECT idProfesor, idMateria, idGrupo, horas, orden, ? FROM seleccion WHERE idEscenario = ?)", $idNuevo, $id);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => $idNuevo), 'Escenario duplicado');
?>
