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

// Fiel a v3 (página con cabecera): requiere sesión iniciada
checkSession();

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    $db = Db::open();
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
                          WHERE c.id IN (SELECT idCurso FROM materias WHERE idDepartamento=?)
                          ORDER BY c.orden, g.orden";
            $cursos = $db->fetchAll($sqlCursos, $idDepartamento);

            // Obtenemos las materias del departamento
            $sqlMaterias = "SELECT m.id AS idMateria, m.nombre AS nombre, m.idCurso AS idCurso, m.horas AS horas, m.idEspecialidad AS idEspecialidad, m.asignada_directiva AS asignada_directiva
                              FROM materias m
                              WHERE m.idDepartamento=? ORDER BY m.id";
            $materias = $db->fetchAll($sqlMaterias, $idDepartamento);

            // Obtenemos las selecciones del escenario
            $sqlSel = "SELECT s.id AS id, s.idProfesor AS idProfesor, s.idMateria AS idMateria, s.idGrupo AS idGrupo, s.horas AS horas, s.orden AS orden, p.nombre AS nombreProfesor
                        FROM seleccion s
                        JOIN profesores p ON p.id = s.idProfesor
                        WHERE s.idEscenario=?
                        ORDER BY s.orden, p.orden";
            $selecciones = $db->fetchAll($sqlSel, $idEscenario);

            sendJSONSuccess(array(
                'cursos' => $cursos,
                'materias' => $materias,
                'selecciones' => $selecciones
            ));
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}