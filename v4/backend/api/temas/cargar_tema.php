<?php
/**
 * API para cargar un tema
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idTema = isset($_GET['idTema']) ? intval($_GET['idTema']) : 0;

if ($idTema <= 0) {
    sendJSONError('Parámetro inválido');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

$resultado = null;

$query = "SELECT * FROM temas WHERE id=$idTema";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $resultado = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
}

closeDBConnection($conn);

sendJSONSuccess(array('tema' => $resultado));
?>
