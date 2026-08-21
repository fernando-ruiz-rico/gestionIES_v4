<?php
/**
 * API para la Exportación a Excel (Fase 7.4)
 * Fiel a v3: devuelve los datos de la selección de un escenario (desiderata)
 * para ser volcados en una hoja de cálculo. La aplicación puede usar estos
 * datos para generar el fichero Excel/CSV.
 *
 * Acción:
 *   - listar : devuelve los datos de la selección para un escenario
 *
 * Permisos: profesores, jefes de departamento y admins.
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos');
}

try {
    switch ($action) {
        // Devuelve los datos de la selección para un escenario (para exportar)
        case 'listar':
            $idEscenario = isset($_GET['idEscenario']) ? intval($_GET['idEscenario']) : 0;
            $idDepartamento = isset($_GET['idDepartamento']) ? intval($_GET['idDepartamento']) : 0;
            if ($idEscenario <= 0 || $idDepartamento <= 0) {
                throw new Exception('Datos inválidos');
            }
            // Obtenemos los cursos y grupos del departamento
            $sqlCursos = "SELECT DISTINCT c.id AS idCurso, c.nombre AS nombreCurso, c.abreviatura AS abrevCurso, c.orden AS ordenCurso,
                              g.id AS idGrupo, g.nombre AS nombreGrupo, g.abreviatura AS abrevGrupo, g.orden AS ordenGrupo, g.mostrar
                          FROM cursos c
                          JOIN grupos g ON g.idCurso = c.id
                          WHERE c.id IN (SELECT idCurso FROM materias WHERE idDepartamento=$idDepartamento)
                          ORDER BY c.orden, g.orden";
            $result = mysqli_query($db, $sqlCursos);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $cursos = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $cursos[] = $fila;
            }
            mysqli_free_result($result);

            // Obtenemos las materias del departamento
            $sqlMaterias = "SELECT m.id AS idMateria, m.nombre AS nombre, m.idCurso AS idCurso, m.horas AS horas, m.idEspecialidad AS idEspecialidad, m.asignada_directiva AS asignada_directiva
                             FROM materias m
                             WHERE m.idDepartamento=$idDepartamento ORDER BY m.id";
            $result = mysqli_query($db, $sqlMaterias);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $materias = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $materias[] = $fila;
            }
            mysqli_free_result($result);

            // Obtenemos las selecciones del escenario
            $sqlSel = "SELECT s.id AS id, s.idProfesor AS idProfesor, s.idMateria AS idMateria, s.idGrupo AS idGrupo, s.horas AS horas, s.orden AS orden, p.nombre AS nombreProfesor
                       FROM seleccion s
                       JOIN profesores p ON p.id = s.idProfesor
                       WHERE s.idEscenario=$idEscenario
                       ORDER BY s.orden, p.orden";
            $result = mysqli_query($db, $sqlSel);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $selecciones = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $selecciones[] = $fila;
            }
            mysqli_free_result($result);

            closeDBConnection($db);
            sendJSONSuccess(array(
                'cursos' => $cursos,
                'materias' => $materias,
                'selecciones' => $selecciones
            ));
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
