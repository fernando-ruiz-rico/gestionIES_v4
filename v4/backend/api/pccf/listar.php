<?php
// API para cargar el contenido de un PCCF (Fase 3.1 - PCCF)
// Devuelve el contenido asociado a un ciclo y, opcionalmente, a un apartado concreto
// desde la tabla contenidos_pccf (modelo fiel a v3).

header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$idCiclo = isset($_GET['idCiclo']) ? intval($_GET['idCiclo']) : 0;
$idApartado = isset($_GET['idApartado']) ? intval($_GET['idApartado']) : 0;

if ($idCiclo <= 0) {
    sendJSONError('Ciclo no válido', 400);
}

$db = getDBConnection();
if (!$db) {
    sendJSONError('Error de conexión a la base de datos', 500);
}

try {
    // Si se indica un apartado concreto, devuelve su texto.
    if ($idApartado > 0) {
        $stmt = mysqli_prepare($db, "SELECT texto FROM contenidos_pccf WHERE idCiclo = ? AND idApartado = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idCiclo, $idApartado);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $texto = '';
        if (mysqli_num_rows($result) > 0) {
            $fila = mysqli_fetch_assoc($result);
            $texto = $fila['texto'];
        }
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        sendJSONSuccess(['texto' => $texto]);
    } else {
        // De lo contrario, devuelve todo el contenido del ciclo agrupado por apartado.
        $sql = "SELECT idApartado, texto FROM contenidos_pccf WHERE idCiclo = ? ORDER BY idApartado";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $idCiclo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $contenido = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $contenido[] = [
                'idApartado' => $fila['idApartado'],
                'texto'      => $fila['texto']
            ];
        }
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        sendJSONSuccess($contenido);
    }
} catch (Exception $e) {
    sendJSONError($e->getMessage());
} finally {
    closeDBConnection($db);
}
?>
