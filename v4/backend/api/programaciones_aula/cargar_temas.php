<?php
/**
 * API para cargar temas de una materia
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;

if ($idMateria <= 0) {
    sendJSONError('Parámetro inválido');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

$resultado = array();

$query = "SELECT * FROM temas WHERE idMateria = $idMateria ORDER BY orden";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($fila = mysqli_fetch_assoc($result)) {
        $resultado[] = array(
            'id' => $fila['id'],
            'orden' => $fila['orden'],
            'titulo' => $fila['titulo']
        );
    }
    mysqli_free_result($result);
}

closeDBConnection($conn);

sendJSONSuccess($resultado);
?>
