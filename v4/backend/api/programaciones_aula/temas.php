<?php
// API: Listar temas (unidades) de una materia
require_once '../../config.php';
cabeceraJson();

$session = checkSession();

$idMateria = getOptimoInt('idMateria');
if ($idMateria <= 0) {
    sendJSONError('Debe indicar una materia', 400);
}

try {
    $db = Db::open();

    $filas = $db->fetchAll("SELECT id, orden, titulo FROM temas WHERE idMateria = ? ORDER BY orden", $idMateria);

    $temas = array();
    foreach ($filas as $fila) {
        $temas[] = array(
            'id'     => intval($fila['id']),
            'orden'  => intval($fila['orden']),
            'titulo' => $fila['titulo']
        );
    }

    sendJSONSuccess($temas);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
