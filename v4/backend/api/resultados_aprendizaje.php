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
$action = isset($_GET['action']) ? $_GET['action'] : '';

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
    $stmt = mysqli_prepare($db, "SELECT idDepartamento FROM materias WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $idMateria);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return ($fila && $fila['idDepartamento'] !== null) ? intval($fila['idDepartamento']) : 0;
}

// Devuelve el departamento de la materia a la que pertenece un resultado
function idDepartamentoDeRA($db, $idResultado)
{
    $stmt = mysqli_prepare($db, "SELECT idMateria FROM resultados_aprendizaje WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $idResultado);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
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

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos');
}

try {
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
                $stmt = mysqli_prepare($db,
                    "SELECT m.id AS idMateria, m.nombre AS nombre,
                            c.nombre AS curso, c.abreviatura AS abrevCurso,
                            m.horas AS horas, m.horas_empresa AS horas_empresa,
                            m.asignada_directiva AS asignada_directiva, m.tipo AS tipo
                     FROM materias m
                     LEFT JOIN cursos c ON c.id = m.idCurso
                     WHERE m.tiene_programacion = TRUE AND m.idDepartamento = ?
                     ORDER BY c.orden, c.nombre, m.nombre");
                mysqli_stmt_bind_param($stmt, "i", $idDepartamento);
            } else {
                // Profesor: solo las materias que imparte en los escenarios actuales
                $idProfesor = intval($_SESSION['idUsuario']);
                $stmt = mysqli_prepare($db,
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
                     ORDER BY m.nombre");
                mysqli_stmt_bind_param($stmt, "i", $idProfesor);
            }
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_error($db));
            }
            $resultadoMaterias = mysqli_stmt_get_result($stmt);
            $materias = [];
            while ($fila = mysqli_fetch_assoc($resultadoMaterias)) {
                $materias[] = $fila;
            }
            mysqli_stmt_close($stmt);
            closeDBConnection($db);
            sendJSONSuccess($materias);
            break;

        // Carga los RA (y sus CE) de una materia. Solo lectura.
        case 'cargar':
            $idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
            if ($idMateria <= 0) {
                throw new Exception('ID de materia inválido');
            }
            // Horas de docencia en empresa para la materia
            $result = mysqli_query($db, "SELECT horas_empresa FROM materias WHERE id = $idMateria");
            $fila = mysqli_fetch_assoc($result);
            $horasEmpresa = $fila ? $fila['horas_empresa'] : 0;
            mysqli_free_result($result);

            $result = mysqli_query($db, "SELECT * FROM resultados_aprendizaje WHERE idMateria = $idMateria ORDER BY orden");
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $resultados = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $resultados[] = $fila;
            }
            mysqli_free_result($result);

            closeDBConnection($db);
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
                $texto = mysqli_real_escape_string($db, $texto);
                $query = "UPDATE resultados_aprendizaje SET texto='$texto', orden=$orden, porcentaje_empresa=$porcentajeEmpresa WHERE id=$id";
            } else {
                $texto = mysqli_real_escape_string($db, $texto);
                $query = "INSERT INTO resultados_aprendizaje (idMateria, texto, orden, porcentaje_empresa) VALUES ($idMateria, '$texto', $orden, $porcentajeEmpresa)";
            }
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            $nuevoId = ($id > 0) ? $id : mysqli_insert_id($db);
            closeDBConnection($db);
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
            $query = "UPDATE materias SET horas_empresa=$horas WHERE id=$idMateria";
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
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
            $query = "UPDATE resultados_aprendizaje SET porcentaje_evaluacion=$porcentajeEvaluacion, es_clave=$esClave WHERE id=$idResultado";
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
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
            if (!mysqli_query($db, "DELETE FROM criterios_evaluacion WHERE idRA=$id") ||
                !mysqli_query($db, "DELETE FROM resultados_aprendizaje WHERE id=$id")) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Resultado de aprendizaje eliminado');
            break;

        // Devuelve un resultado de aprendizaje concreto
        case 'obtener':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) {
                throw new Exception('ID de resultado inválido');
            }
            $result = mysqli_query($db, "SELECT * FROM resultados_aprendizaje WHERE id=$id");
            $fila = mysqli_fetch_assoc($result);
            if (!$fila) {
                throw new Exception('Resultado no encontrado');
            }
            closeDBConnection($db);
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
            $codigo = mysqli_real_escape_string($db, $codigo);
            $texto = empty($texto) ? '' : mysqli_real_escape_string($db, $texto);
            $query = "INSERT INTO criterios_evaluacion (idRA, codigo, texto) VALUES ($idResultado, '$codigo', '$texto')";
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            $nuevoId = mysqli_insert_id($db);
            closeDBConnection($db);
            sendJSONSuccess(array('id' => $nuevoId), 'Criterio de evaluación guardado');
            break;

        // Actualiza un criterio de evaluación
        case 'actualizar_criterio':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $idResultado = intval($datos['idResultado']);
            $codigoAntiguo = mysqli_real_escape_string($db, $datos['codigo']);
            $nuevoCodigo = mysqli_real_escape_string($db, $datos['nuevoCodigo']);
            $nuevoTexto = $datos['nuevoTexto'] === null ? '' : mysqli_real_escape_string($db, $datos['nuevoTexto']);
            if ($idResultado <= 0 || empty($codigoAntiguo)) {
                throw new Exception('Datos incompletos para actualizar el criterio');
            }
            comprobarDepartamento(idDepartamentoDeRA($db, $idResultado));
            $query = "UPDATE criterios_evaluacion SET codigo='$nuevoCodigo', texto='$nuevoTexto' WHERE idRA=$idResultado AND codigo='$codigoAntiguo'";
            if (!mysqli_query($db, $query)) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Criterio de evaluación actualizado');
            break;

        // Elimina un criterio de evaluación
        case 'eliminar_criterio':
            if (!tienePermisoEdicion()) {
                throw new Exception('No tiene permisos para realizar esta acción');
            }
            $idResultado = intval($datos['idResultado']);
            $codigo = mysqli_real_escape_string($db, $datos['codigo']);
            if ($idResultado <= 0 || empty($codigo)) {
                throw new Exception('Datos incompletos para eliminar el criterio');
            }
            comprobarDepartamento(idDepartamentoDeRA($db, $idResultado));
            if (!mysqli_query($db, "DELETE FROM criterios_evaluacion WHERE idRA=$idResultado AND codigo='$codigo'")) {
                throw new Exception(mysqli_error($db));
            }
            closeDBConnection($db);
            sendJSONSuccess(null, 'Criterio de evaluación eliminado');
            break;

        // Carga los criterios de evaluación asociados a un resultado
        case 'cargar_criterios':
            $idResultado = isset($_GET['idResultado']) ? intval($_GET['idResultado']) : 0;
            if ($idResultado <= 0) {
                throw new Exception('ID de resultado inválido');
            }
            $result = mysqli_query($db, "SELECT * FROM criterios_evaluacion WHERE idRA=$idResultado ORDER BY codigo");
            if (!$result) {
                throw new Exception(mysqli_error($db));
            }
            $criterios = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $criterios[] = $fila;
            }
            mysqli_free_result($result);
            closeDBConnection($db);
            sendJSONSuccess($criterios);
            break;

        default:
            throw new Exception('Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    closeDBConnection($db);
    sendJSONError($e->getMessage());
}
