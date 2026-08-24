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

$db = Db::open();

try {
    // Si se indica un apartado concreto, devuelve su texto.
    if ($idApartado > 0) {
        $fila = $db->fetchOne("SELECT texto FROM contenidos_pccf WHERE idCiclo = ? AND idApartado = ?",
            $idCiclo, $idApartado);
        $texto = $fila ? $fila['texto'] : '';
        sendJSONSuccess(['texto' => $texto]);
    } else {
        // De lo contrario, devuelve todo el contenido del ciclo agrupado por apartado.
        $filas = $db->fetchAll(
            "SELECT idApartado, texto FROM contenidos_pccf WHERE idCiclo = ? ORDER BY idApartado",
            $idCiclo);
        $contenido = [];
        foreach ($filas as $fila) {
            $contenido[] = [
                'idApartado' => $fila['idApartado'],
                'texto'      => $fila['texto']
            ];
        }
        sendJSONSuccess($contenido);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
