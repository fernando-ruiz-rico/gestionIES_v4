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

require_once '../config.php';
cabeceraJson();
@session_start();
 $datos = cuerpoJson();

$method = $_SERVER['REQUEST_METHOD'];
$action = getOptimo('action');

// Permisos superiores (admin o jefe de departamento)
function esSuper()
{
    return isset($_SESSION['rol']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'jefeDepartamento');
}

try {
    $db = Db::open();

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
                       WHERE c.id IN (SELECT idCurso FROM materias WHERE idDepartamento=?)
                       ORDER BY c.orden, g.orden";
            } else {
                $sql = "SELECT DISTINCT c.id AS idCurso, c.nombre AS nombreCurso, c.abreviatura AS abrevCurso, c.orden AS ordenCurso,
                              g.id AS idGrupo, g.nombre AS nombreGrupo, g.abreviatura AS abrevGrupo, g.orden AS ordenGrupo, g.mostrar
                       FROM cursos c
                       JOIN grupos g ON g.idCurso = c.id
                       WHERE c.id IN (SELECT idCurso FROM materias WHERE idDepartamento=? AND asignada_directiva=0)
                       ORDER BY c.orden, g.orden";
            }
            $cursos = $db->fetchAll($sql, $idDepartamento);
            $db->close();
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
                    WHERE m.idDepartamento=?
                    ORDER BY c.orden, m.nombre";
            $materias = $db->fetchAll($sql, $idDepartamento);
            $db->close();
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
                   FROM profesores WHERE idDepartamento=? AND activo=1";
            $params = array();
            $params[] = $idDepartamento;
            if (!empty($idEspecialidad)) {
                $sql .= " AND idEspecialidad=?";
                $params[] = $idEspecialidad;
            }
            $sql .= " ORDER BY orden";
            $profesores = call_user_func_array(array($db, 'fetchAll'), array_merge(array($sql), $params));
            $db->close();
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
                    WHERE s.idProfesor=? AND s.idEscenario=?
                    ORDER BY s.orden";
            $selecciones = $db->fetchAll($sql, $idProfesor, $idEscenario);
            $db->close();
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
            $fila = $db->fetchOne("SELECT asignada_directiva FROM materias WHERE id=?", $idMateria);
            $asignadaDirectiva = $fila ? $fila['asignada_directiva'] : 0;
            $fila = $db->fetchOne("SELECT COUNT(*) AS total FROM seleccion WHERE idProfesor=? AND idEscenario=?", $idProfesor, $idEscenario);
            $orden = $asignadaDirectiva ? 100 : $fila['total'] + 1;
            $db->execute("INSERT INTO seleccion (idProfesor, idMateria, idGrupo, idEscenario, horas, orden) VALUES (?, ?, ?, ?, ?, ?)", $idProfesor, $idMateria, $idGrupo, $idEscenario, $horas, $orden);
            $nuevoId = $db->insertId();
            $db->close();
            sendJSONSuccess(array('id' => $nuevoId), 'Selección añadida');
            break;

        // Elimina una selección
        case 'borrar_seleccion':
            $id = intval($datos['id']);
            if ($id <= 0) {
                throw new Exception('ID de selección inválido');
            }
            $db->execute("DELETE FROM seleccion WHERE id=?", $id);
            $db->close();
            sendJSONSuccess(null, 'Selección eliminada');
            break;

        // Elimina todas las selecciones de un profesor para un escenario
        case 'borrar_toda_seleccion':
            $idProfesor = intval($datos['idProfesor']);
            $idEscenario = intval($datos['idEscenario']);
            if ($idProfesor <= 0 || $idEscenario <= 0) {
                throw new Exception('Datos inválidos');
            }
            $db->execute("DELETE FROM seleccion WHERE idProfesor=? AND idEscenario=?", $idProfesor, $idEscenario);
            $db->close();
            sendJSONSuccess(null, 'Todas las selecciones eliminadas');
            break;

        // Reordena las selecciones de un profesor para un escenario
        case 'ordenar_seleccion':
            $idEscenario = intval($datos['idEscenario']);
            $orden = datosOptimo($datos, 'orden');
            $ids = explode(",", $orden);
            foreach ($ids as $pos => $cod) {
                $idSel = intval(substr($cod, 3));
                if ($idSel > 0) {
                    $db->execute("UPDATE seleccion SET orden=? WHERE id=? AND idEscenario=?", $pos + 1, $idSel, $idEscenario);
                }
            }
            $db->close();
            sendJSONSuccess(null, 'Orden actualizado');
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
