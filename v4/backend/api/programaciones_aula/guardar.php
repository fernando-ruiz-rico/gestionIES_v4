<?php
// API: Guardar el texto introductorio de una programación de aula
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

$session = checkSession();

// En v3 el guardado solo está permitido con permisos (admin/jefe) o un profesor para sí mismo.
$rol = $session['rol'];
$idUsuarioSesion = intval($session['idUsuario']);

if (esUsuarioSuper($rol)) {
    // Admin puede guardar para cualquier profesor
} else {
    // Un profesor solo puede guardar el contenido de sí mismo
    if (isset($session['activo']) && $session['activo'] == 1) {
        // Ok, continuar
    } else {
        sendJSONError('No tiene permisos para realizar esta acción', 403);
    }
}

$data = json_decode(file_get_contents('php://input'), true);

$idTema     = isset($data['idTema']) ? intval($data['idTema']) : 0;
$idGrupo    = isset($data['idGrupo']) ? intval($data['idGrupo']) : 0;
$texto      = isset($data['texto']) ? $data['texto'] : '';

// Determinar idProfesor según rol
if (esUsuarioSuper($rol)) {
    $idProfesor = isset($data['idProfesor']) ? intval($data['idProfesor']) : $idUsuarioSesion;
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
