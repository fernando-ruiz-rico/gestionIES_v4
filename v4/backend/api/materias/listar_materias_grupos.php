<?php
// Lista los grupos de un curso con los valores de materias_grupos de una
// materia (para el formulario de datos por grupo). Fiel a v3:
// v3/ajax/materias/cargar_forms_materias_grupos.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idMateria = getOptimoInt('idMateria');
$idCurso = getOptimoInt('idCurso');

if ($idMateria <= 0 || $idCurso <= 0) {
    sendJSONError('Parámetros inválidos', 400);
}

try {
    $db = Db::open();

    // Datos de cabecera (curso - materia)
    $filaH = $db->fetchOne("SELECT c.nombre AS nombreCurso, m.nombre AS nombreMateria FROM cursos c, materias m WHERE c.id = m.idCurso AND c.id = ? AND m.id = ?", $idCurso, $idMateria);

    // Datos de referencia de la materia (para el botón "Importar")
    $general = $db->fetchOne("SELECT cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor FROM materias WHERE id = ?", $idMateria);

    // Grupos del curso con sus valores de materias_grupos (o null si no están)
    $grupos = [];
    foreach ($db->fetchAll("SELECT id, nombre FROM grupos WHERE idCurso = ? ORDER BY orden", $idCurso) as $fGr) {
        $g = [
            'id' => intval($fGr['id']),
            'nombre' => $fGr['nombre'],
            'cantidad' => null,
            'horas' => null,
            'horas_complementarias' => null,
            'min_num_profesores' => null,
            'max_grupos_profesor' => null
        ];
        $filaMG = $db->fetchOne("SELECT * FROM materias_grupos WHERE idMateria = ? AND idGrupo = ?", $idMateria, intval($fGr['id']));
        if ($filaMG) {
            $g['cantidad'] = intval($filaMG['cantidad']);
            $g['horas'] = intval($filaMG['horas']);
            $g['horas_complementarias'] = intval($filaMG['horas_complementarias']);
            $g['min_num_profesores'] = intval($filaMG['min_num_profesores']);
            $g['max_grupos_profesor'] = intval($filaMG['max_grupos_profesor']);
        }
        $grupos[] = $g;
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess([
    'idCurso' => $idCurso,
    'idMateria' => $idMateria,
    'nombreCurso' => isset($filaH['nombreCurso']) ? $filaH['nombreCurso'] : '',
    'nombreMateria' => isset($filaH['nombreMateria']) ? $filaH['nombreMateria'] : '',
    'general' => $general,
    'grupos' => $grupos
]);
?>
