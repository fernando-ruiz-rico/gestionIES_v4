<?php
// API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3):
// inserta o actualiza una unidad de competencia
// Fiel a v3: las cualificaciones profesionales (cualificaciones_profesionales)
// pueden asociar unidades de competencia (unidades_competencia) a través de
// la tabla cualificaciones_unidades.
// Permisos: solo el rol admin.
require_once '../../config.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    // Permiso: solo admin
    checkPermission(array(ROLE_ADMIN));

    $codigo = $datos['codigo'];
    $texto = $datos['texto'];
    // Fiel a v3: "idUnidad" es el código ANTERIOR (clave de edición).
    $id = trim(datosOptimo($datos, 'id'));
    if (empty($codigo) || empty($texto)) {
        throw new Exception('Datos incompletos para guardar la unidad');
    }

    if ($id !== '') {
        $query = "UPDATE unidades_competencia SET codigo=?, texto=? WHERE codigo=?";
        $db->execute($query, $codigo, $texto, $id);
        // Si el código ha cambiado, las asociaciones siguen al nuevo (v3)
        $query = "UPDATE unidades_ciclos SET codigoUnidad=? WHERE codigoUnidad=?";
        $db->execute($query, $codigo, $id);
    } else {
        $query = "INSERT INTO unidades_competencia (codigo, texto) VALUES (?, ?)";
        $db->execute($query, $codigo, $texto);
    }
    $db->close();
    sendJSONSuccess(array('codigo' => $codigo), 'Unidad de competencia guardada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
