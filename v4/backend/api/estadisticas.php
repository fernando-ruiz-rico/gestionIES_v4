<?php
/**
 * API para Estadísticas de la selección (Fase 7.2)
 * Fiel a v3: calcula para un escenario (desiderata) los datos estadísticos de
 * la selección: horas totales impartidas por especialidad y por profesor, y
 * posibles conflictos de materias.
 *
 * Acción:
 *   - listar : devuelve las estadísticas de un departamento para un escenario
 *
 * Permisos: profesores, jefes de departamento y admins.
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

// Fiel a v3 (página con cabecera): requiere sesión iniciada
checkSession();

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Horas lectivas de referencia para un profesor (según v3)
define('HORAS_PROFESOR', 18);

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos');
}

try {
    switch ($action) {
        // Devuelve las estadísticas de un departamento para un escenario
        case 'listar':
            $idEscenario = isset($_GET['idEscenario']) ? intval($_GET['idEscenario']) : 0;
            $idDepartamento = isset($_GET['idDepartamento']) ? intval($_GET['idDepartamento']) : 0;
            if ($idEscenario <= 0 || $idDepartamento <= 0) {
                throw new Exception('Datos inválidos');
            }
            // Horas totales impartidas por especialidad
            $sqlEsp = "SELECT e.id AS idEspecialidad, e.descripcion AS descripcion, e.horasTutoria AS tutoria, e.horasIngles AS ingles, e.profesores AS profesores
                       FROM especialidades e
                       WHERE e.idDepartamento=$idDepartamento";
            $result = mysqli_query($db, $sqlEsp);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $especialidades = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $especialidades[$fila['idEspecialidad']] = $fila;
            }
            mysqli_free_result($result);

            foreach ($especialidades as $idEsp => $valores) {
                $resultAux = mysqli_query($db, "SELECT COALESCE(SUM(seleccion.horas), 0) AS suma FROM profesores, seleccion WHERE profesores.id = seleccion.idProfesor AND profesores.idEspecialidad='$idEsp' AND seleccion.idEscenario=$idEscenario AND profesores.idDepartamento=$idDepartamento");
                $fila = mysqli_fetch_assoc($resultAux);
                $especialidades[$idEsp]['horas_totales'] = $fila['suma'];
                $especialidades[$idEsp]['horas_ref'] = $valores['profesores'] * HORAS_PROFESOR;
            }

            // Horas totales por profesor
            $sqlProf = "SELECT p.id AS id, p.nombre AS nombre, COALESCE(SUM(s.horas), 0) AS horas
                        FROM profesores p
                        LEFT JOIN seleccion s ON s.idProfesor = p.id AND s.idEscenario = $idEscenario
                        WHERE p.idDepartamento=$idDepartamento AND p.activo=1
                        GROUP BY p.id ORDER BY p.orden";
            $result = mysqli_query($db, $sqlProf);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $profesores = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $profesores[] = $fila;
            }
            mysqli_free_result($result);

            closeDBConnection($db);
            sendJSONSuccess(array(
                'especialidades' => $especialidades,
                'profesores' => $profesores,
                'horas_ref' => HORAS_PROFESOR
            ));
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
