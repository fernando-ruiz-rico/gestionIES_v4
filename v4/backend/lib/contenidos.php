<?php
// Utilidad compartida para guardar —o eliminar, si el texto llega vacío— una
// única fila de "texto" de las tablas de contenido (PCCF, contenidos por
// defecto del PCCF y contenidos por defecto de programaciones).
//
// Las tres llamadas (pccf/guardar.php, pccf_contenidos_defecto/guardar.php y
// programaciones_contenidos_defecto/guardar.php) comparten exactamente este
// comportamiento, así que la lógica vive aquí y no se duplica.
//
// $claves son los pares [columna, valor] que identifican la fila, p. ej.
// [['idCiclo', 1], ['idApartado', 2]]; en el INSERT entran en ese orden.
//
// $avisaSinFila: si es true y la eliminación no afecta a ninguna fila, se
// avisa con un 400 (así lo hacían pccf y pccf_contenidos_defecto); si es
// false se elimina en silencio (programaciones_contenidos_defecto).

function contenidos_guardarTexto(Db $db, $tabla, array $claves, $texto, $msjEliminar, $msjGuardar, $avisaSinFila = true)
{
    $valores = array_map(function ($c) { return $c[1]; }, $claves);
    $cols    = implode(', ', array_map(function ($c) { return $c[0]; }, $claves));
    $donde   = implode(' AND ', array_map(function ($c) { return $c[0] . ' = ?'; }, $claves));

    $texto = trim($texto);

    // Sin texto: eliminamos la fila (igual que en las fases 2.3/2.4)
    if ($texto === '') {
        $n = $db->execute("DELETE FROM $tabla WHERE $donde", ...$valores);
        if ($avisaSinFila && $n <= 0) {
            sendJSONError('No existe contenido que eliminar', 400);
        }
        $db->close();
        sendJSONSuccess(null, $msjEliminar);
        return;
    }

    // Con texto: actualizamos si ya existe, o insertamos si no
    $fila = $db->fetchOne("SELECT id FROM $tabla WHERE $donde", ...$valores);
    if ($fila) {
        $db->execute("UPDATE $tabla SET texto = ? WHERE $donde", ...array_merge(array($texto), $valores));
    } else {
        $marcadores = implode(', ', array_fill(0, count($valores) + 1, '?'));
        $db->execute("INSERT INTO $tabla ($cols, texto) VALUES ($marcadores)", ...array_merge($valores, array($texto)));
    }

    $db->close();
    sendJSONSuccess(null, $msjGuardar);
}
