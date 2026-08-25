<?php
// API: Guardar los datos de seguimiento de una programación de aula
// (equivalente a v3 ajax/programaciones_seguimiento/insertar_seguimiento_programacion_aula.php)
// Inserta o actualiza la fila del triplete materia+grupo+profesor en el curso actual;
// con textos vacíos se guarda el texto vacío, idéntico al comportamiento de v3.
require_once '../../config.php';
cabeceraJson();

$session = checkSession();

// En v3 el guardado está permitido a admin/jefe (para cualquier profesor) y a un profesor para sí mismo
$rol = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

// Un superusuario (admin/jefe) puede guardar para cualquier profesor; un
// profesor solo puede guardarlo para sí mismo y debe estar activo.
if (!esUsuarioSuper($rol)) {
    if (!isset($session['activo']) || $session['activo'] != 1) {
        sendJSONError('No tiene permisos para realizar esta acción', 403);
    }
}

$data = cuerpoJson();

$idMateria      = datosOptimoInt($data, 'idMateria');
$idGrupo        = datosOptimoInt($data, 'idGrupo');
$idEvaluacion   = datosOptimoInt($data, 'idEvaluacion');
$temporalizacion = datosOptimo($data, 'temporalizacion');
$resultados      = datosOptimo($data, 'resultados');
$inclusion       = datosOptimo($data, 'inclusion');
$numAprobados   = datosOptimoInt($data, 'num_aprobados');
$numSuspensos   = datosOptimoInt($data, 'num_suspensos');
$numOtros       = datosOptimoInt($data, 'num_otros');

// Determinar idProfesor según rol
if (esUsuarioSuper($rol)) {
    $idProfesor = datosOptimoInt($data, 'idProfesor', $idUsuarioSesion);
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
