<?php
// Carga las preferencias horarias de un profesor, en la misma petición
// que el cargar_preferencias_profesor.php de v3: las horas salen de la
// tabla "horas" (mañana/tarde, ordenadas) y las casillas guardadas
// (R = roja, A = amarilla) salen en los mismos códigos de celda que v3
// (día + hora con '_' en vez de ':', p. ej. L07_55).
//
// Recibe idProfesor por GET (opcional): sin id, devuelve las del propio
// profesor de la sesión. Permisos como guardar.php: admin, o el propio
// profesor.
//
// Devuelve: data {
//     horasManana: ['07:55', ...], horasTarde: ['15:05', ...],
//     rojas: 'L07_55M11_55', amarillas: 'X19_10'
// }

require_once '../../config.php';
cabeceraJson();

$session = checkSession();

// De quién son: el profesor de la sesión, o el que pida un admin
// (o sí mismo, como en guardar.php)
if (!empty($_GET['idProfesor'])) {
    $idProfesor = intval($_GET['idProfesor']);
} else {
    $idProfesor = $session['idUsuario'];
}

$permisos = ($_SESSION['rol'] == ROLE_ADMIN) || ($idProfesor == $session['idUsuario']);

if (!$permisos) {
    sendJSONError('No tiene permisos para realizar esta acción', 403);
}

try {
    $db = Db::open();
    $horas = $db->fetchAll("SELECT * FROM horas ORDER BY hora");
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

$horasManana = array();
$horasTarde  = array();
foreach ($horas as $fila) {
    if ($fila['turno'] == 'M') {
        $horasManana[] = $fila['hora'];
    } else {
        $horasTarde[] = $fila['hora'];
    }
}

try {
    $prefs = $db->fetchAll(
        "SELECT dia, hora, preferencia FROM preferencias_horario WHERE idProfesor = ?",
        $idProfesor);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

$rojas     = '';
$amarillas = '';
foreach ($prefs as $fila) {
    $celda = $fila['dia'] . str_replace(':', '_', $fila['hora']);
    if ($fila['preferencia'] == 'R') {
        $rojas .= $celda;
    } else {
        $amarillas .= $celda;
    }
}

sendJSONSuccess(array(
    'horasManana' => $horasManana,
    'horasTarde'  => $horasTarde,
    'rojas'       => $rojas,
    'amarillas'   => $amarillas
));
?>
