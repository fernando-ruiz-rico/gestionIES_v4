<?php
// API para guardar el contenido de un PCCF (Fase 3.1 - PCCF)
// Inserta o actualiza el contenido de un ciclo y apartado concretos en la tabla
// contenidos_pccf (modelo fiel a v3). Con texto vacío se elimina la fila.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$data = json_decode(file_get_contents('php://input'), true);
$data = $data ?: [];

$idCiclo = isset($data['idCiclo']) ? intval($data['idCiclo']) : 0;
$idApartado = isset($data['idApartado']) ? intval($data['idApartado']) : 0;
$texto = isset($data['texto']) ? $data['texto'] : '';

if ($idCiclo <= 0 || $idApartado <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    $texto = trim($texto);

    // Con texto vacío, eliminamos la fila (igual que en las fases 2.3/2.4).
    if ($texto === '') {
        $stmt = mysqli_prepare($db, "DELETE FROM contenidos_pccf WHERE idCiclo = ? AND idApartado = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idCiclo, $idApartado);
        mysqli_stmt_execute($stmt);
        $ok = mysqli_stmt_affected_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        if (!$ok) {
            sendJSONError('No existe contenido que eliminar', 400);
        }
        sendJSONSuccess(null, 'Contenido eliminado correctamente');
    } else {
        // Comprobamos si ya existe contenido para ese ciclo y apartado.
        $stmtCheck = mysqli_prepare($db, "SELECT id FROM contenidos_pccf WHERE idCiclo = ? AND idApartado = ?");
        mysqli_stmt_bind_param($stmtCheck, "ii", $idCiclo, $idApartado);
        mysqli_stmt_execute($stmtCheck);
        $resultCheck = mysqli_stmt_get_result($stmtCheck);

        if (mysqli_num_rows($resultCheck) > 0) {
            // Actualizamos el contenido existente (la sentencia preparada ya escapa).
            $stmt = mysqli_prepare($db, "UPDATE contenidos_pccf SET texto = ? WHERE idCiclo = ? AND idApartado = ?");
            mysqli_stmt_bind_param($stmt, "sii", $texto, $idCiclo, $idApartado);
        } else {
            // Insertamos un nuevo contenido (la sentencia preparada ya escapa).
            $stmt = mysqli_prepare($db, "INSERT INTO contenidos_pccf (idCiclo, idApartado, texto) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iis", $idCiclo, $idApartado, $texto);
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
