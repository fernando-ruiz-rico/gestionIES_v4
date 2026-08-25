<?php
// API para la gestión de Competencias por Ciclo (Fase 4.2):
// inserta o actualiza una competencia de un ciclo
// Fiel a v3: las competencias se almacenan en competencias_ciclos, una fila
// por competencia (con su código, texto, tipo e id de ciclo).
// Permisos: solo el rol admin.
require_once '../../config.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    // Permiso: solo admin
    checkPermission(array(ROLE_ADMIN));

    $id = datosOptimoInt($datos, 'id');
    $codigo = $datos['codigo'];
    $texto = $datos['texto'];
    $tipo = datosOptimoInt($datos, 'tipo', 1);
    $idCiclo = intval($datos['idCiclo']);

    if ($id > 0) {
        $db->execute("UPDATE competencias_ciclos SET codigo=?, texto=?, tipo=? WHERE id=?", $codigo, $texto, $tipo, $id);
    } else {
        if (empty($codigo) || empty($texto) || $idCiclo <= 0) {
            throw new Exception('Datos incompletos para guardar la competencia');
        }
        // "orden" es NOT NULL sin valor por defecto; v3 no la pedía, así que la ponemos al final de la lista
        $filaMax = $db->fetchOne("SELECT MAX(orden) AS maxo FROM competencias_ciclos WHERE idCiclo = ?", $idCiclo);
        $orden = ($filaMax && $filaMax['maxo'] !== null) ? intval($filaMax['maxo']) + 1 : 1;
        $db->execute("INSERT INTO competencias_ciclos (codigo, texto, tipo, idCiclo, orden) VALUES (?, ?, ?, ?, ?)", $codigo, $texto, $tipo, $idCiclo, $orden);
    }

    $nuevoId = ($id > 0) ? $id : $db->insertId();
    sendJSONSuccess(array('id' => $nuevoId), 'Competencia guardada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
