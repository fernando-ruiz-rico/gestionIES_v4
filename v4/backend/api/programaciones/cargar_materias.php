<?php
// FASE 2.1 — Desplegable de materias (fiel a v3/cargar_materias_programaciones.php).
// Por rol: el profesor solo ve las suyas; el jefe, las de su departamento;
// el admin, todas (v4 no tiene la selección de departamento de v3).
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();

    $session = checkSession();
    $rol = $session['rol'];

    if ($rol === ROLE_PROFESOR) {
        $idProfesor = (int)$session['idUsuario'];
        $sql = "SELECT DISTINCT m.id AS id, m.nombre AS nomMateria, cu.nombre AS nomCurso
                  FROM materias m
                  JOIN cursos cu ON cu.id = m.idCurso
                  JOIN seleccion s ON s.idMateria = m.id
                  JOIN escenarios_desideratas e ON e.id = s.idEscenario
                 WHERE s.idProfesor = ?
                   AND m.tiene_programacion = 1
                   AND e.actual = 1
                 ORDER BY m.nombre";
        $materias = array();
        foreach ($db->fetchAll($sql, $idProfesor) as $fila) {
            $materias[] = [
                'id'       => (int)$fila['id'],
                'nombre'   => $fila['nomMateria'] . ' (' . $fila['nomCurso'] . ')'
            ];
        }
    } elseif (esUsuarioSuper($rol)) {
        // Fiel a v3: el jefe ve las del departamento; el admin (sin
        // selector de departamento en v4) ve todas.
        $idDepartamento = !empty($session['idDepartamento']) ? (int)$session['idDepartamento'] : 0;
        $sql = "SELECT DISTINCT m.id AS id, m.nombre AS nombre, c.orden AS ordenCurso
                  FROM materias m
                  JOIN cursos c ON c.id = m.idCurso
                 WHERE m.tiene_programacion = 1";
        $params = array();
        if ($idDepartamento > 0) {
            $sql .= " AND m.idDepartamento = ?";
            $params[] = $idDepartamento;
        }
        $sql .= " ORDER BY c.orden, m.nombre";
        $materias = array();
        foreach ($db->fetchAll($sql, ...$params) as $fila) {
            $materias[] = ['id' => (int)$fila['id'], 'nombre' => $fila['nombre']];
        }
    } else {
        throw new Exception('Rol no reconocido');
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
