<?php
/**
 * API para la gestión de Resultados de Aprendizaje (Fase 4.1)
 * Fiel a v3: los RA se asocian a cada materia, con un porcentaje de atención
 * en empresa y un porcentaje de evaluación, y pueden llevar asociados
 * criterios de evaluación (CE).
 *
 * Acciones:
 *   - listar_materias : lista materias con datos para el selector
 *   - cargar            : carga los RA + CE de una materia (solo lectura)
 *   - guardar           : inserta/actualiza un RA
 *   - actualizar_horas  : actualiza las horas de docencia en empresa de la materia
 *   - actualizar_evaluacion : actualiza el % de evaluación y si es un RA clave
 *   - eliminar          : elimina un RA (y sus CE)
 *   - obtener           : devuelve un RA concreto en JSON
 *   - guardar_criterio  : asocia un nuevo criterio de evaluación a un RA
 *   - actualizar_criterio : actualiza un criterio de evaluación
 *   - eliminar_criterio : elimina un criterio de evaluación
 *   - cargar_criterios  : carga los CE asociados a un RA
 *
 * Permisos: solo admin y jefes de departamento (sobre su propio departamento)
 * pueden crear/modificar/eliminar resultados.
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
@session_start();
 $datos = json_decode(file_get_contents("php://input"), true) ?: [];

$method = $_SERVER['REQUEST_METHOD'];
$action = getOptimo('action');

// Permisos de edición: admin o jefe de departamento
function tienePermisoEdicion()
{
    return isset($_SESSION['rol']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'jefeDepartamento');
}

// Un usuario solo puede trabajar sobre los RA de su propio departamento cuando
// es jefe de departamento; el admin puede sobre cualquier departamento.
function puedeTrabajarSobreDepartamento($idDepartamento)
{
    if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') {
        return true;
    }
    // Jefe de solo puede editar si el departamento coincide con el suyo
    if (isset($_SESSION['departamentoUsuario'])) {
        return intval($_SESSION['departamentoUsuario']) == intval($idDepartamento);
    }
    return false;
}

// Devuelve el departamento del que depende la materia indicada (0 si no existe)
function idDepartamentoDeMateria($db, $idMateria)
{
    $fila = $db->fetchOne("SELECT idDepartamento FROM materias WHERE id = ?", $idMateria);
    return ($fila && $fila['idDepartamento'] !== null) ? intval($fila['idDepartamento']) : 0;
}

// Devuelve el departamento de la materia a la que pertenece un resultado
function idDepartamentoDeRA($db, $idResultado)
{
    $fila = $db->fetchOne("SELECT idMateria FROM resultados_aprendizaje WHERE id = ?", $idResultado);
    return ($fila) ? idDepartamentoDeMateria($db, intval($fila['idMateria'])) : 0;
}

// Comprueba que el usuario puede editar datos de una materia/departamento:
// los jefes de departamento solo sobre la materia de su propio departamento
// (el admin puede sobre cualquiera), como en la vista de v3.
function comprobarDepartamento($idDepartamento)
{
    if (!$idDepartamento || !puedeTrabajarSobreDepartamento($idDepartamento)) {
        throw new Exception('No tiene permisos para realizar esta acción');
    }
}

try {
    $db = Db::open();

    switch ($action) {
        // Lista las materias del selector, fiel a v3 (includes/cargar_materias_programaciones.php):
        //   - admin : todas las materias del departamento elegido en la vista
        //   - jefe  : todas las materias de su departamento
        //   - profe : solo las materias que imparte en los escenarios actuales
        // En los tres casos, únicamente las materias con programación activa.
        case 'listar_materias':
            $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
            if ($rol == 'admin' || $rol == 'jefeDepartamento') {
                if ($rol == 'admin') {
                    $idDepartamento = isset($_REQUEST['idDepartamento']) ? intval($_REQUEST['idDepartamento']) : 0;
                    if ($idDepartamento <= 0) {
                        throw new Exception('Departamento inválido');
                    }
                } else {
                    $idDepartamento = intval($_SESSION['departamentoUsuario']);
                }
                $materias = $db->fetchAll(
                    "SELECT m.id AS idMateria, m.nombre AS nombre,
                            c.nombre AS curso, c.abreviatura AS abrevCurso,
                            m.horas AS horas, m.horas_empresa AS horas_empresa,
                            m.asignada_directiva AS asignada_directiva, m.tipo AS tipo
                     FROM materias m
                     LEFT JOIN cursos c ON c.id = m.idCurso
                     WHERE m.tiene_programacion = TRUE AND m.idDepartamento = ?
                     ORDER BY c.orden, c.nombre, m.nombre",
                    $idDepartamento);
            } else {
                // Profesor: solo las materias que imparte en los escenarios actuales
                $idProfesor = intval($_SESSION['idUsuario']);
                $materias = $db->fetchAll(
                    "SELECT DISTINCT m.id AS idMateria, m.nombre AS nombre,
                            c.nombre AS curso, c.abreviatura AS abrevCurso,
                            m.horas AS horas, m.horas_empresa AS horas_empresa,
                            m.asignada_directiva AS asignada_directiva, m.tipo AS tipo
                     FROM materias m
                     LEFT JOIN cursos c ON c.id = m.idCurso
                     LEFT JOIN seleccion s ON s.idMateria = m.id
                     LEFT JOIN escenarios_desideratas e ON e.id = s.idEscenario
                     WHERE m.tiene_programacion = TRUE
                       AND e.actual = TRUE
                       AND s.idProfesor = ?
                     ORDER BY m.nombre",
                    $idProfesor);
            }
            $db->close();
            sendJSONSuccess($materias);
            break;

        // Carga los RA (y sus CE) de una materia. Solo lectura.
        case 'cargar':
            $idMateria = getOptimoInt('idMateria');
            if ($idMateria <= 0) {
                throw new Exception('ID de materia inválido');
            }
            // Horas de docencia en empresa para la materia
            $fila = $db->fetchOne("SELECT horas_empresa FROM materias WHERE id = ?", $idMateria);
            $horasEmpresa = $fila ? $fila['horas_empresa'] : 0;

            $resultados = $db->fetchAll("SELECT * FROM resultados_aprendizaje WHERE idMateria = ? ORDER BY orden", $idMateria);

            $db->close();
            sendJSONSuccess(array(
                'idMateria' => $idMateria,
                'horas_empresa' => $horasEmpresa,
                'permisos' => tienePermisoEdicion(),
                'resultados' => $resultados
            ));
            break;

        // Inserta o actualiza un resultado de aprendizaje
        case 'guardar':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $id = isset($datos['id']) && !empty($datos['id']) ? intval($datos['id']) : 0;
            $idMateria = intval($datos['idMateria']);
            $texto = $datos['texto'];
            $orden = intval($datos['orden']);
            $porcentajeEmpresa = intval(isset($datos['porcentaje_empresa']) ? $datos['porcentaje_empresa'] : 0);
            if ($idMateria <= 0 || empty($texto)) {
                throw new Exception('Datos incompletos para guardar el resultado');
            }
            $idDepartamento = ($id > 0) ? idDepartamentoDeRA($db, $id) : idDepartamentoDeMateria($db, $idMateria);
            comprobarDepartamento($idDepartamento);
            if ($id > 0) {
                $db->execute(
                    "UPDATE resultados_aprendizaje SET texto = ?, orden = ?, porcentaje_empresa = ? WHERE id = ?",
                    $texto, $orden, $porcentajeEmpresa, $id);
                $nuevoId = $id;
            } else {
                $db->execute(
                    "INSERT INTO resultados_aprendizaje (idMateria, texto, orden, porcentaje_empresa) VALUES (?, ?, ?, ?)",
                    $idMateria, $texto, $orden, $porcentajeEmpresa);
                $nuevoId = $db->insertId();
            }
            $db->close();
            sendJSONSuccess(array('id' => $nuevoId), 'Resultado de aprendizaje guardado');
            break;

        // Actualiza las horas de docencia en empresa de la materia
        case 'actualizar_horas':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $idMateria = intval($datos['idMateria']);
            $horas = intval($datos['horas']);
            if ($idMateria <= 0) {
                throw new Exception('ID de materia inválido');
            }
            comprobarDepartamento(idDepartamentoDeMateria($db, $idMateria));
            $db->execute("UPDATE materias SET horas_empresa = ? WHERE id = ?", $horas, $idMateria);
            $db->close();
            sendJSONSuccess(null, 'Horas de empresa actualizadas');
            break;

        // Actualiza el % de evaluación y si un RA es clave
        case 'actualizar_evaluacion':
            $idResultado = intval($datos['idResultado']);
            $porcentajeEvaluacion = intval(isset($datos['porcentaje_evaluacion']) ? $datos['porcentaje_evaluacion'] : 0);
            $esClave = isset($datos['es_clave']) ? 1 : 0;
            if ($idResultado <= 0) {
                throw new Exception('ID de resultado inválido');
            }
            comprobarDepartamento(idDepartamentoDeRA($db, $idResultado));
            $db->execute(
                "UPDATE resultados_aprendizaje SET porcentaje_evaluacion = ?, es_clave = ? WHERE id = ?",
                $porcentajeEvaluacion, $esClave, $idResultado);
            $db->close();
            sendJSONSuccess(null, 'Evaluación actualizada');
            break;

        // Elimina un resultado de aprendizaje y sus criterios de evaluación
        case 'eliminar':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $id = intval($datos['id']);
            if ($id <= 0) {
                throw new Exception('ID de resultado inválido');
            }
            comprobarDepartamento(idDepartamentoDeRA($db, $id));
            $db->execute("DELETE FROM criterios_evaluacion WHERE idRA = ?", $id);
            $db->execute("DELETE FROM resultados_aprendizaje WHERE id = ?", $id);
            $db->close();
            sendJSONSuccess(null, 'Resultado de aprendizaje eliminado');
            break;

        // Devuelve un resultado de aprendizaje concreto
        case 'obtener':
            $id = getOptimoInt('id');
            if ($id <= 0) {
                throw new Exception('ID de resultado inválido');
            }
            $fila = $db->fetchOne("SELECT * FROM resultados_aprendizaje WHERE id = ?", $id);
            $db->close();
            if (!$fila) {
                sendJSONError('Resultado no encontrado', 404);
            }
            sendJSONSuccess($fila);
            break;

        // Asocia un nuevo criterio de evaluación a un resultado
        case 'guardar_criterio':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $idResultado = intval($datos['idResultado']);
            $codigo = $datos['codigo'];
            $texto = $datos['texto'];
            if ($idResultado <= 0 || empty($codigo)) {
                throw new Exception('Datos incompletos para guardar el criterio');
            }
            comprobarDepartamento(idDepartamentoDeRA($db, $idResultado));
            $texto = empty($texto) ? '' : $texto;
            $db->execute("INSERT INTO criterios_evaluacion (idRA, codigo, texto) VALUES (?, ?, ?)", $idResultado, $codigo, $texto);
            $nuevoId = $db->insertId();
            $db->close();
            sendJSONSuccess(array('id' => $nuevoId), 'Criterio de evaluación guardado');
            break;

        // Actualiza un criterio de evaluación
        case 'actualizar_criterio':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $idResultado = intval($datos['idResultado']);
            $codigoAntiguo = $datos['codigo'];
            $nuevoCodigo = $datos['nuevoCodigo'];
            $nuevoTexto = $datos['nuevoTexto'] === null ? '' : $datos['nuevoTexto'];
            if ($idResultado <= 0 || empty($codigoAntiguo)) {
                throw new Exception('Datos incompletos para actualizar el criterio');
            }
            comprobarDepartamento(idDepartamentoDeRA($db, $idResultado));
            $db->execute(
                "UPDATE criterios_evaluacion SET codigo = ?, texto = ? WHERE idRA = ? AND codigo = ?",
                $nuevoCodigo, $nuevoTexto, $idResultado, $codigoAntiguo);
            $db->close();
            sendJSONSuccess(null, 'Criterio de evaluación actualizado');
            break;

        // Elimina un criterio de evaluación
        case 'eliminar_criterio':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $idResultado = intval($datos['idResultado']);
            $codigo = $datos['codigo'];
            if ($idResultado <= 0 || empty($codigo)) {
                throw new Exception('Datos incompletos para eliminar el criterio');
            }
            comprobarDepartamento(idDepartamentoDeRA($db, $idResultado));
            $db->execute("DELETE FROM criterios_evaluacion WHERE idRA = ? AND codigo = ?", $idResultado, $codigo);
            $db->close();
            sendJSONSuccess(null, 'Criterio de evaluación eliminado');
            break;

        // Carga los criterios de evaluación asociados a un resultado
        case 'cargar_criterios':
            $idResultado = getOptimoInt('idResultado');
            if ($idResultado <= 0) {
                throw new Exception('ID de resultado inválido');
            }
            $criterios = $db->fetchAll("SELECT * FROM criterios_evaluacion WHERE idRA = ? ORDER BY codigo", $idResultado);
            $db->close();
            sendJSONSuccess($criterios);
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
?>
