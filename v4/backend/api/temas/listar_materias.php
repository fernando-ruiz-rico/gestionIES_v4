<?php
// FASE 2.6 — Listar materias con programación activa (para el selector).
// Fiel a v3 (cargar_materias_programaciones.php): el profesor solo ve las
// materias que imparte en los escenarios actuales; el jefe, las de su
// departamento; el admin, todas (v4 no tiene el selector de v3).
require_once '../../config.php';
require_once '../../lib/temas.php';
cabeceraJson();

try {
    $db = Db::open();
    $session = checkSession();

    $rol = isset($session['rol']) ? $session['rol'] : '';
    $filas = array();
    if ($rol === ROLE_PROFESOR) {
        $idProfesor = (int)$session['idUsuario'];
        $sql = "SELECT DISTINCT m.id AS id, m.nombre AS materia, c.nombre AS curso, m.horas_anuales
                  FROM materias m
                  LEFT JOIN cursos c ON c.id = m.idCurso
                  LEFT JOIN seleccion s ON s.idMateria = m.id
                  LEFT JOIN escenarios_desideratas e ON e.id = s.idEscenario
                  WHERE m.tiene_programacion = 1 AND e.actual = 1 AND s.idProfesor = ?
                  ORDER BY m.nombre";
        $filas = $db->fetchAll($sql, $idProfesor);
    } else {
        $idDepartamento = !empty($session['idDepartamento']) ? (int)$session['idDepartamento'] : 0;
        $sql = "SELECT m.id AS id, m.nombre AS materia, c.nombre AS curso, m.horas_anuales
                  FROM materias m
                  LEFT JOIN cursos c ON c.id = m.idCurso
                  WHERE m.tiene_programacion = 1";
        $params = array();
        if ($idDepartamento > 0) {
            $sql .= " AND m.idDepartamento = ?";
            $params[] = $idDepartamento;
        }
        $sql .= " ORDER BY c.orden, c.nombre, m.nombre";
        $filas = $db->fetchAll($sql, ...$params);
    }

    $materias = array();
    foreach ($filas as $fila) {
        $idMateria = intval($fila['id']);
        $materias[] = [
            'id' => $idMateria,
            'materia' => $fila['materia'],
            'curso' => $fila['curso'],
            'horas_anuales' => intval($fila['horas_anuales']),
            'idCiclo' => temas_id_ciclo_por_materia($db, $idMateria)
        ];
    }

    $db->close();
    sendJSONSuccess($materias);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
