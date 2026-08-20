<?php
// API endpoint para guardar contenido por defecto de un apartado
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

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

$textoEscapado = mysqli_real_escape_string($db, trim($texto));

// Si no hay texto, eliminamos el contenido por defecto
if (empty($textoEscapado)) {
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
        mysqli_stmt_bind_param($stmt, "sii", $textoEscapado, $idApartado, $idDepartamento);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true]);
    } else {
        // Insertar
        $stmt = mysqli_prepare($db, "INSERT INTO contenidos_defecto_programaciones (idApartado, idDepartamento, texto) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $idApartado, $idDepartamento, $textoEscapado);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true]);
    }
    
    mysqli_free_result($resultCheck);
    mysqli_stmt_close($stmtCheck);
}

mysqli_close($db);
?>
