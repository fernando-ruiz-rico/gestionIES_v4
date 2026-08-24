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

$db = Db::open();

try {
    $texto = trim($texto);

    // Con texto vacío, eliminamos la fila (igual que en las fases 2.3/2.4).
    if ($texto === '') {
        $afectadas = $db->execute("DELETE FROM contenidos_pccf WHERE idCiclo = ? AND idApartado = ?",
            $idCiclo, $idApartado);
        if ($afectadas <= 0) {
            sendJSONError('No existe contenido que eliminar', 400);
        }
        sendJSONSuccess(null, 'Contenido eliminado correctamente');
    } else {
        // Comprobamos si ya existe contenido para ese ciclo y apartado.
        $existe = $db->count("SELECT id FROM contenidos_pccf WHERE idCiclo = ? AND idApartado = ?",
            $idCiclo, $idApartado) > 0;

        if ($existe) {
            // Actualizamos el contenido existente (la sentencia preparada ya escapa).
            $db->execute("UPDATE contenidos_pccf SET texto = ? WHERE idCiclo = ? AND idApartado = ?",
                $texto, $idCiclo, $idApartado);
        } else {
            // Insertamos un nuevo contenido (la sentencia preparada ya escapa).
            $db->execute("INSERT INTO contenidos_pccf (idCiclo, idApartado, texto) VALUES (?, ?, ?)",
                $idCiclo, $idApartado, $texto);
        }

        sendJSONSuccess(null, 'Datos guardados correctamente');
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
