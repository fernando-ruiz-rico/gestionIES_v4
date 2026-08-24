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
    sendJSONError('Parámetros no válidos', 400);
}

$db = Db::open();

try {
    $texto = trim($texto);

    // Si no hay texto, eliminamos el contenido por defecto
    if ($texto === '') {
        $db->execute("DELETE FROM contenidos_defecto_programaciones WHERE idApartado = ? AND idDepartamento = ?", $idApartado, $idDepartamento);
        $db->close();
        sendJSONSuccess(null, 'Contenido eliminado correctamente');
    } else {
        // Verificar si ya existe
        $n = $db->count("SELECT id FROM contenidos_defecto_programaciones WHERE idApartado = ? AND idDepartamento = ?", $idApartado, $idDepartamento);

        if ($n > 0) {
            // Actualizar
            $db->execute("UPDATE contenidos_defecto_programaciones SET texto = ? WHERE idApartado = ? AND idDepartamento = ?", $texto, $idApartado, $idDepartamento);
        } else {
            // Insertar
            $db->execute("INSERT INTO contenidos_defecto_programaciones (idApartado, idDepartamento, texto) VALUES (?, ?, ?)", $idApartado, $idDepartamento, $texto);
        }

        $db->close();
        sendJSONSuccess(null, 'Contenido guardado correctamente');
    }
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
