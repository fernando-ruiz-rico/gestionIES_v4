<?php
/**
 * API para la gestión de Selección de materias (Fase 5.1)
 * Fiel a v3: la selección de materias se guarda en la tabla seleccion, una fila
 * por cada elección que hace un profesor sobre una materia para un grupo y un
 * escenario (desiderata) concretos.
 *
 * Acciones:
 *   - listar_cursos       : lista cursos + grupos + materias de un departamento
 *   - listar_profesores   : lista los profesores de un departamento
 *   - listar_seleccion    : lista la selección de un profesor para un escenario
 *   - insertar_seleccion  : añade una selección
 *   - borrar_seleccion    : elimina una selección
 *   - borrar_toda_seleccion : elimina todas las selecciones de un profesor
 *   - ordenar_seleccion   : reordena las selecciones de un profesor
 *
 * Permisos: los admins y jefes de departamento pueden elegir libremente; los
 * profesores solo sobre sus propias elecciones (y no las asignadas por la directiva).
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
@session_start();
 $datos = json_decode(file_get_contents("php://input"), true) ?: [];

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Permisos superiores (admin o jefe de departamento)
function esSuper()
{
    return isset($_SESSION['rol']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'jefeDepartamento');
}

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos');
}

try {
    switch ($action) {
        // Lista cursos + grupos + materias de un departamento para un escenario
        case 'listar_cursos':
            $idDepartamento = intval($_REQUEST['idDepartamento']);
            $idEscenario = isset($_REQUEST['idEscenario']) ? intval($_REQUEST['idEscenario']) : 0;
            if ($idDepartamento <= 0) {
                throw new Exception('Departamento inválido');
            }
            $super = esSuper();
            // Cursos y grupos
            if ($super) {
                $sql = "SELECT DISTINCT c.id AS idCurso, c.nombre AS nombreCurso, c.abreviatura AS abrevCurso, c.orden AS ordenCurso,
                              g.id AS idGrupo, g.nombre AS nombreGrupo, g.abreviatura AS abrevGrupo, g.orden AS ordenGrupo, g.mostrar
                        FROM cursos c
                        JOIN grupos g ON g.idCurso = c.id
                        WHERE c.id IN (SELECT idCurso FROM materias WHERE idDepartamento=$idDepartamento)
                        ORDER BY c.orden, g.orden";
            } else {
                $sql = "SELECT DISTINCT c.id AS idCurso, c.nombre AS nombreCurso, c.abreviatura AS abrevCurso, c.orden AS ordenCurso,
                              g.id AS idGrupo, g.nombre AS nombreGrupo, g.abreviatura AS abrevGrupo, g.orden AS ordenGrupo, g.mostrar
                        FROM cursos c
                        JOIN grupos g ON g.idCurso = c.id
                        WHERE c.id IN (SELECT idCurso FROM materias WHERE idDepartamento=$idDepartamento AND asignada_directiva=0)
                        ORDER BY c.orden, g.orden";
            }
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $cursos = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $cursos[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($cursos);
            break;

        // Lista las materias del departamento (para añadirlas a la selección)
        case 'listar_materias':
            $idDepartamento = intval($_REQUEST['idDepartamento']);
            if ($idDepartamento <= 0) {
                throw new Exception('Departamento inválido');
            }
            $sql = "SELECT m.id AS idMateria, m.nombre AS nombre, m.idCurso AS idCurso,
                           m.horas AS horas, m.tipo AS tipo, m.divisible AS divisible,
                           c.abreviatura AS abrevCurso
                    FROM materias m
                    JOIN cursos c ON c.id = m.idCurso
                    WHERE m.idDepartamento=$idDepartamento
                    ORDER BY c.orden, m.nombre";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $materias = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $materias[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($materias);
            break;

        // Lista los profesores activos de un departamento
        case 'listar_profesores':
            $idDepartamento = intval($_REQUEST['idDepartamento']);
            $idEspecialidad = isset($_REQUEST['idEspecialidad']) ? $_REQUEST['idEspecialidad'] : '';
            if ($idDepartamento <= 0) {
                throw new Exception('Departamento inválido');
            }
            $sql = "SELECT id, nombre, abreviatura, idEspecialidad, orden
                   FROM profesores WHERE idDepartamento=$idDepartamento AND activo=1";
            if (!empty($idEspecialidad)) {
                $sql .= " AND idEspecialidad='" . mysqli_real_escape_string($db, $idEspecialidad) . "'";
            }
            $sql .= " ORDER BY orden";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $profesores = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $profesores[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($profesores);
            break;

        // Lista la selección de un profesor para un escenario
        case 'listar_seleccion':
            $idProfesor = intval($_REQUEST['idProfesor']);
            $idEscenario = intval($_REQUEST['idEscenario']);
            if ($idProfesor <= 0 || $idEscenario <= 0) {
                throw new Exception('Datos inválidos');
            }
            $sql = "SELECT s.id AS id, s.horas AS horas, s.orden AS orden,
                           m.nombre AS nombre, m.divisible AS divisible,
                           c.abreviatura AS abrevCurso, g.abreviatura AS abrevGrupo, g.mostrar,
                           m.asignada_directiva AS asignada_directiva
                    FROM seleccion s
                    JOIN materias m ON m.id = s.idMateria
                    JOIN cursos c ON c.id = m.idCurso
                    JOIN grupos g ON g.id = s.idGrupo
                    WHERE s.idProfesor=$idProfesor AND s.idEscenario=$idEscenario
                    ORDER BY s.orden";
            $result = mysqli_query($db, $sql);
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $selecciones = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $selecciones[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($selecciones);
            break;

        // Añade una nueva selección
        case 'insertar_seleccion':
            $idProfesor = intval($datos['idProfesor']);
            $idMateria = intval($datos['idMateria']);
            $idGrupo = intval($datos['idGrupo']);
            $idEscenario = intval($datos['idEscenario']);
            $horas = intval($datos['horas']);
            if ($idProfesor <= 0 || $idMateria <= 0 || $idGrupo <= 0 || $idEscenario <= 0 || $horas <= 0) {
                throw new Exception('Datos incompletos para la selección');
            }
            // Si la materia la asigna la directiva, no hay peligro de conflicto: orden bajo
            $result = mysqli_query($db, "SELECT asignada_directiva FROM materias WHERE id=$idMateria");
            $fila = mysqli_fetch_assoc($result);
            $asignadaDirectiva = $fila ? $fila['asignada_directiva'] : 0;
            mysqli_free_result($result);
            $result = mysqli_query($db, "SELECT COUNT(*) AS total FROM seleccion WHERE idProfesor=$idProfesor AND idEscenario=$idEscenario");
            $fila = mysqli_fetch_assoc($result);
            $orden = $asignadaDirectiva ? 100 : $fila['total'] + 1;
            mysqli_free_result($result);
            $query = "INSERT INTO seleccion (idProfesor, idMateria, idGrupo, idEscenario, horas, orden) VALUES ($idProfesor, $idMateria, $idGrupo, $idEscenario, $horas, $orden)";
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            $nuevoId = mysqli_insert_id($db);
            closeDBConnection($db);
            sendJSONSuccess(array('id' => $nuevoId), 'Selección añadida');
            break;

        // Elimina una selección
        case 'borrar_seleccion':
            $id = intval($datos['id']);
            if ($id <= 0) {
                throw new Exception('ID de selección inválido');
            }
            if (!mysqli_query($db, "DELETE FROM seleccion WHERE id=$id")) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Selección eliminada');
            break;

        // Elimina todas las selecciones de un profesor para un escenario
        case 'borrar_toda_seleccion':
            $idProfesor = intval($datos['idProfesor']);
            $idEscenario = intval($datos['idEscenario']);
            if ($idProfesor <= 0 || $idEscenario <= 0) {
                throw new Exception('Datos inválidos');
            }
            if (!mysqli_query($db, "DELETE FROM seleccion WHERE idProfesor=$idProfesor AND idEscenario=$idEscenario")) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Todas las selecciones eliminadas');
            break;

        // Reordena las selecciones de un profesor para un escenario
        case 'ordenar_seleccion':
            $idEscenario = intval($datos['idEscenario']);
            $orden = isset($datos['orden']) ? $datos['orden'] : '';
            $ids = explode(",", $orden);
            foreach ($ids as $pos => $cod) {
                $idSel = intval(substr($cod, 3));
                if ($idSel > 0) {
                    mysqli_query($db, "UPDATE seleccion SET orden=" . ($pos + 1) . " WHERE id=$idSel AND idEscenario=$idEscenario");
                }
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Orden actualizado');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
