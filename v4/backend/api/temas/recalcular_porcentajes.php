<?php
// FASE 2.6 — Recalcular porcentajes de evaluación de los RA
// (v3 calcularPorcentajesRA).
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

    $listadoRA = array();
    foreach ($db->fetchAll("SELECT ra.id, ra.orden, COUNT(ct.codigo) AS num_criterios
                    FROM resultados_aprendizaje ra
                    LEFT JOIN criterios_temas ct ON ra.id = ct.idRA
                    WHERE ra.idMateria = ?
                    GROUP BY ra.id, ra.orden
                    ORDER BY ra.orden", $idMateria) as $fila) {
        $listadoRA[] = ['id' => intval($fila['id']), 'num_criterios' => intval($fila['num_criterios'])];
    }

    if (!empty($listadoRA)) {
        $filaTotal = $db->fetchOne("SELECT COUNT(*) AS total
                    FROM resultados_aprendizaje ra
                    INNER JOIN criterios_temas ct ON ra.id = ct.idRA
                    WHERE ra.idMateria = ?", $idMateria);
        $totalCriterios = $filaTotal ? intval($filaTotal['total']) : 0;
    } else {
        $totalCriterios = 0;
    }

    $porcentajes = array();
    $suma = 0;
    foreach ($listadoRA as $item) {
        $num = $item['num_criterios'];
        $porcentaje = $totalCriterios > 0 ? intval(($num / $totalCriterios) * 100) : 0;
        $porcentajes[] = ['id' => $item['id'], 'porcentaje' => $porcentaje];
        $suma += $porcentaje;
    }

    // Si la suma no llega a 100, repartir el resto en los últimos con valor > 0
    if ($suma > 0 && $suma < 100) {
        for ($i = count($porcentajes) - 1; $i >= 0 && $suma < 100; $i--) {
            if ($porcentajes[$i]['porcentaje'] > 0) {
                $porcentajes[$i]['porcentaje']++;
                $suma++;
            }
        }
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
