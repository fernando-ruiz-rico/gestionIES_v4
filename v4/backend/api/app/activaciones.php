<?php
// Estado de las activaciones de la app (Desideratas / Programaciones)
require_once '../../config.php';
cabeceraJson();

@session_start();

// Verificar autenticación
if (empty($_SESSION['idUsuario'])) {
    sendJSONError('No hay sesión activa', 401);
}

$activaciones = array(
    'desideratas' => false,
    'programaciones' => false
);

// Estado de las activaciones, igual que includes/comprobar_activaciones.php de v3:
// la tabla real es 'config' con columnas 'clave' y 'valor' ('ON' / 'OFF').
try {
    $db = Db::open();
    $filas = $db->fetchAll("SELECT clave, valor FROM config WHERE clave IN ('desideratas', 'programaciones')");
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

foreach ($filas as $row) {
    if ($row['clave'] == 'desideratas') {
        $activaciones['desideratas'] = ($row['valor'] == 'ON');
    }
    if ($row['clave'] == 'programaciones') {
        $activaciones['programaciones'] = ($row['valor'] == 'ON');
    }
}

sendJSONSuccess($activaciones);
