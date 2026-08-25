<?php
// FASE 2.7 — Insertar o actualizar la fila de contenidos por defecto
// del departamento (contexto, recursos, metodología, adaptaciones).
// Fila por departamento (contenidos_defecto_temas.idDepartamento = PK):
// se inserta si no existe, se actualiza si existe.
// Equivalente a v3: temas_contenidos_defecto.php + ajax/temas_contenidos_defecto/.
require_once '../../config.php';
cabeceraJson();

$session = checkSession();
$permisos = in_array($session['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));
if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

try {
    $db = Db::open();

    $body = cuerpoJson();

    $idDepartamento = datosOptimoInt($body, 'idDepartamento');
    $contexto = datosOptimo($body, 'contexto');
    $recursos = datosOptimo($body, 'recursos');
    $metodologia = datosOptimo($body, 'metodologia');
    $adaptaciones = datosOptimo($body, 'adaptaciones');

    // Un jefe de departamento solo puede editar su propio departamento
    if ($session['rol'] === ROLE_JEFE_DEPARTAMENTO && intval($session['idDepartamento']) !== $idDepartamento) {
        sendJSONError('Solo puede editar el contenido de su propio departamento', 403);
    }
    if ($idDepartamento <= 0) {
        sendJSONError('Debe indicar un departamento', 400);
    }

    // Comprobar si ya existe la fila del departamento
    $existe = $db->count("SELECT idDepartamento FROM contenidos_defecto_temas WHERE idDepartamento = ?", $idDepartamento) > 0;

    if ($existe) {
        $db->execute("UPDATE contenidos_defecto_temas SET contexto = ?, recursos = ?, metodologia = ?, adaptaciones = ? WHERE idDepartamento = ?", $contexto, $recursos, $metodologia, $adaptaciones, $idDepartamento);
    } else {
        $db->execute("INSERT INTO contenidos_defecto_temas (idDepartamento, contexto, recursos, metodologia, adaptaciones) VALUES (?, ?, ?, ?, ?)", $idDepartamento, $contexto, $recursos, $metodologia, $adaptaciones);
    }

    $db->close();
    sendJSONSuccess(null, 'Contenidos guardados correctamente');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
