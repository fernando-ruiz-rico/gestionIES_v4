<?php
/**
 * API para guardar contenido de programación de aula
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idTema = isset($_POST['idTema']) ? intval($_POST['idTema']) : 0;
$idGrupo = isset($_POST['idGrupo']) ? intval($_POST['idGrupo']) : 0;
$idProfesor = isset($_POST['idProfesor']) ? intval($_POST['idProfesor']) : 0;
$texto = isset($_POST['texto']) ? $_POST['texto'] : '';

if ($idTema <= 0 || $idGrupo <= 0 || $idProfesor <= 0) {
    sendJSONError('Parámetros inválidos');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

$error = true;

$query = "SELECT * FROM programaciones_aula_temas WHERE idTema=$idTema AND idGrupo=$idGrupo AND idProfesor=$idProfesor";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    // Actualizar existente
    $texto_escaped = mysqli_real_escape_string($conn, $texto);
    $query2 = "UPDATE programaciones_aula_temas SET texto='$texto_escaped' WHERE idTema=$idTema AND idGrupo=$idGrupo AND idProfesor=$idProfesor";
    mysqli_query($conn, $query2);
    $error = mysqli_affected_rows($conn) > 0 ? false : true;
} else {
    // Insertar nuevo
    $texto_escaped = mysqli_real_escape_string($conn, $texto);
    $query2 = "INSERT INTO programaciones_aula_temas (idTema, idGrupo, idProfesor, texto) VALUES ($idTema, $idGrupo, $idProfesor, '$texto_escaped')";
    mysqli_query($conn, $query2);
    $error = mysqli_affected_rows($conn) > 0 ? false : true;
}

mysqli_free_result($result);
closeDBConnection($conn);

if ($error) {
    sendJSONSuccess(array('error' => true));
} else {
    sendJSONSuccess(array('error' => false));
}
?>
