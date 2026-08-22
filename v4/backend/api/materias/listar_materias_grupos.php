<?php
// Lista los grupos de un curso con los valores de materias_grupos de una
// materia (para el formulario de datos por grupo). Fiel a v3:
// v3/ajax/materias/cargar_forms_materias_grupos.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$idMateria = intval(isset($_GET['idMateria']) ? $_GET['idMateria'] : 0);
$idCurso = intval(isset($_GET['idCurso']) ? $_GET['idCurso'] : 0);

if ($idMateria <= 0 || $idCurso <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

// Datos de cabecera (curso - materia)
$resH = mysqli_query($db, "SELECT c.nombre AS nombreCurso, m.nombre AS nombreMateria FROM cursos c, materias m WHERE c.id = m.idCurso AND c.id = " . intval($idCurso) . " AND m.id = " . intval($idMateria));
$filaH = mysqli_fetch_assoc($resH);
mysqli_free_result($resH);

// Datos de referencia de la materia (para el botón "Importar")
$resG = mysqli_query($db, "SELECT cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor FROM materias WHERE id = " . intval($idMateria));
$general = mysqli_fetch_assoc($resG);
mysqli_free_result($resG);

// Grupos del curso con sus valores de materias_grupos (o null si no están)
$grupos = [];
$resGr = mysqli_query($db, "SELECT id, nombre FROM grupos WHERE idCurso = " . intval($idCurso) . " ORDER BY orden");
while ($fGr = mysqli_fetch_assoc($resGr)) {
    $g = [
        'id' => intval($fGr['id']),
        'nombre' => $fGr['nombre'],
        'cantidad' => null,
        'horas' => null,
        'horas_complementarias' => null,
        'min_num_profesores' => null,
        'max_grupos_profesor' => null
    ];
    $resMG = mysqli_query($db, "SELECT * FROM materias_grupos WHERE idMateria = " . intval($idMateria) . " AND idGrupo = " . intval($fGr['id']));
    $filaMG = mysqli_fetch_assoc($resMG);
    if ($filaMG) {
        $g['cantidad'] = intval($filaMG['cantidad']);
        $g['horas'] = intval($filaMG['horas']);
        $g['horas_complementarias'] = intval($filaMG['horas_complementarias']);
        $g['min_num_profesores'] = intval($filaMG['min_num_profesores']);
        $g['max_grupos_profesor'] = intval($filaMG['max_grupos_profesor']);
    }
    mysqli_free_result($resMG);
    $grupos[] = $g;
}
mysqli_free_result($resGr);
mysqli_close($db);

echo json_encode([
    'idCurso' => $idCurso,
    'idMateria' => $idMateria,
    'nombreCurso' => isset($filaH['nombreCurso']) ? $filaH['nombreCurso'] : '',
    'nombreMateria' => isset($filaH['nombreMateria']) ? $filaH['nombreMateria'] : '',
    'general' => $general,
    'grupos' => $grupos
]);
?>
