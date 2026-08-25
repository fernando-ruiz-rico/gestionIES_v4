<?php
// API de selección (Desideratas): elegir una materia para el profesor
// actual (v3/insertar_seleccion.php)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

$datos = cuerpoJson();
if ($datos === null) {
    sendJSONError('Faltan datos', 400);
}
$idProfesor = datosOptimoInt($datos, 'idProfesor');
$idMateria = datosOptimoInt($datos, 'idMateria');
$idGrupo = datosOptimoInt($datos, 'idGrupo');
$horas = datosOptimoInt($datos, 'horas');
$idEscenario = getOptimoInt('idEscenario');
if ($idProfesor <= 0 || $idMateria <= 0 || $idGrupo <= 0 || $idEscenario <= 0 || $horas <= 0) {
    sendJSONError('Faltan parámetros', 400);
}
try {
    $db = Db::open();
    // v3: si la materia la asigna la directiva se le da un orden inferior (100),
    // de modo que queda por detrás de las que elige el profesor
    $asignada = $db->fetchOne("SELECT asignada_directiva FROM materias WHERE id = ?", $idMateria);
    if ($asignada) {
        $total = $db->fetchOne("SELECT COUNT(*) AS total FROM seleccion
                                  WHERE idProfesor = ? AND idEscenario = ?", $idProfesor, $idEscenario);
        $orden = $asignada['asignada_directiva'] ? 100 : $total['total'] + 1;
    } else {
        $orden = $db->fetchOne("SELECT COALESCE(MAX(orden), 0) + 1 AS nuevo
                                  FROM seleccion
                                  WHERE idProfesor = ? AND idEscenario = ?", $idProfesor, $idEscenario)['nuevo'];
    }
    $db->execute("INSERT INTO seleccion (idProfesor, idMateria, idGrupo, horas, orden, idEscenario)
                  VALUES (?, ?, ?, ?, ?, ?)", $idProfesor, $idMateria, $idGrupo, $horas, $orden, $idEscenario);
    $db->close();
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
sendJSONSuccess(null, 'Seleccionada');
