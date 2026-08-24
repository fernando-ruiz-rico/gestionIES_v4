<?php
// ============================================================================
// FASE 2.7 — Contenidos por defecto de temas / unidades de programación
// Equivalente a v3: tems_contenidos_defecto.php + ajax/temas_contenidos_defecto/
//   - cargar   : carga los contenidos por defecto de un departamento
//                (contexto, recursos, metodología, acciones)
//   - guardar  : inserta o actualiza la fila del departamento (rol admin o
//                jefe de departamento; este último solo para su propio depto)
// Modelo fiel a v3: no hay borrado por campo. La fila es por departamento
// (contenidos_defecto_temas.idDepartamento = PK). Se inserta si no existe la
// fila, se actualiza si existe.
// Compatible con PHP 5 (capa Db / sentencias preparadas).
// ============================================================================
require_once '../config.php';
cabeceraJson();

$session = checkSession();
$permisos = in_array($session['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));
if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$body = array();
if ($method === 'POST') {
    $body = cuerpoJson();
}

$action = getOptimo('action');
if ($action === '' && isset($body['action'])) {
    $action = $body['action'];
}

try {
    $db = Db::open();

    // ---------------------------------------------------------------------------
    // Acción: cargar (GET ?idDepartamento=N)
    // ---------------------------------------------------------------------------
    if ($action === 'cargar') {
        $idDepartamento = getOptimoInt('idDepartamento');
        if ($idDepartamento <= 0) {
            sendJSONError('Debe indicar un departamento', 400);
        }

        $fila = $db->fetchOne("SELECT contexto, recursos, metodologia, adaptaciones
                FROM contenidos_defecto_temas WHERE idDepartamento = ?", $idDepartamento);

        sendJSONSuccess(array(
            'contexto' => $fila ? $fila['contexto'] : '',
            'recursos' => $fila ? $fila['recursos'] : '',
            'metodologia' => $fila ? $fila['metodologia'] : '',
            'adaptaciones' => $fila ? $fila['adaptaciones'] : ''
        ));
    }

    // ---------------------------------------------------------------------------
    // Acción: guardar (POST {idDepartamento, contexto, recursos, metodologia, acciones})
    // ---------------------------------------------------------------------------
    if ($action === 'guardar') {
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

        sendJSONSuccess(null, 'Contenidos guardados correctamente');
    }

    sendJSONError('Acción no válida', 400);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
