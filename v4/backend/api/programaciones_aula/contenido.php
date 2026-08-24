<?php
// API: Cargar el texto introductorio de una programación de aula (tema + grupo + profesor)
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

$idTema     = getOptimoInt('idTema');
$idGrupo    = getOptimoInt('idGrupo');
$rol        = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

// Admin puede ver contenido de cualquier profesor
if (esUsuarioSuper($rol)) {
    $idProfesor = getOptimoInt('idProfesor', $idUsuarioSesion);
} else {
    $idProfesor = $idUsuarioSesion;
}

if ($idGrupo <= 0 || $idProfesor <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();

    $fila = $db->fetchOne("SELECT texto FROM programaciones_aula_temas WHERE idTema = ? AND idGrupo = ? AND idProfesor = ?", $idTema, $idGrupo, $idProfesor);
    $texto = ($fila !== null) ? $fila['texto'] : '';

    sendJSONSuccess(array('texto' => $texto));
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
