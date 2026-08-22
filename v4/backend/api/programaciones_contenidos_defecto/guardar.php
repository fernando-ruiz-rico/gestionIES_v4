<?php
// API endpoint para guardar contenido por defecto de un apartado
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$data = json_decode(file_get_contents('php://input'), true);

$idApartado = isset($data['idApartado']) ? intval($data['idApartado']) : 0;
$idDepartamento = isset($data['idDepartamento']) ? intval($data['idDepartamento']) : 0;
$texto = isset($data['texto']) ? $data['texto'] : '';

if ($idApartado <= 0 || $idDepartamento <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Parámetros no válidos']);
    exit;
}

$db = getDBConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$texto = trim($texto);

// Si no hay texto, eliminamos el contenido por defecto
if ($texto === '') {
    $stmt = mysqli_prepare($db, "DELETE FROM contenidos_defecto_programaciones WHERE idApartado = ? AND idDepartamento = ?");
    mysqli_stmt_bind_param($stmt, "ii", $idApartado, $idDepartamento);
    mysqli_stmt_execute($stmt);
    echo json_encode(['success' => true]);
} else {
    // Verificar si ya existe
    $stmtCheck = mysqli_prepare($db, "SELECT id FROM contenidos_defecto_programaciones WHERE idApartado = ? AND idDepartamento = ?");
    mysqli_stmt_bind_param($stmtCheck, "ii", $idApartado, $idDepartamento);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);

    if (mysqli_num_rows($resultCheck) > 0) {
        // Actualizar
        $stmt = mysqli_prepare($db, "UPDATE contenidos_defecto_programaciones SET texto = ? WHERE idApartado = ? AND idDepartamento = ?");
        mysqli_stmt_bind_param($stmt, "sii", $texto, $idApartado, $idDepartamento);
    } else {
        // Insertar
        $stmt = mysqli_prepare($db, "INSERT INTO contenidos_defecto_programaciones (idApartado, idDepartamento, texto) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $idApartado, $idDepartamento, $texto);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . mysqli_error($db)]);
    }

    mysqli_free_result($resultCheck);
    mysqli_stmt_close($stmtCheck);
}

mysqli_close($db);
?>
