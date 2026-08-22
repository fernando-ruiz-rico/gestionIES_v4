<?php
// API para guardar un apartado del PCCF (Fase 3.2 - Apartados PCCF)
// Inserta o actualiza el apartado recibido en la tabla apartados_pccf.

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$data = json_decode(file_get_contents('php://input'), true);
$data = $data ?: [];

$id = isset($data['id']) ? intval($data['id']) : 0;
$titulo = isset($data['titulo']) ? $data['titulo'] : '';
$subapartado = isset($data['subapartado']) ? (int)$data['subapartado'] : 0;
$requerido = isset($data['requerido']) ? (int)$data['requerido'] : 1;
$tipo = isset($data['tipo']) ? (int)$data['tipo'] : 0;
$contenidoDefecto = isset($data['contenido_defecto']) ? (int)$data['contenido_defecto'] : 0;

if ($titulo === '') {
    sendJSONError('El título es obligatorio', 400);
}

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    // Tipo: s (titulo) + i x 5. La sentencia preparada ya escapa el texto.
    $tipoStr = 'siiiii';

    if ($id > 0) {
        // Actualización. Orden de los '?': titulo, subapartado, requerido, tipo, contenido, id
        $stmt = mysqli_prepare($db,
            "UPDATE apartados_pccf SET titulo = ?, subapartado = ?, requerido = ?, tipo = ?, contenido_defecto = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, $tipoStr, $titulo, $subapartado, $requerido, $tipo, $contenidoDefecto, $id);
    } else {
        // Inserción: calculamos el orden a partir del máximo actual.
        $orden = 1;
        $res = mysqli_query($db, "SELECT MAX(orden) AS maxorden FROM apartados_pccf");
        if ($res) {
            $orden = (int)mysqli_fetch_assoc($res)['maxorden'] + 1;
            mysqli_free_result($res);
        }
        $stmt = mysqli_prepare($db,
            "INSERT INTO apartados_pccf (titulo, orden, subapartado, requerido, tipo, contenido_defecto) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, $tipoStr, $titulo, $orden, $subapartado, $requerido, $tipo, $contenidoDefecto);
    }

    if (!mysqli_stmt_execute($stmt)) {
        sendJSONError('Error al guardar: ' . mysqli_error($db));
    }
    mysqli_stmt_close($stmt);
    sendJSONSuccess(null, 'Apartado guardado correctamente');
} catch (Exception $e) {
    sendJSONError($e->getMessage());
} finally {
    closeDBConnection($db);
}
?>
