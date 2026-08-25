<?php
// API para la gestión de Cualificaciones y Unidades de Competencia (Fase 4.3):
// inserta o actualiza una cualificación profesional
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
    // Fiel a v3: "idCualificacion" es el código ANTERIOR (clave de edición),
    // no la llave primaria de la tabla.
    $id = trim(datosOptimo($datos, 'id'));
    if (empty($codigo) || empty($texto)) {
        throw new Exception('Datos incompletos para guardar la cualificación');
    }

    if ($id !== '') {
        $query = "UPDATE cualificaciones_profesionales SET codigo=?, texto=? WHERE codigo=?";
        $db->execute($query, $codigo, $texto, $id);
        // Si el código ha cambiado, las unidades asociadas siguen al nuevo (v3)
        $query = "UPDATE cualificaciones_unidades SET codigoCualificacion=? WHERE codigoCualificacion=?";
        $db->execute($query, $codigo, $id);
    } else {
        $query = "INSERT INTO cualificaciones_profesionales (codigo, texto) VALUES (?, ?)";
        $db->execute($query, $codigo, $texto);
    }
    $db->close();
    sendJSONSuccess(array('codigo' => $codigo), 'Cualificación guardada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
