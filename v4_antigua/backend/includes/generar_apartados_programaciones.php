<?php
    // -------------------------------
    // Genera el contenido HTML para el apartado de "Evaluación del aprendizaje"
    // -------------------------------
    function generarContenidoEvaluacionAprendizaje($idMateria, $idCiclo)
    {
        $sql = "SELECT * FROM resultados_aprendizaje WHERE idMateria = $idMateria ORDER BY orden";
        $resultados = consultarBaseDeDatos($sql);

        if (empty($resultados)) {
            return '<p>No hay resultados de aprendizaje definidos para esta materia.</p>';
        }

        $tituloRACE = $idCiclo > 0 ? 'Resultados de Aprendizaje' : 'Competencias Específicas';

        $html = '<table border="1" cellpadding="5">
                    <thead>
                        <tr>
                            <th align="center" width="85%" colspan="2">' . $tituloRACE . '</th>
                            <th align="center" width="15%">% Evaluación</th>
                        </tr>
                    </thead>
                    <tbody>';

        $sumaPorcentajes = 0;

        foreach ($resultados as $ra) {
            $raNumero = ($idCiclo > 0 ? 'RA' : 'CE') . $ra['orden'];
            $raNumero .= ($idCiclo > 0 && (bool)$ra['es_clave'] ? '*' : '');
            $texto = $ra['texto'];
            $porcentajeEval = (int)$ra['porcentaje_evaluacion'];

            $html .= "<tr nobr=\"true\">
                        <td align=\"center\" width=\"10%\">{$raNumero}</td>
                        <td width=\"75%\">{$texto}</td>
                        <td align=\"center\" width=\"15%\">{$porcentajeEval}%</td>
                    </tr>";

            $sumaPorcentajes += $porcentajeEval;
        }

        // Fila de total
        $html .= "<tr nobr=\"true\">
                    <td colspan=\"2\" align=\"right\"><strong>Total:</strong></td>
                    <td align=\"center\"><strong>{$sumaPorcentajes}%</strong></td>
                </tr>";

        $html .= '</tbody></table>';

        $html .= imprimirMensajeRAClave($resultados, $idCiclo);

        return $html;
    }

    // -------------------------------
    // Genera el contenido HTML para el apartado de "Contexto"
    // -------------------------------
    function generarContenidoContexto($idMateria, $idCiclo, $profesores = array())
    {
        // 1. Obtener datos básicos de la materia
        $sqlMateria = "SELECT 
                        m.nombre_oficial, 
                        m.codigo_oficial, 
                        m.creditos_ects, 
                        m.horas_anuales, 
                        m.idDepartamento,
                        m.idCurso
                    FROM materias m
                    WHERE m.id = $idMateria";
        $materia = consultarBaseDeDatos($sqlMateria);

        if (empty($materia)) {
            return '<p>No se encontró la materia especificada.</p>';
        }
        $m = $materia[0];

        if (empty($idCiclo)) {
            $nombreCiclo = 'No disponible';
            $familia = 'No disponible';
            $nivel = 'No disponible';
        } else {
            $sqlCiclo = "SELECT nombre, familia, nivel FROM ciclos WHERE id = " . $idCiclo;
            $ciclo = consultarBaseDeDatos($sqlCiclo);
            if (!empty($ciclo)) {
                $nombreCiclo = $ciclo[0]['nombre'];
                $familia = $ciclo[0]['familia'];
                $nivel = $ciclo[0]['nivel'];
            } else {
                $nombreCiclo = 'No disponible';
                $familia = 'No disponible';
                $nivel = 'No disponible';
            }
        }

        // 2. Obtener lista de profesores
        $listaProfesores = !empty($profesores) ? implode(', ', $profesores) : 'No asignado';

        // 3. Comprobar todos los datos y asignar valores por defecto si es necesario
        $nombreOficial = isset($m['nombre_oficial']) ? $m['nombre_oficial'] : 'No disponible';
        $codigoOficial = isset($m['codigo_oficial']) ? $m['codigo_oficial'] : 'No disponible';
        $horasAnuales = isset($m['horas_anuales']) ? (int)$m['horas_anuales'] : 0;
        $creditosEcts = isset($m['creditos_ects']) ? (int)$m['creditos_ects'] : 0;
        $trCreditosEcts = $creditosEcts > 0
            ? "<tr><td><strong>Créditos ECTS:</strong></td><td>{$creditosEcts}</td></tr>"
            : '';
        list($anyo1, $anyo2) = obtenerCursoAcademico();

        // 4. Generar HTML
        $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">
                    <tr><td width=\"35%\"><strong>Centro:</strong></td><td width=\"65%\">IES San Vicente</td></tr>
                    <tr><td><strong>Familia Profesional:</strong></td><td>{$familia}</td></tr>
                    <tr><td><strong>Nivel:</strong></td><td>{$nivel}</td></tr>
                    <tr><td><strong>Ciclo Formativo:</strong></td><td>{$nombreCiclo}</td></tr>
                    <tr><td><strong>Denominación del módulo:</strong></td><td>{$nombreOficial}</td></tr>
                    <tr><td><strong>Código del módulo:</strong></td><td>{$codigoOficial}</td></tr>
                    <tr><td><strong>Horas anuales:</strong></td><td>{$horasAnuales}</td></tr>
                    {$trCreditosEcts}
                    <tr><td><strong>Curso académico:</strong></td><td>{$anyo1}/{$anyo2}</td></tr>
                    <tr><td><strong>Docentes responsables:</strong></td><td>{$listaProfesores}</td></tr>
                </table>";

        return $html;
    }

    // -------------------------------
    // Genera el contenido HTML para el apartado de "Relación de unidades de competencia y módulos profesionales"
    // -------------------------------
    function generarContenidoRelacionUCModulos($idMateria)
    {
        // 1. Obtener el idCiclo asociado a la materia
        $sqlCiclo = "
            SELECT cic.id
            FROM materias m
            JOIN cursos cur ON m.idCurso = cur.id
            JOIN cursos_ciclos cc ON cur.id = cc.idCurso
            JOIN ciclos cic ON cc.idCiclo = cic.id
            WHERE m.id = $idMateria
            LIMIT 1";
        $resCiclo = consultarBaseDeDatos($sqlCiclo);
        if (empty($resCiclo)) {
            return '<p>No se pudo determinar el ciclo formativo asociado a esta materia.</p>';
        }
        $idCiclo = (int)$resCiclo[0]['id'];

        // 2. Obtener todas las unidades de competencia del ciclo
        $sqlUCs = "
            SELECT uc.codigo, uc.texto
            FROM unidades_ciclos ucic
            JOIN unidades_competencia uc ON ucic.codigoUnidad = uc.codigo
            WHERE ucic.idCiclo = {$idCiclo}
            ORDER BY uc.codigo";
        $unidadesCompetencia = consultarBaseDeDatos($sqlUCs);
        if (empty($unidadesCompetencia)) {
            return '<p>No hay unidades de competencia asociadas al ciclo de esta materia.</p>';
        }

        // 3. Mapear cada UC a sus cualificaciones
        $ucToCualificaciones = [];
        $codigosUC = array_map(function($uc) {return $uc['codigo'];}, $unidadesCompetencia);
        $listaCodigosUC = [];
        foreach ($codigosUC as $codigo) {
            $listaCodigosUC[] = "'" . addslashes($codigo) . "'";
        }
        $listaCodigosUC = implode(',', $listaCodigosUC);

        $sqlCualifs = "
            SELECT cu.codigoCualificacion, cu.codigoUnidad, cp.texto AS nombre_cualificacion
            FROM cualificaciones_unidades cu
            JOIN cualificaciones_profesionales cp ON cu.codigoCualificacion = cp.codigo
            WHERE cu.codigoUnidad IN ({$listaCodigosUC})
            ORDER BY cu.codigoCualificacion, cu.codigoUnidad";
        $relacionesCualifUC = consultarBaseDeDatos($sqlCualifs);

        // Agrupar UCs por cualificación
        $cualificaciones = [];
        foreach ($relacionesCualifUC as $fila) {
            $cualif = $fila['codigoCualificacion'];
            if (!isset($cualificaciones[$cualif])) {
                $cualificaciones[$cualif] = [
                    'nombre' => $fila['nombre_cualificacion'],
                    'ucs' => []
                ];
            }
            $cualificaciones[$cualif]['ucs'][] = $fila['codigoUnidad'];
        }

        // Si no hay cualificaciones, mostrar mensaje
        if (empty($cualificaciones)) {
            return '<p>No se encontraron cualificaciones profesionales asociadas a las unidades de competencia del ciclo.</p>';
        }

        // 4. Obtener todos los módulos (materias) del mismo ciclo que tengan UCs
        $sqlModulosConUC = "
            SELECT m.id, m.nombre_oficial, m.codigo_oficial, ucm.idUC
            FROM unidades_competencia_materias ucm
            JOIN materias m ON ucm.idMateria = m.id
            JOIN cursos cur ON m.idCurso = cur.id
            JOIN cursos_ciclos cc ON cur.id = cc.idCurso
            WHERE cc.idCiclo = {$idCiclo}
            ORDER BY m.codigo_oficial";
        $modulosConUC = consultarBaseDeDatos($sqlModulosConUC);

        // Crear mapa: UC -> lista de módulos
        $ucToModulos = [];
        foreach ($modulosConUC as $modulo) {
            $uc = $modulo['idUC'];
            if (!isset($ucToModulos[$uc])) {
                $ucToModulos[$uc] = [];
            }
            $ucToModulos[$uc][] = [
                'codigo' => isset($modulo['codigo_oficial']) ? $modulo['codigo_oficial'] : 'Sin código',
                'nombre' => isset($modulo['nombre_oficial']) ? $modulo['nombre_oficial'] : 'Sin nombre'
            ];
        }

        // 5. Generar HTML
        $html = '';

        foreach ($cualificaciones as $codCualif => $datos) {
            $html .= "<h3>Cualificación Profesional: {$codCualif} - {$datos['nombre']}</h3>";
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
            $html .= '<thead><tr>
                        <th width="17%">Unidad de Competencia</th>
                        <th width="45%">Denominación</th>
                        <th width="38%">Módulos Profesionales que la incorporan</th>
                    </tr></thead><tbody>';

            // Obtener texto de cada UC
            $mapaUCTexto = [];
            foreach ($unidadesCompetencia as $uc) {
                $mapaUCTexto[$uc['codigo']] = $uc['texto'];
            }

            foreach ($datos['ucs'] as $codigoUC) {
                $textoUC = isset($mapaUCTexto[$codigoUC]) ? $mapaUCTexto[$codigoUC] : 'Sin descripción';
                $modulos = isset($ucToModulos[$codigoUC]) ? $ucToModulos[$codigoUC] : array();

                if (!empty($modulos)) {
                    $listaModulos = [];
                    foreach ($modulos as $mod) {
                        $listaModulos[] = "<strong>{$mod['codigo']}</strong>: {$mod['nombre']}";
                    }
                    $modulosHTML = implode('<br>', $listaModulos);
                } else {
                    $modulosHTML = '<em>No asignado a ningún módulo</em>';
                }

                $html .= "<tr nobr=\"true\">
                            <td width=\"17%\" align=\"center\">{$codigoUC}</td>
                            <td width=\"45%\">{$textoUC}</td>
                            <td width=\"38%\">{$modulosHTML}</td>
                        </tr>";
            }

            $html .= '</tbody></table><br>';
        }

        return $html;
    }

    // -------------------------------
    // Genera el contenido HTML para los contenidos de ESO/Bachillerato relacionados
    // -------------------------------
    function generarContenidosESOBACH($idMateria) 
    {
        $sql = "SELECT orden, titulo, contenidos FROM temas WHERE idMateria = {$idMateria} ORDER BY orden";
        $filas = consultarBaseDeDatos($sql);

        if (empty($filas)) return '';

        foreach ($filas as $fila) {
            $html .= "<br><h3>SA{$fila['orden']}. {$fila['titulo']}</h3>";
            $html .= "<p>{$fila['contenidos']}</p>";
        }

        return $html;
    }

    // -------------------------------
    // Genera el contenido HTML para el apartado de "Secuencia de temas y distribución temporal"
    // -------------------------------
    function generarContenidoDistribucionTemas($idMateria, $idCiclo)
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

        // 2. Mapear idRA -> orden (para mostrar RA1, RA2, etc.)
        $sqlRAs = "SELECT id, orden, es_clave FROM resultados_aprendizaje WHERE idMateria = {$idMateria}";
        $resultadosAprendizaje = consultarBaseDeDatos($sqlRAs);
        $mapaRaOrden = array();
        foreach ($resultadosAprendizaje as $ra) {
            $id = (int)$ra['id'];
            $mapaRaOrden[$id] = (int)$ra['orden'];
            $mapaRaEsClave[$id] = (int)$ra['es_clave'];
        }

        // 3. Obtener relaciones de criterios_temas para los temas
        $idsTemas = array();
        foreach ($temas as $t) {
            $idsTemas[] = (int)$t['id'];
        }
        $listaIdsTemas = implode(',', $idsTemas);

        $sqlCriteriosTemas = "
            SELECT idTema, idRA, codigo
            FROM criterios_temas
            WHERE idTema IN ({$listaIdsTemas})";
        $relaciones = consultarBaseDeDatos($sqlCriteriosTemas);

        // 4. Agrupar por tema → RA → lista de códigos
        $temaRaCriterios = array(); // idTema => [ idRA => [codigo1, codigo2, ...] ]
        foreach ($relaciones as $rel) {
            $idTema = (int)$rel['idTema'];
            $idRA = (int)$rel['idRA'];
            $codigo = $rel['codigo'];

            if (!isset($temaRaCriterios[$idTema])) {
                $temaRaCriterios[$idTema] = array();
            }
            if (!isset($temaRaCriterios[$idTema][$idRA])) {
                $temaRaCriterios[$idTema][$idRA] = array();
            }
            $temaRaCriterios[$idTema][$idRA][] = $codigo;
        }

        // 5. Generar HTML
        $tituloUPSA = $idCiclo > 0 ? 'UP' : 'SA';
        $tituloRACE = $idCiclo > 0 ? 'RA' : 'Comp.';
        $tituloTemas = $idCiclo > 0 ? "Contenidos" : "Saberes básicos";
        $columnaPorcentaje = $idCiclo > 0 ? "<th width=\"9%\" align=\"center\">% Eval.</th>" : '';
        $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">
                    <thead>
                        <tr nobr=\"true\">
                            <th width=\"8%\" align=\"center\">$tituloUPSA</th>
                            <th width=\"10%\" align=\"center\">$tituloRACE</th>
                            <th width=\"16%\" align=\"center\">Criterios de Evaluación</th>
                            <th width=\"40%\" align=\"center\">$tituloTemas</th>
                            $columnaPorcentaje
                            <th width=\"9%\" align=\"center\">Horas</th>
                            <th width=\"8%\" align=\"center\">Trim.</th>
                        </tr>
                    </thead>
                    <tbody>";

        $totalPorcentaje = 0;
        $totalHoras = 0;

        foreach ($temas as $tema) {
            $orden = (int)$tema['orden'];
            $titulo = isset($tema['titulo']) && $tema['titulo'] !== '' ? $tema['titulo'] : '<em>Sin título</em>';
            $porcentaje = isset($tema['peso_evaluacion']) ? (int)$tema['peso_evaluacion'] : 0;
            $horas = isset($tema['horas']) ? (int)$tema['horas'] : 0;
            $trimestre = isset($tema['trimestre']) ? (int)$tema['trimestre'] : 0;

            $totalPorcentaje += $porcentaje;
            $totalHoras += $horas;

            // Obtener RA del tema (claves del subarray)
            $raDelTema = isset($temaRaCriterios[$tema['id']]) ? array_keys($temaRaCriterios[$tema['id']]) : array();
            $numRAs = count($raDelTema);

            // Si no hay RA, mostrar una fila vacía
            if ($numRAs === 0) {
                $html .= "<tr nobr=\"true\">
                            <td align=\"center\">{$orden}</td>
                            <td></td>
                            <td></td>
                            <td>{$titulo}</td>
                            <td align=\"center\">{$porcentaje}%</td>
                            <td align=\"center\">{$horas}</td>
                            <td align=\"center\">{$trimestre}</td>
                        </tr>";
                continue;
            }

            // Ordenar RA por su número de orden (RA1, RA2, ...)
            usort($raDelTema, function($a, $b) use ($mapaRaOrden) {
                $ordA = isset($mapaRaOrden[$a]) ? $mapaRaOrden[$a] : 999;
                $ordB = isset($mapaRaOrden[$b]) ? $mapaRaOrden[$b] : 999;
                return $ordA - $ordB;
            });

            // Generar una fila por RA
            for ($i = 0; $i < $numRAs; $i++) {
                $idRA = $raDelTema[$i];
                $raOrden = isset($mapaRaOrden[$idRA]) ? $mapaRaOrden[$idRA] : '?';
                $raEsClave = isset($mapaRaEsClave[$idRA]) ? $mapaRaEsClave[$idRA] : 0;
                $esClave = ($raEsClave && $idCiclo > 0) ? '*' : '';
                $raTexto = ($idCiclo > 0 ? 'RA' : 'CE') . $raOrden;

                // Agrupar y ordenar códigos de criterios
                $codigos = $temaRaCriterios[$tema['id']][$idRA];
                sort($codigos); // opcional: ordenar alfabéticamente
                $ceTexto = implode(', ', array_unique($codigos));

                if ($i === 0) {
                    $columnaPorcentaje = $idCiclo > 0 ? "<td width=\"9%\" align=\"center\" rowspan=\"{$numRAs}\">{$porcentaje}%</td>" : '';
                    // Primera fila: con rowspan
                    $html .= "<tr nobr=\"true\">
                                <td width=\"8%\" align=\"center\" rowspan=\"{$numRAs}\">{$orden}</td>
                                <td width=\"10%\" align=\"center\">{$raTexto}{$esClave}</td>
                                <td width=\"16%\" align=\"center\">{$ceTexto}</td>
                                <td width=\"40%\" rowspan=\"{$numRAs}\">{$titulo}</td>
                                $columnaPorcentaje
                                <td width=\"9%\" align=\"center\" rowspan=\"{$numRAs}\">{$horas}</td>
                                <td width=\"8%\" align=\"center\" rowspan=\"{$numRAs}\">{$trimestre}</td>
                            </tr>";
                } else {
                    // Filas siguientes: solo RA y CE
                    $html .= "<tr nobr=\"true\">
                                <td width=\"10%\" align=\"center\">{$raTexto}{$esClave}</td>
                                <td width=\"16%\" align=\"center\">{$ceTexto}</td>
                            </tr>";
                }
            }
        }

        // Fila de totales
        $columnaPorcentaje = $idCiclo > 0 ? "<td width=\"9%\" align=\"center\"><strong>{$totalPorcentaje}%</strong></td>" : '';
        $html .= "<tr>
                    <td colspan=\"4\" align=\"right\"><strong>TOTALES:</strong></td>
                    $columnaPorcentaje
                    <td align=\"center\"><strong>{$totalHoras}</strong></td>
                    <td></td>
                </tr>";

        $html .= '</tbody></table>';

        $html .= imprimirMensajeRAClave($resultadosAprendizaje, $idCiclo);

        if (empty($idCiclo)) {
            $html .= generarContenidosESOBACH($idMateria);
        }

        return $html;
    }

    // -------------------------------
    // Genera el contenido HTML para el apartado de "Contribución de los RA a las competencias profesionales"
    // -------------------------------
    function generarContenidoRACompetencias($idMateria, $idCiclo, $tipoCompetencias = 1)
    {
        // 1. Obtener RA de la materia
        $sqlRA = "SELECT id, orden, texto FROM resultados_aprendizaje WHERE idMateria = {$idMateria} ORDER BY orden";
        $resultadosAprendizaje = consultarBaseDeDatos($sqlRA);
        if (empty($resultadosAprendizaje)) {
            return '<p>No hay resultados de aprendizaje definidos para esta materia.</p>';
        }

        // Mapear idRA => RA (para acceso rápido)
        $mapaRA = array();
        $prefijo = $idCiclo > 0 ? 'RA' : 'CE';
        foreach ($resultadosAprendizaje as $ra) {
            $mapaRA[$ra['id']] = $prefijo . $ra['orden'];
        }
        $idsRA = array_keys($mapaRA);

        // 2. Obtener competencias de la materia
        // Las competencias se vinculan a la materia a través de temas
        $sqlCompetencias = "
            SELECT DISTINCT cc.id, cc.codigo
            FROM competencias_ciclos cc
            JOIN competencias_temas ct ON cc.id = ct.idCompetencia
            JOIN temas t ON ct.idTema = t.id
            WHERE t.idMateria = {$idMateria} and cc.tipo = {$tipoCompetencias}
            ORDER BY cc.codigo";
        $competencias = consultarBaseDeDatos($sqlCompetencias);
        if (empty($competencias)) return '';

        // 3. Construir mapa: idRA -> [idCompetencia1, idCompetencia2, ...]
        // Paso 3.1: Obtener criterios por RA
        $idsRAList = implode(',', array_map('intval', $idsRA));
        $sqlCriterios = "
            SELECT idRA, codigo
            FROM criterios_evaluacion
            WHERE idRA IN ({$idsRAList})";
        $criterios = consultarBaseDeDatos($sqlCriterios);

        // Agrupar criterios por RA
        $criteriosPorRA = array();
        foreach ($criterios as $c) {
            $criteriosPorRA[$c['idRA']][] = $c['codigo'];
        }

        // Paso 3.2: Obtener temas por criterio (criterios_temas)
        // Primero, obtener todos los códigos de criterio únicos
        $codigosCriterio = array();
        foreach ($criteriosPorRA as $lista) {
            $codigosCriterio = array_merge($codigosCriterio, $lista);
        }
        $codigosCriterio = array_unique($codigosCriterio);
        if (empty($codigosCriterio)) {
            // Sin criterios, sin relación
            $relacionRACompetencia = array();
        } else {
            $listaCodigos = "'" . implode("','", array_map('addslashes', $codigosCriterio)) . "'";
            $sqlTemasPorCriterio = "
                SELECT ce.idRA, ct.idTema
                FROM criterios_temas ct
                JOIN criterios_evaluacion ce ON ct.idRA = ce.idRA AND ct.codigo = ce.codigo
                WHERE ce.idRA IN ({$idsRAList})";
            $temasPorCriterio = consultarBaseDeDatos($sqlTemasPorCriterio);

            // Paso 3.3: Obtener competencias por tema
            $idsTemas = array();
            foreach ($temasPorCriterio as $t) {
                $idsTemas[] = (int)$t['idTema'];
            }
            $idsTemas = array_unique($idsTemas);
            if (!empty($idsTemas)) {
                $listaTemas = implode(',', $idsTemas);
                $sqlCompetenciasPorTema = "
                    SELECT ct.idTema, ct.idCompetencia
                    FROM competencias_temas ct
                    WHERE ct.idTema IN ({$listaTemas})";
                $compPorTema = consultarBaseDeDatos($sqlCompetenciasPorTema);

                // Mapear tema -> competencias
                $temaACompetencias = array();
                foreach ($compPorTema as $fila) {
                    $temaACompetencias[(int)$fila['idTema']][] = (int)$fila['idCompetencia'];
                }

                // Ahora vincular RA -> competencias
                $relacionRACompetencia = array();
                foreach ($temasPorCriterio as $t) {
                    $idRA = (int)$t['idRA'];
                    $idTema = (int)$t['idTema'];
                    if (isset($temaACompetencias[$idTema])) {
                        foreach ($temaACompetencias[$idTema] as $idComp) {
                            $relacionRACompetencia[$idRA][$idComp] = true;
                        }
                    }
                }
            } else {
                $relacionRACompetencia = array();
            }
        }

        if ($idCiclo > 0) {
            if ($tipoCompetencias == 1) {
                $html = '<h2>Competencias profesionales</h2>';
            } else {
                $html = '<h2>Competencias para la empleabilidad</h2>';
            }
        }

        // 4. Generar tabla HTML
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<thead><tr><th align="center">Comp.</th>';
        $prefijo = $idCiclo > 0 ? 'RA' : 'CE';
        foreach ($resultadosAprendizaje as $ra) {
            $raLabel = $prefijo . $ra['orden'];
            $html .= "<th align=\"center\">{$raLabel}</th>";
        }
        $html .= '</tr></thead><tbody>';

        foreach ($competencias as $comp) {
            $html .= '<tr nobr="true">';
            $html .= '<td align="center">' . $comp['codigo'] . '</td>';
            foreach ($resultadosAprendizaje as $ra) {
                $idRA = $ra['id'];
                $idComp = $comp['id'];
                $tieneRelacion = isset($relacionRACompetencia[$idRA][$idComp]);
                $marca = $tieneRelacion ? 'X' : '';
                $html .= "<td align=\"center\">{$marca}</td>";
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table><br><br>';

        if ($tipoCompetencias == 1) {
            $competencias = obtenerCompetenciasProfesionales($idCiclo, $idMateria);
        } else {
            $competencias = obtenerCompetenciasEmpleabilidad($idCiclo);
        }

        foreach ($competencias as $comp) {
            $html .= "<strong>{$comp['codigo']})</strong> {$comp['texto']}<br>";
        }

        return $html;
    }

    // -------------------------------
    // Genera el contenido de las unidades de programación (temas)
    // -------------------------------
    function generarContenidoTemas($idMateria, $idDepartamento, $idCiclo)
    {
        $temas = obtenerTemasDeMateria($idMateria);
        if (empty($temas)) {
            return array('<p>No hay temas definidos para esta materia.</p>');
        }

        $contenidosDefecto = null;
        if ($idDepartamento) {
            $contenidosDefecto = obtenerContenidosDefectoTema($idDepartamento);
        }

        $temasHTML = array();

        foreach ($temas as $tema) {
            $html = '';

            // Título
            $prefijo = $idCiclo > 0 ? 'Tema ' : 'SA';
            $html .= "<h2>{$prefijo}{$tema['orden']}: {$tema['titulo']}</h2>";

            // Tabla básica
            $html .= "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" style=\"width:100%; font-size:14px;\">
                        <tr>
                            <th align=\"center\" bgcolor=\"#f2f2f2\">Horas</th>
                            <th align=\"center\" bgcolor=\"#f2f2f2\">Trimestre</th>
                            <th align=\"center\" bgcolor=\"#f2f2f2\">Peso en evaluación</th>
                        </tr>
                        <tr nobr=\"true\">
                            <td align=\"center\">{$tema['horas']}</td>
                            <td align=\"center\">{$tema['trimestre']}</td>
                            <td align=\"center\">{$tema['peso_evaluacion']}%</td>
                        </tr>
                    </table><br>";

            // Campos sin defecto - ¡NO ESCAPAR! (se espera HTML)
            $camposSinDefecto = array(
                'descripcion'   => 'Descripción',
                'justificacion' => 'Justificación',
                'secuenciacion' => 'Secuenciación',
                'contenidos'    => $idCiclo > 0 ? "Contenidos" : "Saberes básicos",
                'evaluacion'    => 'Evaluación'
            );

            foreach ($camposSinDefecto as $campo => $titulo) {
                $contenido = trim($tema[$campo]);
                if (!empty($contenido)) {
                    $html .= '<h3>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h3>';
                    $html .= $contenido;
                    $html .= '<br>'; // Añadir salto de línea para separar
                }
            }

            // Campos con defecto - ¡NO ESCAPAR! (se espera HTML)
            $camposConDefecto = array(
                'contexto'      => 'Contexto',
                'recursos'      => 'Recursos',
                'metodologia'   => 'Metodología',
                'adaptaciones'  => 'Adaptaciones'
            );

            foreach ($camposConDefecto as $campo => $titulo) {
                $usarDefecto = !empty($tema["{$campo}_defecto"]) && $tema["{$campo}_defecto"] == 1;
                $contenido = '';
                if ($usarDefecto && $contenidosDefecto && isset($contenidosDefecto[$campo])) {
                    $contenido = trim($contenidosDefecto[$campo]);
                } else {
                    $contenido = trim($tema[$campo]);
                }
                if (!empty($contenido)) {
                    $html .= '<h3>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h3>';
                    $html .= $contenido; // <<< ¡No escapar! Se espera HTML
                    $html .= '<br>';
                }
            }

            // RA y criterios
            $sqlCriterios = "
                SELECT ce.idRA, ce.codigo, ce.texto AS criterio
                FROM criterios_temas ct
                INNER JOIN criterios_evaluacion ce ON ct.idRA = ce.idRA AND ct.codigo = ce.codigo
                WHERE ct.idTema = " . (int)$tema['id'] . "
                ORDER BY ce.idRA, ce.codigo";
            $criteriosConRA = consultarBaseDeDatos($sqlCriterios);

            if (!empty($criteriosConRA)) {
                $rasAgrupados = array();
                $prefijo = $idCiclo > 0 ? 'RA' : 'CE';
                foreach ($criteriosConRA as $fila) {
                    $idRA = (int)$fila['idRA'];
                    if (!isset($rasAgrupados[$idRA])) {
                        $raData = consultarBaseDeDatos("SELECT orden, texto FROM resultados_aprendizaje WHERE id = {$idRA} AND idMateria = {$idMateria}");
                        $textoRA = !empty($raData)
                            ? $prefijo . $raData[0]['orden'] . '. ' . $raData[0]['texto']
                            : "Resultado de aprendizaje no encontrado (ID: {$idRA})";
                        $rasAgrupados[$idRA] = array('texto' => $textoRA, 'criterios' => array());
                    }
                    $rasAgrupados[$idRA]['criterios'][] = $fila['codigo'] . ') ' . $fila['criterio'];
                }

                $tituloRACE = $idCiclo > 0 ? 'Resultados de Aprendizaje y Criterios de Evaluación' : 'Competencias Específicas y Criterios de Evaluación';

                $html .= "<h3>{$tituloRACE}</h3>";
                foreach ($rasAgrupados as $datos) {
                    $html .= "<p><strong>{$datos['texto']}</strong></p>";
                    $html .= '<ul style="list-style: none; padding-left: 0;">';
                    foreach ($datos['criterios'] as $criterio) {
                        $html .= "<li>$criterio</li>";
                    }
                    $html .= '</ul>';
                }
            } else {
                $html .= '<p>No hay resultados de aprendizaje ni criterios de evaluación asociados a este tema.</p>';
            }

            // Competencias
            $competencias = obtenerCompetenciasDeTema($tema['id']);
            if (!empty($competencias)) {
                $tituloCompetencias = $idCiclo > 0 ? 'Competencias profesionales y para la empleabilidad' : 'Competencias clave';
                $html .= "<h3>{$tituloCompetencias}</h3><ul>";
                foreach ($competencias as $comp) {
                    $html .= "<li><strong>{$comp['codigo']})</strong> {$comp['texto']}</li>";
                }
                $html .= '</ul>';
            }

            $temasHTML[] = $html;
        }

        return $temasHTML;
    }

    // -------------------------------
    // Genera el contenido HTML para el apartado de "Resultados de Aprendizaje / Competencias Específicas y sus Criterios de Evaluación"
    // -------------------------------
    function generarApartadoRACE($idMateria, $idCiclo)
    {
        $esCiclo = ((int)$idCiclo) > 0;

        // 1. Obtener todos los resultados de aprendizaje (o competencias específicas)
        $sqlRA = "SELECT id, orden, texto FROM resultados_aprendizaje WHERE idMateria = {$idMateria} ORDER BY orden";
        $resultados = consultarBaseDeDatos($sqlRA);

        if (empty($resultados)) {
            return '<p>No hay resultados de aprendizaje ni competencias específicas definidos para esta materia.</p>';
        }

        // 2. Obtener todos los criterios de evaluación asociados a estos RA/CE
        $idsRA = array();
        foreach ($resultados as $ra) {
            $idsRA[] = (int)$ra['id'];
        }
        $listaIdsRA = implode(',', $idsRA);

        $sqlCriterios = "SELECT idRA, codigo, texto FROM criterios_evaluacion WHERE idRA IN ({$listaIdsRA}) ORDER BY idRA, codigo";
        $criterios = consultarBaseDeDatos($sqlCriterios);

        // Agrupar criterios por idRA
        $criteriosPorRA = array();
        foreach ($criterios as $c) {
            $idRA = (int)$c['idRA'];
            if (!isset($criteriosPorRA[$idRA])) {
                $criteriosPorRA[$idRA] = array();
            }
            $criteriosPorRA[$idRA][] = array(
                'codigo' => $c['codigo'],
                'texto'  => $c['texto']
            );
        }

        // 3. Generar HTML
        $html = "";
        $prefijo = $esCiclo ? 'RA' : 'CE';
        foreach ($resultados as $ra) {
            $raId = (int)$ra['id'];
            $raOrden = (int)$ra['orden'];
            $raTexto = htmlspecialchars($ra['texto'], ENT_QUOTES, 'UTF-8');
            $raEtiqueta = $prefijo . $raOrden;

            $html .= "<h3>{$raEtiqueta}. {$raTexto}</h3>";

            if (isset($criteriosPorRA[$raId]) && !empty($criteriosPorRA[$raId])) {
                $html .= '<ul>';
                foreach ($criteriosPorRA[$raId] as $crit) {
                    $codigo = htmlspecialchars($crit['codigo'], ENT_QUOTES, 'UTF-8');
                    $textoCrit = htmlspecialchars($crit['texto'], ENT_QUOTES, 'UTF-8');
                    $html .= "<li><strong>{$codigo})</strong> {$textoCrit}</li>";
                }
                $html .= '</ul>';
            } else {
                $html .= '<p><em>No hay criterios de evaluación asociados.</em></p>';
            }
        }

        return $html;
    }

    // -------------------------------
    // Genera el contenido de cada apartado predefinido según su tipo
    // -------------------------------
    function generarApartadoPredefinido($tipo, $idMateria, $idCiclo, $idDepartamento, $profesores=[], $horasEmpresa = 0)
    {
        if (empty($tipo) || empty($idMateria) || empty($idDepartamento)) {
            return '';
        }

        if (empty($profesores)) {
            $profesores = obtenerProfesoresMateria($idMateria);
        }

        $idMateria = (int)$idMateria;
        $idDepartamento = (int)$idDepartamento;

        switch($tipo) {
            case TIPO_APARTADO_CONTEXTO:
                return(generarContenidoContexto($idMateria, $idCiclo, $profesores));
            case TIPO_APARTADO_RELACION_UC_MODULOS:
                return(generarContenidoRelacionUCModulos($idMateria));
            case TIPO_APARTADO_RELACION_RA_COMPETENCIAS:
                if (empty($idCiclo)) {
                    return(generarContenidoRACompetencias($idMateria, $idCiclo, 1));
                }
                else {
                    return(generarContenidoRACompetencias($idMateria, $idCiclo, 1) .
                           generarContenidoRACompetencias($idMateria, $idCiclo, 2));
                }
            case TIPO_APARTADO_SECUENCIA_TEMAS:
                return(generarContenidoDistribucionTemas($idMateria, $idCiclo));
            case TIPO_APARTADO_FE:
                return(generarContenidoResultadosAprendizaje($idMateria, $horasEmpresa));
            case TIPO_APARTADO_RA_CE:
                return(generarApartadoRACE($idMateria, $idCiclo));
            case TIPO_APARTADO_EVALUACION_RA:
                return(generarContenidoEvaluacionAprendizaje($idMateria, $idCiclo));
            case TIPO_APARTADO_TEMAS:
                return(generarContenidoTemas($idMateria, $idDepartamento, $idCiclo));
            default:
                return('');
        }
    }
?>
