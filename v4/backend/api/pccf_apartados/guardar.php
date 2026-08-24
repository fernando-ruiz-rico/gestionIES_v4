<?php
// API para guardar un apartado del PCCF (Fase 3.2 - Apartados PCCF)
// Inserta o actualiza el apartado recibido en la tabla apartados_pccf.

require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$data = cuerpoJson();

$id = datosOptimoInt($data, 'id');
$titulo = datosOptimo($data, 'titulo');
$subapartado = datosOptimoInt($data, 'subapartado');
$requerido = datosOptimoInt($data, 'requerido', 1);
$tipo = datosOptimoInt($data, 'tipo');
$contenidoDefecto = datosOptimoInt($data, 'contenido_defecto');

if ($titulo === '') {
    sendJSONError('El título es obligatorio', 400);
}

try {
    $db = Db::open();

    if ($id > 0) {
        // Actualización. Orden de los '?': titulo, subapartado, requerido, tipo, contenido, id
        $db->execute(
            "UPDATE apartados_pccf SET titulo = ?, subapartado = ?, requerido = ?, tipo = ?, contenido_defecto = ? WHERE id = ?",
            $titulo, $subapartado, $requerido, $tipo, $contenidoDefecto, $id);
        sendJSONSuccess(array('id' => $id), 'Apartado guardado correctamente');
    } else {
        // Inserción: calculamos el orden a partir del máximo actual.
        $fila = $db->fetchOne("SELECT MAX(orden) AS maxorden FROM apartados_pccf");
        $orden = (int)$fila['maxorden'] + 1;
        $db->execute(
            "INSERT INTO apartados_pccf (titulo, orden, subapartado, requerido, tipo, contenido_defecto) VALUES (?, ?, ?, ?, ?, ?)",
            $titulo, $orden, $subapartado, $requerido, $tipo, $contenidoDefecto);
        $idNuevo = $db->insertId();
        sendJSONSuccess(array('id' => $idNuevo), 'Apartado guardado correctamente');
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
