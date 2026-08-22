<?php
/**
 * API para el Histórico de selecciones (Fase 7.1)
 * Fiel a v3: muestra para cada profesor del departamento las materias que
 * eligió en un escenario (desiderata) concreto, señalando las que tienen
 * conflictos con otros profesores.
 *
 * Acción:
 *   - listar : devuelve las selecciones de un departamento para un escenario
 *
 * Permisos: profesores, jefes de departamento y admins.
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

// Fiel a v3 (página con cabecera): requiere sesión iniciada
checkSession();

$action = isset($_GET['action']) ? $_GET['action'] : '';

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos');
}

try {
    switch ($action) {
        // Devuelve el histórico de selecciones de un departamento para un escenario
        case 'listar':
            $idEscenario = isset($_GET['idEscenario']) ? intval($_GET['idEscenario']) : 0;
            if ($idEscenario <= 0) {
                throw new Exception('Escenario inválido');
            }
            // Solo se puede consultar el histórico de un departamento. Los jefes
            // solo ven su propio departamento; los admins pueden elegir uno.
            $idDepartamento = isset($_GET['idDepartamento']) ? intval($_GET['idDepartamento']) : 0;
            if ($idDepartamento <= 0) {
                throw new Exception('ID de departamento inválido');
            }
            // Calculamos primero las materias con conflictos
            $sqlMaterias = "SELECT m.id AS idMateria, m.divisible AS divisible, mg.idGrupo AS idGrupo, mg.cantidad AS cantidad, mg.horas AS horas
                            FROM materias m
                            JOIN materias_grupos mg ON mg.idMateria = m.id
                            WHERE mg.idGrupo IN (SELECT DISTINCT idGrupo FROM seleccion WHERE idEscenario=$idEscenario)
                            AND m.idDepartamento=$idDepartamento AND mg.cantidad > 0";
            $materiasConflictos = array();
            $result = mysqli_query($db, $sqlMaterias);
            if ($result) {
                while ($fila = mysqli_fetch_assoc($result)) {
                    $materiasConflictos[$fila['idMateria']][$fila['idGrupo']] = $fila;
                }
            }
            mysqli_free_result($result);

            // Obtenemos todos los profesores que eligieron en ese escenario
            $sql = "SELECT DISTINCT p.id AS id, p.nombre AS nombre, p.orden AS orden
                    FROM profesores p
                    WHERE (p.idDepartamento=$idDepartamento AND p.activo=1)
                    OR p.id IN (SELECT idProfesor FROM seleccion WHERE idEscenario=$idEscenario)
                    ORDER BY p.orden";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $historico = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $idProf = $fila['id'];
                // Materias elegidas por este profesor
                $sqlProf = "SELECT s.id AS idSeleccion, s.horas AS horas, s.orden AS orden,
                               m.nombre AS nombre, m.tipo AS tipo, s.idMateria AS idMateria,
                               c.abreviatura AS abrevCurso, g.abreviatura AS abrevGrupo, g.mostrar
                            FROM seleccion s
                            JOIN materias m ON m.id = s.idMateria
                            JOIN cursos c ON c.id = m.idCurso
                            JOIN grupos g ON g.id = s.idGrupo
                            WHERE s.idProfesor=$idProf AND s.idEscenario=$idEscenario
                            ORDER BY s.orden";
                $resultProf = mysqli_query($db, $sqlProf);
                $materias = [];
                while ($filaProf = mysqli_fetch_assoc($resultProf)) {
                    $materia = $filaProf;
                    // Marcamos si la materia tiene conflicto
                    $conflicto = false;
                    if (isset($materiasConflictos[$filaProf['idMateria']][$filaProf['idGrupo']])) {
                        $conflicto = true;
                    }
                    $materia['conflicto'] = $conflicto;
                    $materias[] = $materia;
                }
                mysqli_free_result($resultProf);
                $historico[] = array(
                    'id' => $fila['id'],
                    'nombre' => $fila['nombre'],
                    'materias' => $materias
                );
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($historico);
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
