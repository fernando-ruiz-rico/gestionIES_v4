<?php
// API de Resultados de Aprendizaje (Fase 4.1):
// inserta o actualiza un resultado de aprendizaje
require_once '../../config.php';
require_once '../../lib/resultados_aprendizaje.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    if (!raTienePermisoEdicion()) {
        throw new Exception('No tiene permisos para realizar esta acción');
    }

    $id = datosOptimoInt($datos, 'id');
    $idMateria = intval($datos['idMateria']);
    $texto = $datos['texto'];
    $orden = intval($datos['orden']);
    $porcentajeEmpresa = datosOptimoInt($datos, 'porcentaje_empresa');
    if ($idMateria <= 0 || empty($texto)) {
        throw new Exception('Datos incompletos para guardar el resultado');
    }

    $idDepartamento = ($id > 0) ? raIdDepartamentoDeRA($db, $id) : raIdDepartamentoDeMateria($db, $idMateria);
    raComprobarDepartamento($idDepartamento);

    if ($id > 0) {
        $db->execute(
            "UPDATE resultados_aprendizaje SET texto = ?, orden = ?, porcentaje_empresa = ? WHERE id = ?",
            $texto, $orden, $porcentajeEmpresa, $id);
        $nuevoId = $id;
    } else {
        $db->execute(
            "INSERT INTO resultados_aprendizaje (idMateria, texto, orden, porcentaje_empresa) VALUES (?, ?, ?, ?)",
            $idMateria, $texto, $orden, $porcentajeEmpresa);
        $nuevoId = $db->insertId();
    }

    $db->close();
    sendJSONSuccess(array('id' => $nuevoId), 'Resultado de aprendizaje guardado');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage());
}
