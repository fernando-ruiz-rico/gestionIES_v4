<?php
// Inserta/Modifica los datos de una materia para un grupo determinado.
// Fiel a v3: v3/ajax/materias/insertar_materia_grupo.php (jefe o admin).
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$idMateria = intval(isset($datos['idMateria']) ? $datos['idMateria'] : 0);
$idGrupo = intval(isset($datos['idGrupo']) ? $datos['idGrupo'] : 0);

if ($idMateria <= 0 || $idGrupo <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

$cantidad = intval(isset($datos['cantidad']) ? $datos['cantidad'] : 1);
$horas = intval(isset($datos['horas']) ? $datos['horas'] : 0);
$horasComplementarias = intval(isset($datos['horas_complementarias']) ? $datos['horas_complementarias'] : 0);
$minNumProfesores = intval(isset($datos['min_num_profesores']) ? $datos['min_num_profesores'] : 0);
$maxGruposProfesor = intval(isset($datos['max_grupos_profesor']) ? $datos['max_grupos_profesor'] : 0);

// Comprobamos si ya existe un registro para ese grupo y materia
$res = mysqli_query($db, "SELECT * FROM materias_grupos WHERE idMateria = " . intval($idMateria) . " AND idGrupo = " . intval($idGrupo));
$existe = mysqli_num_rows($res) > 0;
mysqli_free_result($res);

if ($existe) {
    $stmt = mysqli_prepare($db, "UPDATE materias_grupos SET cantidad=?, horas=?, horas_complementarias=?, min_num_profesores=?, max_grupos_profesor=? WHERE idMateria=? AND idGrupo=?");
    mysqli_stmt_bind_param($stmt, "iiiiiii", $cantidad, $horas, $horasComplementarias, $minNumProfesores, $maxGruposProfesor, $idMateria, $idGrupo);
} else {
    $stmt = mysqli_prepare($db, "INSERT INTO materias_grupos (idMateria, idGrupo, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiiiiii", $idMateria, $idGrupo, $cantidad, $horas, $horasComplementarias, $minNumProfesores, $maxGruposProfesor);
}
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($db);

echo json_encode([
    'success' => (bool) $ok,
    'message' => $ok ? 'Datos del grupo guardados' : 'Error al guardar los datos del grupo'
]);
?>
