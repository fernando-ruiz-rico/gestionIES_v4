<?php
/**
 * API para eliminar un tema
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idTema = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idTema <= 0) {
    sendJSONError('Parámetro inválido');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

// Eliminar relaciones con otras tablas
mysqli_query($conn, "DELETE FROM competencias_temas WHERE idTema = $idTema");
mysqli_query($conn, "DELETE FROM criterios_temas WHERE idTema = $idTema");
mysqli_query($conn, "DELETE FROM programaciones_aula_temas WHERE idTema = $idTema");

// Eliminar el tema
mysqli_query($conn, "DELETE FROM temas WHERE id = $idTema");

closeDBConnection($conn);

sendJSONSuccess(array('eliminado' => true));
?>
