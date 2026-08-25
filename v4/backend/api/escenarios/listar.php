<?php
// API para listar los escenarios de desideratas de un departamento
// (tabla real: escenarios_desideratas, asociados por departamentos_escenarios)
require_once '../../config.php';
cabeceraJson();

// Fiel a v3 (cargar_escenarios.php): requiere sesión iniciada
checkSession();

$idDepartamento = getOptimoInt('idDepartamento');
if ($idDepartamento <= 0) {
    sendJSONError('Departamento inválido', 400);
}

// Fiel a v3: solo los escenarios vinculados al departamento, por nombre
try {
    $db = Db::open();
    $filas = $db->fetchAll("SELECT e.id, e.nombre, e.actual, e.activo_desideratas, e.modo_rueda
                            FROM escenarios_desideratas e
                            JOIN departamentos_escenarios de ON de.idEscenario = e.id
                            WHERE de.idDepartamento = ?
                            ORDER BY e.nombre", $idDepartamento);
    $escenarios = array();
    foreach ($filas as $fila) {
        // Departamentos asociados a cada escenario (se usan en el modal de edición)
        $fila['departamentos'] = $db->fetchAll("SELECT d.id, d.nombre
                                                 FROM departamentos_escenarios de
                                                 JOIN departamentos d ON d.id = de.idDepartamento
                                                 WHERE de.idEscenario = ?
                                                 ORDER BY d.nombre", $fila['id']);
        $escenarios[] = $fila;
    }
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess($escenarios);
?>
