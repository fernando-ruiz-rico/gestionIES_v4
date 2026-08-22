<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$nombre = trim(isset($datos['nombre']) ? $datos['nombre'] : '');
$abreviatura = trim(isset($datos['abreviatura']) ? $datos['abreviatura'] : '');
$idCurso = intval(isset($datos['idCurso']) ? $datos['idCurso'] : 0);
$mostrar = intval(isset($datos['mostrar']) ? $datos['mostrar'] : 1);
$horas_complementarias_dual = intval(isset($datos['horas_complementarias_dual']) ? $datos['horas_complementarias_dual'] : 0);
$id = isset($datos['id']) ? intval($datos['id']) : 0;

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre obligatorio']);
    exit;
}

if ($id > 0) {
    // Fiel a v3: al modificar solo se actualizan nombre, abreviatura, mostrar y
    // horas_complementarias_dual (ni idCurso ni orden se tocan aquí: el orden se
    // gestiona en el endpoint de reordenación y el curso no cambia al editar).
    $stmt = mysqli_prepare($db, "UPDATE grupos SET nombre=?, abreviatura=?, mostrar=?, horas_complementarias_dual=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssiii", $nombre, $abreviatura, $mostrar, $horas_complementarias_dual, $id);
    $ok = mysqli_stmt_execute($stmt);
} else {
    // Fiel a v3: al crear no se fija orden (queda NULL) y se copia la
    // configuración de las materias del curso a materias_grupos para el grupo nuevo.
    $stmt = mysqli_prepare($db, "INSERT INTO grupos (nombre, abreviatura, idCurso, mostrar, horas_complementarias_dual) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiii", $nombre, $abreviatura, $idCurso, $mostrar, $horas_complementarias_dual);
    $ok = mysqli_stmt_execute($stmt);

    if ($ok) {
        $idNuevo = mysqli_insert_id($db);
        $id = $idNuevo;
        $res = mysqli_query($db, "SELECT id, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor FROM materias WHERE idCurso = " . $idCurso);
        if ($res) {
            while ($m = mysqli_fetch_assoc($res)) {
                $stmtMg = mysqli_prepare($db, "INSERT INTO materias_grupos (idMateria, idGrupo, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $idMateria = intval($m['id']);
                $cantidad = intval($m['cantidad']);
                $horas = intval($m['horas']);
                $horasCom = intval($m['horas_complementarias']);
                $minProf = intval($m['min_num_profesores']);
                $maxGrp = intval($m['max_grupos_profesor']);
                mysqli_stmt_bind_param($stmtMg, "iiiiiii", $idMateria, $idNuevo, $cantidad, $horas, $horasCom, $minProf, $maxGrp);
                mysqli_stmt_execute($stmtMg);
                mysqli_stmt_close($stmtMg);
            }
            mysqli_free_result($res);
        }
    }
}

echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Guardado correctamente' : 'Error al guardar',
    'id' => $id
]);

if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
mysqli_close($db);
?>
