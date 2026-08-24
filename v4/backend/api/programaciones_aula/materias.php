<?php
// API: Listar materias con programación activa para un profesor
// Solo las del CURSO ACTUAL: la asignación (seleccion) ha de apuntar a un
// escenario de "desideratas" actual (e.actual = 1), fiel a v3.
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

// Admin puede elegir profesor; un profesor usa siempre el suyo
$rol = $session['rol'];
$idProfesorSesion = intval($session['idUsuario']);

if (esUsuarioSuper($rol)) {
    $idProfesor = getOptimoInt('idProfesor', $idProfesorSesion);
} else {
    $idProfesor = $idProfesorSesion;
}

try {
    $db = Db::open();

    // Fiel a v3 (programaciones_aula.php, rama del profesor): solo las materias
    // que imparte en los escenarios ACTUALES (curso actual); mismo criterio que
    // programaciones/cargar_materias, temas, resultados_aprendizaje y grupos.php
    // de esta misma sección (e.actual = 1).
    $filas = $db->fetchAll("SELECT DISTINCT m.id AS id, m.nombre AS nombreMateria, c.nombre AS nomCurso, m.horas AS horas
                            FROM materias m
                            JOIN cursos c ON c.id = m.idCurso
                            JOIN seleccion s ON s.idMateria = m.id
                            JOIN escenarios_desideratas e ON e.id = s.idEscenario
                            WHERE m.tiene_programacion = 1
                              AND s.idProfesor = ?
                              AND e.actual = 1
                            ORDER BY m.nombre", $idProfesor);

    $materias = array();
    foreach ($filas as $fila) {
        $materias[] = array(
            'id'       => intval($fila['id']),
            'nombre'   => $fila['nombreMateria'],
            'nomCurso' => $fila['nomCurso'],
            'horas'    => intval($fila['horas'])
        );
    }

    sendJSONSuccess($materias);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
