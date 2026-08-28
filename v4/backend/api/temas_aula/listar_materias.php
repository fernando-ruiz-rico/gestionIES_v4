<?php
// Programaciones de aula — Listar las materias que imparte el profesor para el
// grupo elegido en el escenario actual (para el selector de la vista de
// unidades de aula). Espejo de api/temas/listar_materias.php, pero limitado al
// (profesor, grupo) de la copia: la materia con la que se llegó desde
// «Programaciones de aula» → «Unidades» debe aparecer aquí para poder
// preseleccionarla aunque aún no exista copia.
require_once '../../config.php';
require_once '../../lib/temas.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $idGrupo    = getOptimoInt('idGrupo');
    $idProfesor = getOptimoInt('idProfesor');

    $filas = array();
    if ($idGrupo > 0 && $idProfesor > 0) {
        $filas = $db->fetchAll(
            "SELECT DISTINCT m.id AS id, m.nombre AS materia, c.nombre AS curso, m.horas_anuales, c.orden
               FROM materias m
               LEFT JOIN cursos c ON c.id = m.idCurso
               JOIN seleccion s ON s.idMateria = m.id
               JOIN escenarios_desideratas e ON e.id = s.idEscenario
              WHERE m.tiene_programacion = 1
                AND e.actual = 1
                AND s.idProfesor = ?
                AND s.idGrupo = ?
              ORDER BY c.orden, c.nombre, m.nombre",
            $idProfesor, $idGrupo);
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
?>
