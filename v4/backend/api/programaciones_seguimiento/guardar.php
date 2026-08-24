<?php
// API: Guardar los datos de seguimiento de una programación de aula
// (equivalente a v3 ajax/programaciones_seguimiento/insertar_seguimiento_programacion_aula.php)
// Inserta o actualiza la fila del triplete materia+grupo+profesor en el curso actual;
// con textos vacíos se guarda el texto vacío, idéntico al comportamiento de v3.
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

// En v3 el guardado está permitido a admin/jefe (para cualquier profesor) y a un profesor para sí mismo
$rol = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

if (esUsuarioSuper($rol)) {
    // Admin puede guardar para cualquier profesor
} else {
    // Un profesor solo puede guardar el seguimiento de sí mismo
    if (isset($session['activo']) && $session['activo'] == 1) {
        // Ok, continuar
    } else {
        sendJSONError('No tiene permisos para realizar esta acción', 403);
    }
}

$data = json_decode(file_get_contents('php://input'), true);

$idMateria      = isset($data['idMateria']) ? intval($data['idMateria']) : 0;
$idGrupo        = isset($data['idGrupo']) ? intval($data['idGrupo']) : 0;
$idEvaluacion   = isset($data['idEvaluacion']) ? intval($data['idEvaluacion']) : 0;
$temporalizacion = isset($data['temporalizacion']) ? $data['temporalizacion'] : '';
$resultados      = isset($data['resultados']) ? $data['resultados'] : '';
$inclusion       = isset($data['inclusion']) ? $data['inclusion'] : '';
$numAprobados   = isset($data['num_aprobados']) ? intval($data['num_aprobados']) : 0;
$numSuspensos   = isset($data['num_suspensos']) ? intval($data['num_suspensos']) : 0;
$numOtros       = isset($data['num_otros']) ? intval($data['num_otros']) : 0;

// Determinar idProfesor según rol
if (esUsuarioSuper($rol)) {
    $idProfesor = isset($data['idProfesor']) ? intval($data['idProfesor']) : $idUsuarioSesion;
} else {
    $idProfesor = $idUsuarioSesion;
}

if ($idMateria <= 0 || $idGrupo <= 0 || $idEvaluacion <= 0 || $idProfesor <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();

    $curso = cursoActual();

    // Verificar si ya existe una fila para esta combinación (materia + grupo + profesor + curso + evaluación)
    $existe = $db->count("SELECT id FROM seguimiento_programaciones_aula WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ? AND curso = ? AND evaluacion = ?",
        $idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion) > 0;

    if ($existe) {
        $db->execute("UPDATE seguimiento_programaciones_aula
                        SET temporalizacion = ?, resultados = ?, inclusion = ?, num_aprobados = ?, num_suspensos = ?, num_otros = ?
                        WHERE idMateria = ? AND idGrupo = ? AND idProfesor = ? AND curso = ? AND evaluacion = ?",
            $temporalizacion, $resultados, $inclusion, $numAprobados, $numSuspensos, $numOtros,
            $idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion);
    } else {
        $db->execute("INSERT INTO seguimiento_programaciones_aula (idMateria, idGrupo, idProfesor, curso, evaluacion, temporalizacion, resultados, inclusion, num_aprobados, num_suspensos, num_otros)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $idMateria, $idGrupo, $idProfesor, $curso, $idEvaluacion,
            $temporalizacion, $resultados, $inclusion,
            $numAprobados, $numSuspensos, $numOtros);
    }

    sendJSONSuccess(null, 'Seguimiento guardado correctamente');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
