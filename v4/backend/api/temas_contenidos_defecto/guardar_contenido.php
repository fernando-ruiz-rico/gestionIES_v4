<?php
/**
 * API para guardar contenidos por defecto de temas
 * Backend para GestionIES v4
 */

require_once '../../config.php';

@session_start();

// Verificar autenticación y permisos
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');

if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

$idDepartamento = isset($_POST['idDepartamento']) ? intval($_POST['idDepartamento']) : 0;
$contexto = isset($_POST['contexto']) ? $_POST['contexto'] : '';
$recursos = isset($_POST['recursos']) ? $_POST['recursos'] : '';
$metodologia = isset($_POST['metodologia']) ? $_POST['metodologia'] : '';
$adaptaciones = isset($_POST['adaptaciones']) ? $_POST['adaptaciones'] : '';

if ($idDepartamento <= 0) {
    sendJSONError('Parámetro inválido');
}

$conn = getDBConnection();

if (!$conn) {
    sendJSONError('Error de conexión a la base de datos');
}

$contexto = mysqli_real_escape_string($conn, $contexto);
$recursos = mysqli_real_escape_string($conn, $recursos);
$metodologia = mysqli_real_escape_string($conn, $metodologia);
$adaptaciones = mysqli_real_escape_string($conn, $adaptaciones);

$result = mysqli_query($conn, "SELECT * FROM contenidos_defecto_temas WHERE idDepartamento=$idDepartamento");

if (mysqli_num_rows($result) > 0) {
    // Actualizar existente
    $query2 = "UPDATE contenidos_defecto_temas SET contexto='$contexto', recursos='$recursos', metodologia='$metodologia', adaptaciones='$adaptaciones' WHERE idDepartamento=$idDepartamento";
    mysqli_query($conn, $query2);
    $error = mysqli_affected_rows($conn) > 0 ? false : true;
} else {
    // Insertar nuevo
    $query2 = "INSERT INTO contenidos_defecto_temas (idDepartamento, contexto, recursos, metodologia, adaptaciones) VALUES ($idDepartamento, '$contexto', '$recursos', '$metodologia', '$adaptaciones')";
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
