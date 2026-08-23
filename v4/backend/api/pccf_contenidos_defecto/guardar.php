<?php
// API para guardar el contenido por defecto de un apartado del PCCF (Fase 3.3)
// Inserta o actualiza el contenido del apartado para el departamento indicado.
// Con texto vacío se elimina la fila (fiel a v3).

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

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
    // Fiel a v3: sólo se editan aquí los apartados que admiten contenido por
    // defecto y son editables (tipo == 0). Los de otro tipo se rellenan
    // automáticamente a partir de la base de datos y no se guardan, así que
    // rechazamos cualquier apartado no editable aunque llegue directamente.
    $stmtApto = mysqli_prepare($db, "SELECT tipo FROM apartados_pccf WHERE id = ?");
    mysqli_stmt_bind_param($stmtApto, "i", $idApartado);
    mysqli_stmt_execute($stmtApto);
    $resApto = mysqli_stmt_get_result($stmtApto);
    $filaApto = mysqli_fetch_assoc($resApto);
    mysqli_free_result($resApto);
    mysqli_stmt_close($stmtApto);
    if (!$filaApto || intval($filaApto['tipo']) != 0) {
        sendJSONError('El apartado seleccionado no es editable: se rellena automáticamente a partir de la base de datos', 400);
    }

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
            // Actualizamos (la sentencia preparada ya escapa el texto).
            $stmt = mysqli_prepare($db, "UPDATE contenidos_defecto_pccf SET texto = ? WHERE idApartado = ? AND idDepartamento = ?");
            mysqli_stmt_bind_param($stmt, "sii", $texto, $idApartado, $idDepartamento);
        } else {
            // Insertamos (la sentencia preparada ya escapa el texto).
            $stmt = mysqli_prepare($db, "INSERT INTO contenidos_defecto_pccf (idDepartamento, idApartado, texto) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iis", $idDepartamento, $idApartado, $texto);
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
