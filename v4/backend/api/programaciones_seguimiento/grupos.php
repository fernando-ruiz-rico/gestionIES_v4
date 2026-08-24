<?php
// API: Listar grupos de un profesor para una materia (seguimiento de programaciones)
require_once '../../config.php';
cabeceraJson();

$session = checkSession();

$idMateria = getOptimoInt('idMateria');
$rol = $session['rol'];
$idProfesorSesion = intval($session['idUsuario']);

if ($idMateria <= 0) {
    sendJSONError('Debe indicar una materia', 400);
}

// Admin puede ver grupos de cualquier profesor
if (esUsuarioSuper($rol)) {
    $idProfesor = getOptimoInt('idProfesor', $idProfesorSesion);
} else {
    $idProfesor = $idProfesorSesion;
}

try {
    $db = Db::open();

    $filas = $db->fetchAll("SELECT g.id AS id, g.nombre AS nombre
                                FROM grupos g
                                WHERE g.id IN (
                                    SELECT s.idGrupo FROM seleccion s
                                    JOIN escenarios_desideratas e ON e.id = s.idEscenario
                                    WHERE s.idMateria = ? AND s.idProfesor = ? AND e.actual = 1
                                )
                                ORDER BY g.nombre",
        $idMateria, $idProfesor);

    $grupos = [];
    foreach ($filas as $fila) {
        $grupos[] = [
            'id'      => intval($fila['id']),
            'nombre'  => $fila['nombre']
        ];
    }

    sendJSONSuccess($grupos);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
