<?php
// FASE 2.6 — Recalcular porcentajes de evaluación de los RA.
//
// Cálculo por peso de unidad: el % que se aplica a cada RA en la evaluación
// anual se calcula a partir del % que cada unidad (tema) tiene como peso en
// la evaluación anual (temas.peso_evaluacion). El peso de cada unidad se
// reparte entre los RA que intervienen en ella, en proporción a los
// criterios de evaluación (CE) de cada RA en esa unidad (criterios_temas).
// El % final de cada RA es la suma de su parte en cada unidad en la que
// interviene, así un RA que influye en más unidades, y con más CE en cada
// una, se lleva una parte mayor de la nota de la asignatura.
require_once '../../config.php';
cabeceraJson();

try {
    $db = Db::open();
    checkSession();

    $body = cuerpoJson();

    $idMateria = datosOptimoInt($body, 'idMateria');
    if ($idMateria <= 0) {
        throw new Exception('Debe indicar una materia');
    }

    // Peso de cada unidad en la evaluación anual
    $pesos = array();
    foreach ($db->fetchAll("SELECT id, peso_evaluacion FROM temas WHERE idMateria = ?", $idMateria) as $fila) {
        $pesos[intval($fila['id'])] = intval($fila['peso_evaluacion']);
    }

    // CE por (RA, unidad): cuántos criterios de evaluación lleva cada RA en
    // cada unidad, y el total de CE por unidad (todos los RA).
    $ceRA = array();      // ceRA[ra][tema] = nº de CE del RA en la unidad
    $ceTotal = array();   // ceTotal[tema] = nº total de CE de la unidad
    foreach ($db->fetchAll(
        "SELECT ct.idRA, ct.idTema, COUNT(*) AS n
           FROM criterios_temas ct
           INNER JOIN temas t ON t.id = ct.idTema
          WHERE t.idMateria = ?
          GROUP BY ct.idRA, ct.idTema", $idMateria) as $fila) {
        $ra   = intval($fila['idRA']);
        $tema = intval($fila['idTema']);
        $n    = intval($fila['n']);
        if (!isset($ceRA[$ra])) {
            $ceRA[$ra] = array();
        }
        $ceRA[$ra][$tema] = $n;
        $ceTotal[$tema] = isset($ceTotal[$tema]) ? $ceTotal[$tema] + $n : $n;
    }

    // % final de cada RA = Σ en cada unidad: peso × (CEs del RA / CE totales)
    $porcentajes = array();
    foreach ($db->fetchAll("SELECT id FROM resultados_aprendizaje WHERE idMateria = ? ORDER BY orden", $idMateria) as $fila) {
        $raId = intval($fila['id']);
        $total = 0;
        if (isset($ceRA[$raId])) {
            foreach ($pesos as $tema => $peso) {
                $nTotal = isset($ceTotal[$tema]) ? $ceTotal[$tema] : 0;
                if ($nTotal <= 0) {
                    continue;
                }
                $nRA = isset($ceRA[$raId][$tema]) ? $ceRA[$raId][$tema] : 0;
                if ($nRA <= 0) {
                    continue;
                }
                $total += $peso * ($nRA / $nTotal);
            }
        }
        $porcentajes[] = ['id' => $raId, 'porcentaje' => intval(round($total))];
    }

    foreach ($porcentajes as $p) {
        $db->execute("UPDATE resultados_aprendizaje SET porcentaje_evaluacion = ? WHERE id = ?",
            $p['porcentaje'], $p['id']);
    }

    $db->close();
    sendJSONSuccess(['ra' => $porcentajes], 'Porcentajes recalculados');
} catch (DbException $e) {
    $db->close();
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $db->close();
    sendJSONError($e->getMessage(), 400);
}
