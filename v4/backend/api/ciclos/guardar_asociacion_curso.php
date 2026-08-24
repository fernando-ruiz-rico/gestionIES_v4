<?php
// API para asociar un curso a un ciclo o cambiar el orden de la asociación
// (Fase 1). Equivalente a v3/ajax/ciclos/insertar_curso_ciclo.php y
// v3/ajax/ciclos/actualizar_curso_ciclo.php unidos.
require_once '../../config.php';
cabeceraJson();

// Solo admin
checkPermission(array(ROLE_ADMIN));

$datos = cuerpoJson();
$idCiclo = datosOptimoInt($datos, 'idCiclo');
$idCurso = datosOptimoInt($datos, 'idCurso');
$orden   = datosOptimoInt($datos, 'orden');
if ($idCiclo <= 0 || $idCurso <= 0 || $orden <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();

    // Si la asociación ya existe actualizamos el orden; si no, la creamos
    $existe = $db->fetchOne("SELECT COUNT(*) AS total FROM cursos_ciclos WHERE idCiclo = ? AND idCurso = ?", $idCiclo, $idCurso);
    if ($existe['total'] > 0) {
        $db->execute("UPDATE cursos_ciclos SET orden = ? WHERE idCiclo = ? AND idCurso = ?", $orden, $idCiclo, $idCurso);
    } else {
        $db->execute("INSERT INTO cursos_ciclos (idCurso, idCiclo, orden) VALUES (?, ?, ?)", $idCurso, $idCiclo, $orden);
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(null, 'Asociación guardada');
?>
