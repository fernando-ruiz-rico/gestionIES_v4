<?php
/**
 * API para cargar grupos de un profesor para una materia
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$idMateria = isset($_GET['idMateria']) ? intval($_GET['idMateria']) : 0;
$idProfesor = isset($_GET['idProfesor']) ? intval($_GET['idProfesor']) : 0;

if ($idMateria <= 0 || $idProfesor <= 0) {
    sendJSONError('Parámetros inválidos');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

$resultado = array();

$query = "SELECT * FROM grupos WHERE id IN (
    SELECT seleccion.idGrupo 
    FROM seleccion, escenarios_desideratas 
    WHERE escenarios_desideratas.id = seleccion.idEscenario 
    AND escenarios_desideratas.actual = TRUE 
    AND idProfesor = $idProfesor 
    AND idMateria = $idMateria
) ORDER BY nombre";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($fila = mysqli_fetch_assoc($result)) {
        $resultado[] = array(
            'id' => $fila['id'],
            'nombre' => $fila['nombre']
        );
    }
    mysqli_free_result($result);
}

closeDBConnection($conn);

sendJSONSuccess($resultado);
?>
