<?php
// API de Resultados de Aprendizaje (Fase 4.1): lista las materias del selector.
// Fiel a v3 (includes/cargar_materias_programaciones.php):
//   - admin : todas las materias del departamento elegido en la vista
//   - jefe  : todas las materias de su departamento
//   - profe : solo las materias que imparte en los escenarios actuales
// En los tres casos, únicamente las materias con programación activa.
require_once '../../config.php';
cabeceraJson();
@session_start();

try {
    $db = Db::open();

    $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
    if ($rol == ROLE_ADMIN || $rol == ROLE_JEFE_DEPARTAMENTO) {
        if ($rol == ROLE_ADMIN) {
            $idDepartamento = getOptimoInt('idDepartamento');
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
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
