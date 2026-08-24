<?php
// API endpoint para guardar (crear/actualizar) apartados de programaciones
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$data = cuerpoJson();

if (!isset($data['titulo']) || trim($data['titulo']) === '') {
    sendJSONError('El título es obligatorio', 400);
}

// Las categorías válidas son las mismas que en v3
$categoriasValidas = array('ESO/BACH', 'FP', 'TODOS');
$categoria = datosOptimo($data, 'categoria');
if (!in_array($categoria, $categoriasValidas)) {
    sendJSONError('Categoría no válida', 400);
}

// El texto no se escapa aquí: la sentencia preparada lo hace por sí misma
$titulo = $data['titulo'];
$subapartado = isset($data['subapartado']) && $data['subapartado'] ? 1 : 0;
$requerido = isset($data['requerido']) && $data['requerido'] ? 1 : 0;
$contenidoDefecto = isset($data['contenido_defecto']) && $data['contenido_defecto'] ? 1 : 0;
$tipo = datosOptimoInt($data, 'tipo');
if ($tipo < 0) {
    sendJSONError('Tipo no válido', 400);
}
$id = isset($data['id']) && $data['id'] > 0 ? intval($data['id']) : 0;

try {
    $db = Db::open();

    if ($id > 0) {
        // Actualizar
        $db->execute("UPDATE apartados_programaciones SET titulo=?, subapartado=?, requerido=?, contenido_defecto=?, categoria=?, tipo=? WHERE id=?", $titulo, $subapartado, $requerido, $contenidoDefecto, $categoria, $tipo, $id);
        $nuevoId = $id;
    } else {
        // Crear nuevo: se asigna el siguiente orden disponible
        $filaOrden = $db->fetchOne("SELECT COALESCE(MAX(orden), 0) + 1 AS nuevoOrden FROM apartados_programaciones");
        $nuevoOrden = intval($filaOrden['nuevoOrden']);
        $db->execute("INSERT INTO apartados_programaciones (titulo, subapartado, requerido, contenido_defecto, categoria, tipo, orden) VALUES (?, ?, ?, ?, ?, ?, ?)", $titulo, $subapartado, $requerido, $contenidoDefecto, $categoria, $tipo, $nuevoOrden);
        $nuevoId = $db->insertId();
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => (int)$nuevoId));
?>
