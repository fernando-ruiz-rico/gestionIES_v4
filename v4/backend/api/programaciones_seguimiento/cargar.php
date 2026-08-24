<?php
// API: Cargar los datos de seguimiento de una programación de aula
// (equivalente a v3 ajax/programaciones_seguimiento/cargar_datos_seguimiento_aula.php)
// Curso: el actual, calculado en el servidor igual que cursoActual() de v3
require_once '../../config.php';
cabeceraJson();

$session = checkSession();

$idMateria    = getOptimoInt('idMateria');
$idGrupo      = getOptimoInt('idGrupo');
$idEvaluacion = getOptimoInt('idEvaluacion');
$rol          = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

if ($idMateria <= 0 || $idGrupo <= 0 || $idEvaluacion <= 0) {
    sendJSONError('Debe indicar materia, grupo y evaluación', 400);
}

// Admin puede ver seguimiento de cualquier profesor
if (esUsuarioSuper($rol)) {
    $idProfesor = getOptimoInt('idProfesor', $idUsuarioSesion);
} else {
    $idProfesor = $idUsuarioSesion;
}

if ($idProfesor <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();

    $curso = cursoActual();

    $fila = $db->fetchOne("SELECT temporalizacion, resultados, inclusion, num_aprobados, num_suspensos, num_otros
                                FROM seguimiento_programaciones_aula
                                WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ? AND curso = ? AND evaluacion = ?",
        $idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion);

    $data = [
        'temporalizacion' => '',
        'resultados'      => '',
        'inclusion'       => '',
        'num_aprobados'   => 0,
        'num_suspensos'   => 0,
        'num_otros'       => 0
    ];

    if ($fila) {
        $data = [
            'temporalizacion' => $fila['temporalizacion'],
            'resultados'      => $fila['resultados'],
            'inclusion'       => $fila['inclusion'],
            'num_aprobados'   => intval($fila['num_aprobados']),
            'num_suspensos'   => intval($fila['num_suspensos']),
            'num_otros'       => intval($fila['num_otros'])
        ];
    }

    sendJSONSuccess($data);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
