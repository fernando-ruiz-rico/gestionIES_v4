<?php
function generarTablasResumenCriteriosEvaluacion($idMateria, $idCiclo)
{
    // 1. Obtener temas de la materia
    $sqlTemas = "
        SELECT id, orden, titulo, peso_evaluacion, horas, trimestre
        FROM temas
        WHERE idMateria = {$idMateria}
        ORDER BY orden";
    $temas = consultarBaseDeDatos($sqlTemas);

    if (empty($temas)) {
        return '<p>No hay temas definidos para esta materia.</p>';
    }

    // 2. Obtener resultados de aprendizaje
    $sqlRAs = "SELECT id, orden, es_clave, texto, porcentaje_evaluacion FROM resultados_aprendizaje WHERE idMateria = {$idMateria}";
    $resultadosAprendizaje = consultarBaseDeDatos($sqlRAs);
    if (empty($resultadosAprendizaje)) {
        return '<p>No hay resultados de aprendizaje definidos.</p>';
    }

    // Crear un mapa de RA por ID para acceso rápido
    $mapRA = array();
    foreach ($resultadosAprendizaje as $ra) {
        $mapRA[(int)$ra['id']] = $ra;
    }

    // 3. Obtener relaciones criterios_temas
    $idsTemas = array();
    foreach ($temas as $t) {
        $idsTemas[] = (int)$t['id'];
    }
    $listaIdsTemas = implode(',', $idsTemas);

    $sqlCriteriosTemas = "
        SELECT ct.idTema, ct.idRA, ct.codigo, ce.texto
        FROM criterios_temas ct
        INNER JOIN criterios_evaluacion ce 
            ON ct.idRA = ce.idRA 
            AND ct.codigo = ce.codigo
        WHERE ct.idTema IN ({$listaIdsTemas})";
    $relaciones = consultarBaseDeDatos($sqlCriteriosTemas);

    // 4. Agrupar por idRA → [codigo => ['texto' => ..., 'temas' => [...]]]
    $raCriteriosTemas = array();
    foreach ($relaciones as $rel) {
        $idTema = (int)$rel['idTema'];
        $idRA = (int)$rel['idRA'];
        $codigo = $rel['codigo'];
        $texto = $rel['texto'];

        if (!isset($raCriteriosTemas[$idRA])) {
            $raCriteriosTemas[$idRA] = array();
        }
        if (!isset($raCriteriosTemas[$idRA][$codigo])) {
            $raCriteriosTemas[$idRA][$codigo] = array(
                'texto' => $texto,
                'temas' => array()
            );
        }
        $raCriteriosTemas[$idRA][$codigo]['temas'][] = $idTema;
    }

    // --- PRECÁLCULO DE LOS VALORES DE CADA CELDA (IPF/RAS para respetar %UP y %RA) ---
    // Estructura de salida: $valoresCelda[$idRA][$codigo][$idTema] = valor_calculado (en puntos porcentuales, sin redondear)
    $valoresCelda = array();

    // Índices compactos para RA y Temas
    $idxRA = array(); $revRA = array(); $r = 0;
    foreach ($resultadosAprendizaje as $ra) {
        $id = (int)$ra['id'];
        $idxRA[$id] = $r;
        $revRA[$r] = $id;
        $r++;
    }
    $idxT = array(); $revT = array(); $tix = 0;
    foreach ($temas as $t) {
        $id = (int)$t['id'];
        $idxT[$id] = $tix;
        $revT[$tix] = $id;
        $tix++;
    }
    $R = count($idxRA);
    $T = count($idxT);

    // Objetivos: filas (RA) y columnas (UP) en 0..100
    $rowTarget = array_fill(0, $R, 0.0); // % por RA (introducidos "a mano")
    foreach ($resultadosAprendizaje as $ra) {
        $rowTarget[$idxRA[(int)$ra['id']]] = (float)$ra['porcentaje_evaluacion'];
    }
    $colTarget = array_fill(0, $T, 0.0); // % por UP (temas)
    foreach ($temas as $t) {
        $colTarget[$idxT[(int)$t['id']]] = (float)$t['peso_evaluacion'];
    }

    // Normalización suave por si no suman exactamente 100 (evita incompatibilidades numéricas)
    $sumRows = array_sum($rowTarget);
    $sumCols = array_sum($colTarget);
    if ($sumRows > 0 && abs($sumRows - 100.0) > 1e-9) {
        $f = 100.0 / $sumRows;
        foreach ($rowTarget as $k => $v) $rowTarget[$k] = $v * $f;
    }
    if ($sumCols > 0 && abs($sumCols - 100.0) > 1e-9) {
        $f = 100.0 / $sumCols;
        foreach ($colTarget as $k => $v) $colTarget[$k] = $v * $f;
    }

    // N[r][t] = nº de criterios del RA r en la UP t (estructura de ceros)
    $N = array();
    for ($i = 0; $i < $R; $i++) {
        $N[$i] = array_fill(0, $T, 0);
    }
    foreach ($raCriteriosTemas as $idRA => $criterios) {
        if (!isset($idxRA[$idRA])) continue;
        $ridx = $idxRA[$idRA];
        foreach ($criterios as $codigo => $info) {
            foreach ($info['temas'] as $idTema) {
                if (!isset($idxT[$idTema])) continue;
                $tidx = $idxT[$idTema];
                $N[$ridx][$tidx] += 1;
            }
        }
    }

    // Validaciones estructurales mínimas (no detenemos, pero evitamos divisiones por 0)
    for ($ridx = 0; $ridx < $R; $ridx++) {
        $rowHasAny = false;
        for ($tidx = 0; $tidx < $T; $tidx++) if ($N[$ridx][$tidx] > 0) { $rowHasAny = true; break; }
        if (!$rowHasAny) $rowTarget[$ridx] = 0.0; // si un RA no aparece en ninguna UP, su objetivo efectivo es 0
    }
    for ($tidx = 0; $tidx < $T; $tidx++) {
        $colHasAny = false;
        for ($ridx = 0; $ridx < $R; $ridx++) if ($N[$ridx][$tidx] > 0) { $colHasAny = true; break; }
        if (!$colHasAny) $colTarget[$tidx] = 0.0; // si una UP no tiene RAs, su objetivo efectivo es 0
    }

    // ---------- PARCHE VIRTUAL: añadir CE transversal automático (sin tocar BD) ----------
    // Objetivo: evitar columnas exclusivas (temas con 1 solo RA).
    // Estrategia: para cada columna exclusiva, conectarla además con el RA que tenga mayor "capacidad" (rowTarget - carga exclusiva)

    // 1) Capacidad inicial por RA
    $cap = $rowTarget; // copia para heurística

    // 2) Detectamos columnas exclusivas y carga exclusiva por RA
    $exclusivoPorRA = array_fill(0, $R, 0.0);
    $colsExclusivas = []; // tidx => ridx único
    for ($tidx = 0; $tidx < $T; $tidx++) {
        $rUnico = -1; $cnt = 0;
        for ($ridx = 0; $ridx < $R; $ridx++) {
            if ($N[$ridx][$tidx] > 0) { $cnt++; $rUnico = $ridx; }
        }
        if ($cnt === 1) {
            $colsExclusivas[$tidx] = $rUnico;
            $exclusivoPorRA[$rUnico] += $colTarget[$tidx];
        }
    }

    define('COMPETENCIAS', 'zzz');

    // 3) Para cada columna exclusiva, conectar un segundo RA (el de mayor capacidad disponible)
    foreach ($colsExclusivas as $tidx => $rUnico) {
        // Elegir candidato distinto de rUnico
        $bestR = -1; $bestCap = -INF;
        for ($ridx = 0; $ridx < $R; $ridx++) {
            if ($ridx === $rUnico) continue;
            $c = (isset($cap[$ridx]) ? $cap[$ridx] : 0.0);
            if ($c > $bestCap) { $bestCap = $c; $bestR = $ridx; }
        }
        if ($bestR < 0) continue; // degenera, no debería ocurrir con R>=2

        if ($N[$bestR][$tidx] == 0) {
            // Conecta virtualmente: incrementa N y añade un criterio virtual en raCriteriosTemas
            $N[$bestR][$tidx] = 1;

            $idRBest = $revRA[$bestR];
            $idTema = $revT[$tidx];

            if (!isset($raCriteriosTemas[$idRBest])) {
                $raCriteriosTemas[$idRBest] = array();
            }
            if (!isset($raCriteriosTemas[$idRBest][COMPETENCIAS])) {
                $raCriteriosTemas[$idRBest][COMPETENCIAS] = array(
                    'texto' => 'Competencias transversales',
                    'temas' => array()
                );
            }
            // Evitar duplicados de tema:
            if (!in_array($idTema, $raCriteriosTemas[$idRBest][COMPETENCIAS]['temas'], true)) {
                $raCriteriosTemas[$idRBest][COMPETENCIAS]['temas'][] = $idTema;
            }

            // Heurística: actualiza capacidades (opcional)
            $cap[$bestR] = (isset($cap[$bestR]) ? $cap[$bestR] : 0.0) - $colTarget[$tidx];
        }
    }
    // ---------- FIN PARCHE VIRTUAL ----------

    // Inicialización de A[r][t] con la estructura N (mejor arranque que todo 1)
    $A = array();
    for ($ridx = 0; $ridx < $R; $ridx++) {
        $A[$ridx] = array();
        for ($tidx = 0; $tidx < $T; $tidx++) {
            $A[$ridx][$tidx] = ($N[$ridx][$tidx] > 0) ? (float)$N[$ridx][$tidx] : 0.0;
        }
    }

    // IPF / RAS para ajustar a márgenes de filas y columnas
    $maxIter = 1000;
    $tol = 1e-9;
    for ($iter = 0; $iter < $maxIter; $iter++) {
        // Escalado por filas (RA)
        for ($ridx = 0; $ridx < $R; $ridx++) {
            $s = 0.0;
            for ($tidx = 0; $tidx < $T; $tidx++) $s += $A[$ridx][$tidx];
            if ($s > 0.0) {
                $f = $rowTarget[$ridx] / $s;
                for ($tidx = 0; $tidx < $T; $tidx++) $A[$ridx][$tidx] *= $f;
            }
        }
        // Escalado por columnas (UP)
        for ($tidx = 0; $tidx < $T; $tidx++) {
            $s = 0.0;
            for ($ridx = 0; $ridx < $R; $ridx++) $s += $A[$ridx][$tidx];
            if ($s > 0.0) {
                $f = $colTarget[$tidx] / $s;
                for ($ridx = 0; $ridx < $R; $ridx++) $A[$ridx][$tidx] *= $f;
            }
        }
        // Criterio de parada
        $err = 0.0;
        for ($ridx = 0; $ridx < $R; $ridx++) {
            $s = 0.0; for ($tidx = 0; $tidx < $T; $tidx++) $s += $A[$ridx][$tidx];
            $err = max($err, abs($s - $rowTarget[$ridx]));
        }
        for ($tidx = 0; $tidx < $T; $tidx++) {
            $s = 0.0; for ($ridx = 0; $ridx < $R; $ridx++) $s += $A[$ridx][$tidx];
            $err = max($err, abs($s - $colTarget[$tidx]));
        }
        if ($err < $tol) break;
    }

    // Construir $valoresCelda distribuyendo A[r][t] entre los criterios del RA en esa UP
    foreach ($resultadosAprendizaje as $ra) {
        $idRA = (int)$ra['id'];
        if (!isset($raCriteriosTemas[$idRA])) continue;
        $ridx = $idxRA[$idRA];

        foreach ($temas as $t) {
            $idTema = (int)$t['id'];
            $tidx = $idxT[$idTema];
            $pesoRT = $A[$ridx][$tidx]; // % del RA en esa UP

            if ($pesoRT <= 0.0) continue;

            // Criterios de este RA en esta UP
            $criteriosEnUP = array();
            foreach ($raCriteriosTemas[$idRA] as $codigo => $info) {
                if (in_array($idTema, $info['temas'])) {
                    $criteriosEnUP[] = $codigo;
                }
            }
            $n = count($criteriosEnUP);
            if ($n == 0) continue;

            // Reparto a partes iguales entre los criterios del RA en esa UP (sin redondear aquí)
            $valorPorCriterio = $pesoRT / $n;
            foreach ($criteriosEnUP as $codigo) {
                if ($valorPorCriterio >= 0.01) {
                    $valoresCelda[$idRA][$codigo][$idTema] = $valorPorCriterio;
                }
            }
        }
    }
    // --- FIN PRECÁLCULO ---

    // --- GENERAR EL HTML ---
    $htmlTotal = '';
    $up_sa = $idCiclo > 0 ? 'UP' : 'SA';
    $estiloEncabezado = "text-align:left; background-color:#f2f2f2; font-weight:bold; font-size:12px;";

    foreach ($resultadosAprendizaje as $ra) {
        $idRA = (int)$ra['id'];
        $raOrden = (int)$ra['orden'];
        $raTitulo = isset($ra['texto']) ? $ra['texto'] : 'Sin título';
        $raEsClave = $idCiclo > 0 ? (bool)$ra['es_clave'] : false;
        $raTexto = ($idCiclo > 0 ? 'RA' : 'CE') . $raOrden;
        if ($raEsClave) $raTexto .= '*';

        if (!isset($raCriteriosTemas[$idRA]) || empty($raCriteriosTemas[$idRA])) {
            continue;
        }

        // Ordenar códigos: numéricos primero en orden ascendente y, al final, los no numéricos (p.ej. 'Competencias')
        $codigos = array_keys($raCriteriosTemas[$idRA]);
        usort($codigos, function($a, $b) {
            $na = is_numeric($a);
            $nb = is_numeric($b);
            if ($na && $nb) {
                $a = (int)$a;
                $b = (int)$b;
                if ($a == $b) return 0;
                return ($a < $b) ? -1 : 1;
            }
            if ($na && !$nb) return -1;
            if (!$na && $nb) return 1;
            return strcmp($a, $b);
        });

        $numTemas = count($temas);
        $widthTemas = ($numTemas > 8) ? 70 : 60;
        $widthPrimeraColumna = 100 - $widthTemas;
        $widthTema = $widthTemas / ($numTemas + 1);
        $colspanEncabezado = $numTemas + 2;

        if ($numTemas > 12) {
            $fontSizeTexto = 7.5;
            $fontSizeTemas = 6.5;
            $fontSizeTotal = 6.5;
        }
        else if ($numTemas > 8) {
            $fontSizeTexto = 9;
            $fontSizeTemas = 8;
            $fontSizeTotal = 9;
        }
        else {
            $fontSizeTexto = 10;
            $fontSizeTemas = 9;
            $fontSizeTotal = 10;
        }

        $fontSizeTexto = "font-size: {$fontSizeTexto}px";
        $fontSizeTemas = "font-size: {$fontSizeTemas}px";
        $fontSizeTotal = "font-size: {$fontSizeTotal}px";

        $html = "<br><table border=\"1\" cellpadding=\"4\" cellspacing=\"0\" width=\"100%\" style=\"{$fontSizeTexto};\">
                    <thead>
                        <tr nobr=\"true\">
                            <th colspan=\"{$colspanEncabezado}\" style=\"{$estiloEncabezado}\" width=\"100%\">{$raTexto}. {$raTitulo}</th>
                        </tr>
                        <tr nobr=\"true\">";

        $html .= "<th width=\"{$widthPrimeraColumna}%\" align=\"left\">Criterios de Evaluación</th>";
        foreach ($temas as $t) {
            $ordenTema = (int)$t['orden'];
            $html .= "<th width=\"{$widthTema}%\" align=\"center\" style=\"{$fontSizeTemas};\">{$up_sa} {$ordenTema}</th>";
        }
        $html .= "<th width=\"{$widthTema}%\" align=\"center\" style=\"{$fontSizeTemas};\">TOTAL</th></tr></thead><tbody>";

        $totalGeneral = 0.0;

        foreach ($codigos as $codigo) {
            $mostrarFila = false;
            // Comprobar si hay al menos un valor en la fila
            foreach ($temas as $t) {
                $idTemaActual = (int)$t['id'];

                if (isset($valoresCelda[$idRA][$codigo][$idTemaActual])) {
                    $mostrarFila = true;
                    break;
                }
            }
            if (!$mostrarFila) continue;

            $infoCriterio = $raCriteriosTemas[$idRA][$codigo];
            $textoCriterio = $infoCriterio['texto'];
            $totalFila = 0.0;

            $textoCodigo = ($codigo != COMPETENCIAS) ? "<strong>{$raOrden}.{$codigo}. </strong>" : "";

            $html .= "<tr nobr=\"true\"><td width=\"{$widthPrimeraColumna}%\">{$textoCodigo}{$textoCriterio}</td>";

            foreach ($temas as $t) {
                $idTemaActual = (int)$t['id'];
                $valor = '';

                if (isset($valoresCelda[$idRA][$codigo][$idTemaActual])) {
                    // Mostrar con 2 decimales pero sin redondear internamente antes
                    $v = $valoresCelda[$idRA][$codigo][$idTemaActual];
                    $valor = number_format($v, 2, ',', '');
                    $totalFila += $v;
                }

                $html .= "<td width=\"{$widthTema}%\" style=\"text-align: center; {$fontSizeTemas};\">{$valor}</td>";
            }

            $html .= "<td align=\"center\" width=\"{$widthTema}%\" style=\"{$fontSizeTotal};\"><strong>" . number_format($totalFila, 2, ',', '') . "</strong></td></tr>";
            $totalGeneral += $totalFila;
        }

        $colspan = $numTemas + 1;
        // TOTAL general de la tabla (2 decimales para coherencia)
        $html .= "<tr nobr=\"true\"><td colspan=\"{$colspan}\" align=\"right\"><strong>TOTAL:</strong></td><td align=\"center\" style=\"{$fontSizeTotal};\"><strong>" . number_format($totalGeneral, 1, ',', '') . "</strong></td></tr>";
        $html .= '</tbody></table><br>';

        $htmlTotal .= $html;
    }

    if (empty($htmlTotal)) {
        $htmlTotal = '<p>No hay tablas de criterios por resultado de aprendizaje para mostrar.</p>';
    }

    return $htmlTotal;
}
?>
