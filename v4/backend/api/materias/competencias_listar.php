<?php
// Lista las competencias profesionales asociadas a una materia y las del
// ciclo al que pertenece (para el desplegable de "Añadir").
// Fiel a v3: v3/ajax/materias/cargar_competencias_materia.php (lectura).
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
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

// Nombre de la materia
$resN = mysqli_query($db, "SELECT nombre FROM materias WHERE id = " . intval($idMateria));
$filaN = mysqli_fetch_assoc($resN);
$nombreMateria = isset($filaN['nombre']) ? $filaN['nombre'] : '';
mysqli_free_result($resN);

// Ciclo al que pertenece la materia (primer ciclo del curso, igual que v3)
$resC = mysqli_query($db, "SELECT ciclos.id AS id FROM ciclos, cursos, cursos_ciclos, materias WHERE ciclos.id = cursos_ciclos.idCiclo AND cursos.id = cursos_ciclos.idCurso AND materias.idCurso = cursos.id AND materias.id = " . intval($idMateria));
$filaC = mysqli_fetch_assoc($resC);
$idCiclo = isset($filaC['id']) ? intval($filaC['id']) : 0;
mysqli_free_result($resC);

// Competencias ya asociadas a la materia
$asociadas = [];
$resA = mysqli_query($db, "SELECT competencias_ciclos.* FROM competencias_ciclos, competencias_materias WHERE competencias_ciclos.id = competencias_materias.idCompetencia AND competencias_materias.idMateria = " . intval($idMateria) . " ORDER BY competencias_ciclos.orden");
while ($fA = mysqli_fetch_assoc($resA)) {
    $asociadas[] = ['id' => intval($fA['id']), 'codigo' => $fA['codigo'], 'texto' => $fA['texto']];
}
mysqli_free_result($resA);

// Opciones para añadir (todas las del ciclo, ordenadas por codigo)
$opciones = [];
$resO = mysqli_query($db, "SELECT * FROM competencias_ciclos WHERE idCiclo = " . intval($idCiclo) . " ORDER BY codigo");
while ($fO = mysqli_fetch_assoc($resO)) {
    $opciones[] = ['id' => intval($fO['id']), 'codigo' => $fO['codigo'], 'texto' => $fO['texto']];
}
mysqli_free_result($resO);
mysqli_close($db);

echo json_encode([
    'idMateria' => $idMateria,
    'nombreMateria' => $nombreMateria,
    'idCiclo' => $idCiclo,
    'asociadas' => $asociadas,
    'opciones' => $opciones
]);
?>
