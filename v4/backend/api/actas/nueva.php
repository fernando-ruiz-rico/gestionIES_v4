<?php
// API para la gestión de Actas de departamentos (Fase 6.1):
// devuelve el texto inicial de una acta nueva, fiel a v3
// (ajax/actas/nueva_acta_departamento.php):
//   - apartado «Asistentes» relleno con el listado completo de profesores
//     del departamento, ordenado alfabéticamente por nombre
//   - inicio del apartado «Orden del día»
//
// Departamento:
//   - el admin elige el que trabaja (parámetro de la petición);
//   - el jefe de departamento siempre usa el suyo (de la sesión).
require_once '../../config.php';
cabeceraJson();

$session = checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

try {
    $db = Db::open();

    $idDepartamento = 0;
    if ($session['rol'] == ROLE_ADMIN) {
        $idDepartamento = getOptimoInt('idDepartamento');
    } elseif (!empty($session['departamentoUsuario'])) {
        $idDepartamento = intval($session['departamentoUsuario']);
    }
    if ($idDepartamento <= 0) {
        throw new Exception('El usuario no tiene departamento asignado');
    }

    // Todos los profesores del departamento, ordenados alfabéticamente
    // por nombre (mismo criterio que v3)
    $profesores = $db->fetchAll(
        "SELECT nombre FROM profesores WHERE idDepartamento = ? ORDER BY nombre",
        $idDepartamento
    );

    // Texto inicial: «Asistentes» con todos los profesores y la
    // apertura del «Orden del día» (mismo HTML que v3)
    $texto = '<h3>Asistentes</h3><ol>';
    foreach ($profesores as $profesor) {
        $texto .= '<li>' . htmlspecialchars($profesor['nombre'], ENT_QUOTES) . '</li>';
    }
    $texto .= '</ol><h3>Orden del día</h3><p>Por completar</p>';

    sendJSONSuccess(array('texto' => $texto), 'Texto inicial de la acta');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
