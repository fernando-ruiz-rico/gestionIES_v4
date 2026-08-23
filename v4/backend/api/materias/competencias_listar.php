<?php
// Lista las competencias profesionales asociadas a una materia y las del
// ciclo al que pertenece (para el desplegable de "Añadir").
// Fiel a v3: v3/ajax/materias/cargar_competencias_materia.php (lectura).
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$idMateria = intval(isset($_GET['idMateria']) ? $_GET['idMateria'] : 0);
if ($idMateria <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetro inválido']);
    exit;
}

try {
    $db = new Db($conn);

    // Nombre de la materia
    $filaN = $db->fetchOne("SELECT nombre FROM materias WHERE id = " . intval($idMateria));
    $nombreMateria = isset($filaN['nombre']) ? $filaN['nombre'] : '';

    // Ciclo al que pertenece la materia (primer ciclo del curso, igual que v3)
    $filaC = $db->fetchOne("SELECT ciclos.id AS id FROM ciclos, cursos, cursos_ciclos, materias WHERE ciclos.id = cursos_ciclos.idCiclo AND cursos.id = cursos_ciclos.idCurso AND materias.idCurso = cursos.id AND materias.id = " . intval($idMateria));
    $idCiclo = isset($filaC['id']) ? intval($filaC['id']) : 0;

    // Competencias ya asociadas a la materia
    $asociadas = [];
    foreach ($db->fetchAll("SELECT competencias_ciclos.* FROM competencias_ciclos, competencias_materias WHERE competencias_ciclos.id = competencias_materias.idCompetencia AND competencias_materias.idMateria = " . intval($idMateria) . " ORDER BY competencias_ciclos.orden") as $fA) {
        $asociadas[] = ['id' => intval($fA['id']), 'codigo' => $fA['codigo'], 'texto' => $fA['texto']];
    }

    // Opciones para añadir (todas las del ciclo, ordenadas por codigo)
    $opciones = [];
    foreach ($db->fetchAll("SELECT * FROM competencias_ciclos WHERE idCiclo = " . intval($idCiclo) . " ORDER BY codigo") as $fO) {
        $opciones[] = ['id' => intval($fO['id']), 'codigo' => $fO['codigo'], 'texto' => $fO['texto']];
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'idMateria' => $idMateria,
    'nombreMateria' => $nombreMateria,
    'idCiclo' => $idCiclo,
    'asociadas' => $asociadas,
    'opciones' => $opciones
]);
?>
