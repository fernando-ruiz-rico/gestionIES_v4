<?php
// API: Guardar el texto introductorio de una programación de aula
require_once '../../config.php';
cabeceraJson();

$session = checkSession();

// En v3 el guardado solo está permitido con permisos (admin/jefe) o un profesor para sí mismo.
$rol = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

// Un superusuario (admin/jefe) puede guardar para cualquier profesor; un
// profesor solo puede guardarlo para sí mismo y debe estar activo.
if (!esUsuarioSuper($rol)) {
    if (!isset($session['activo']) || $session['activo'] != 1) {
        sendJSONError('No tiene permisos para realizar esta acción', 403);
    }
}

$data = cuerpoJson();

$idTema     = datosOptimoInt($data, 'idTema');
$idGrupo    = datosOptimoInt($data, 'idGrupo');
$texto      = datosOptimo($data, 'texto');

// Determinar idProfesor según rol
if (esUsuarioSuper($rol)) {
    $idProfesor = datosOptimoInt($data, 'idProfesor', $idUsuarioSesion);
} else {
    $idProfesor = $idUsuarioSesion;
}

if ($idGrupo <= 0 || $idProfesor <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();

    $texto = trim($texto);

    // Verificar si ya existe una fila para este triplete (tema + grupo + profesor)
    $filaCheck = $db->fetchOne("SELECT id FROM programaciones_aula_temas WHERE idTema = ? AND idGrupo = ? AND idProfesor = ?", $idTema, $idGrupo, $idProfesor);
    $existe = ($filaCheck !== null);

    if ($texto === '') {
        // Borrar el contenido si está vacío (mismo comportamiento que v3: insertar_contenido_programacion.php)
        if ($existe) {
            $db->execute("DELETE FROM programaciones_aula_temas WHERE idTema = ? AND idGrupo = ? AND idProfesor = ?", $idTema, $idGrupo, $idProfesor);
            sendJSONSuccess(null, 'Contenido eliminado');
        } else {
            // No había nada que borrar
            sendJSONSuccess(null, 'No hay contenido para eliminar');
        }
    } else {
        if ($existe) {
            $db->execute("UPDATE programaciones_aula_temas SET texto = ? WHERE idTema = ? AND idGrupo = ? AND idProfesor = ?", $texto, $idTema, $idGrupo, $idProfesor);
        } else {
            $db->execute("INSERT INTO programaciones_aula_temas (idTema, idGrupo, idProfesor, texto) VALUES (?, ?, ?, ?)", $idTema, $idGrupo, $idProfesor, $texto);
        }
        sendJSONSuccess(null, 'Contenido guardado correctamente');
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
