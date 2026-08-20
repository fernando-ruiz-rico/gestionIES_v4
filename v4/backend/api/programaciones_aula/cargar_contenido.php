<?php
/**
 * API para cargar contenido de programación de aula
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idTema = isset($_GET['idTema']) ? intval($_GET['idTema']) : 0;
$idGrupo = isset($_GET['idGrupo']) ? intval($_GET['idGrupo']) : 0;
$idProfesor = isset($_GET['idProfesor']) ? intval($_GET['idProfesor']) : 0;

if ($idTema <= 0 || $idGrupo <= 0 || $idProfesor <= 0) {
    sendJSONError('Parámetros inválidos');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

$resultado = "";

$query = "SELECT texto FROM programaciones_aula_temas WHERE idTema=$idTema AND idGrupo=$idGrupo AND idProfesor=$idProfesor";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $fila = mysqli_fetch_assoc($result);
    $resultado = $fila['texto'];
    mysqli_free_result($result);
}

closeDBConnection($conn);

sendJSONSuccess(array('texto' => $resultado));
?>
