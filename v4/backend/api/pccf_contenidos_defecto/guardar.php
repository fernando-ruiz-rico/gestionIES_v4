<?php
// API para guardar el contenido por defecto de un apartado del PCCF (Fase 3.3)
// Inserta o actualiza el contenido del apartado para el departamento indicado.
// Con texto vacío se elimina la fila (fiel a v3).

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

@session_start();
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$permisos = ($rol == 'admin' || $rol == 'jefeDepartamento');
if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

$data = json_decode(file_get_contents('php://input'), true);
$data = $data ?: [];

$idApartado = isset($data['idApartado']) ? intval($data['idApartado']) : 0;
$idDepartamento = isset($data['idDepartamento']) ? intval($data['idDepartamento']) : 0;
$texto = isset($data['texto']) ? $data['texto'] : '';

if ($idApartado <= 0 || $idDepartamento <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    $texto = trim($texto);

    // Sin texto: eliminamos la fila por defecto.
    if ($texto === '') {
        $stmt = mysqli_prepare($db, "DELETE FROM contenidos_defecto_pccf WHERE idApartado = ? AND idDepartamento = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idApartado, $idDepartamento);
        mysqli_stmt_execute($stmt);
        $ok = mysqli_stmt_affected_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        if (!$ok) {
            sendJSONError('No existe contenido que eliminar', 400);
        }
        sendJSONSuccess(null, 'Contenido por defecto eliminado correctamente');
    } else {
        // Comprobamos si ya existe contenido para ese apartado y departamento.
        $stmtCheck = mysqli_prepare($db, "SELECT id FROM contenidos_defecto_pccf WHERE idApartado = ? AND idDepartamento = ?");
        mysqli_stmt_bind_param($stmtCheck, "ii", $idApartado, $idDepartamento);
        mysqli_stmt_execute($stmtCheck);
        $resultCheck = mysqli_stmt_get_result($stmtCheck);

        if (mysqli_num_rows($resultCheck) > 0) {
            // Actualizamos.
            $stmt = mysqli_prepare($db, "UPDATE contenidos_defecto_pccf SET texto = ? WHERE idApartado = ? AND idDepartamento = ?");
            $textoEscapado = escapeString($texto, $db);
            mysqli_stmt_bind_param($stmt, "sii", $textoEscapado, $idApartado, $idDepartamento);
        } else {
            // Insertamos.
            $stmt = mysqli_prepare($db, "INSERT INTO contenidos_defecto_pccf (idDepartamento, idApartado, texto) VALUES (?, ?, ?)");
            $textoEscapado = escapeString($texto, $db);
            mysqli_stmt_bind_param($stmt, "iis", $idDepartamento, $idApartado, $textoEscapado);
        }

        if (!mysqli_stmt_execute($stmt)) {
            sendJSONError('Error al guardar: ' . mysqli_error($db));
        }
        mysqli_stmt_close($stmt);
        sendJSONSuccess(null, 'Datos guardados correctamente');
    }
} catch (Exception $e) {
    sendJSONError($e->getMessage());
} finally {
    closeDBConnection($db);
}
?>
