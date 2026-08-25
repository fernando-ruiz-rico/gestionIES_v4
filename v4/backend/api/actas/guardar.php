<?php
// API para la gestión de Actas de departamentos (Fase 6.1):
// inserta o actualiza un acta
// Fiel a v3: las actas se almacenan en actas_departamentos, una fila por acta.
//
// Permisos:
//   - Los admins pueden trabajar sobre cualquier departamento.
//   - Los jefes de departamento solo sobre su propio departamento.
require_once '../../config.php';
cabeceraJson();
@session_start();

$datos = cuerpoJson();

try {
    $db = Db::open();

    // Permiso de edición: solo admin o jefe de departamento
    checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

    $idActa = datosOptimoInt($datos, 'idActa');
    $idDepartamento = intval($datos['idDepartamento']);
    $texto = $datos['texto'];
    $fecha = $datos['fecha'];
    if ($idDepartamento <= 0 || empty($texto) || empty($fecha)) {
        throw new Exception('Datos incompletos para guardar el acta');
    }
    if (!puedeEditarDepartamento($idDepartamento)) {
        throw new Exception('No tiene permisos sobre este departamento');
    }

    // La fecha llega en formato d/m/Y (o, en algunos casos, ya Y-m-d);
    // la normalizamos a Y-m-d de forma tolerante.
    $fechaForm = $fecha;
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $ts = strtotime($fecha);
    } elseif (($dt = DateTime::createFromFormat('d/m/Y', $fecha)) !== false) {
        $ts = $dt->getTimestamp();
    } else {
        $ts = strtotime(str_replace('/', '-', $fecha));
    }
    if ($ts === false || $ts === -1) {
        throw new Exception('Fecha no válida: ' . $fecha);
    }
    $fechaForm = date('Y-m-d', $ts);

    if ($idActa > 0) {
        $db->execute("UPDATE actas_departamentos SET texto=?, fecha=? WHERE id=?", $texto, $fechaForm, $idActa);
    } else {
        $db->execute("INSERT INTO actas_departamentos (idDepartamento, texto, fecha) VALUES (?, ?, ?)", $idDepartamento, $texto, $fechaForm);
    }
    $nuevoId = ($idActa > 0) ? $idActa : $db->insertId();
    sendJSONSuccess(array('id' => $nuevoId), 'Acta guardada');
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}

// El usuario puede editar solo si es admin, o jefe de su propio departamento
function puedeEditarDepartamento($idDepartamento)
{
    if ($_SESSION['rol'] == ROLE_ADMIN) {
        return true;
    }
    if (isset($_SESSION['departamentoUsuario'])) {
        return intval($_SESSION['departamentoUsuario']) == intval($idDepartamento);
    }
    return false;
}
