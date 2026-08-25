<?php
// FASE 2.1 — Apartados de una materia (fiel a v3/cargar_apartados.php).
// Un apartado con tipo = 0 es EDITABLE (se rellena a mano, TinyMCE en v3);
// el resto se rellenan automáticamente y solo se editan en sus propias secciones.
require_once '../../config.php';
cabeceraJson();

// ¿La materia pertenece a un ciclo? → 'FP'; si no, 'ESO/BACH' (criterio v3).
function programacionesCategoria($db, $idMateria)
{
    $idMateria = (int)$idMateria;
    $fila = $db->fetchOne(
        "SELECT c.id FROM ciclos c
            JOIN cursos_ciclos cc ON cc.idCiclo = c.id
            JOIN cursos cu ON cu.id = cc.idCurso
            JOIN materias m ON m.idCurso = cu.id
           WHERE m.id = ?
           LIMIT 1", $idMateria);
    return $fila ? 'FP' : 'ESO/BACH';
}

// Listado de apartados de una materia (con numeración "1." / "1.1."), criterio v3.
function programacionesCargarApartados($db, $idMateria)
{
    $idMateria = (int)$idMateria;
    $categoria = programacionesCategoria($db, $idMateria);
    $filas = $db->fetchAll(
        "SELECT id, titulo, tipo, subapartado FROM apartados_programaciones
          WHERE categoria = 'TODOS' OR categoria = ?
          ORDER BY orden", $categoria);
    $apartados = array();
    $principal = 0;
    $secundario = 0;
    foreach ($filas as $fila) {
        if (!(bool)$fila['subapartado']) {
            $principal++;
            $secundario = 0;
        } else {
            $secundario++;
        }
        $apartados[] = [
            'id'        => (int)$fila['id'],
            'tipo'      => (int)$fila['tipo'],
            'nombre'    => ($fila['subapartado']
                              ? "$principal.$secundario. "
                              : "$principal. ") . $fila['titulo']
        ];
    }
    return $apartados;
}

try {
    $db = Db::open();

    $idMateria = getOptimoInt('idMateria');
    if ($idMateria <= 0) {
        throw new Exception('ID de materia inválido');
    }

    $apartados = programacionesCargarApartados($db, $idMateria);
    $db->close();
    sendJSONSuccess($apartados);
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
