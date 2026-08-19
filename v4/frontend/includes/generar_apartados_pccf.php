<?php
    // -------------------------------
    // Genera el contenido HTML para el apartado de "Identificación"
    // -------------------------------
    function generarContenidoIdentificacion($idCiclo)
    {
        // 1. Obtener datos básicos del ciclo formativo
        $ciclo = obtenerDatosCiclo($idCiclo);

        if (empty($ciclo)) {
            return '<p>No se encontró el ciclo especificado.</p>';
        }

        list($anyo1, $anyo2) = obtenerCursoAcademico();

        // 4. Generar HTML
        $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">
                    <tr><td width=\"35%\"><strong>Centro:</strong></td><td width=\"65%\">IES San Vicente</td></tr>
                    <tr><td><strong>Familia Profesional:</strong></td><td>{$ciclo['familia']}</td></tr>
                    <tr><td><strong>Nivel:</strong></td><td>{$ciclo['nivel']}</td></tr>
                    <tr><td><strong>Ciclo Formativo:</strong></td><td>{$ciclo['nombre']}</td></tr>
                    <tr><td><strong>Horas:</strong></td><td>{$ciclo['horas']}</td></tr>
                    <tr><td><strong>Curso académico:</strong></td><td>{$anyo1}/{$anyo2}</td></tr>
                </table>";

        return $html;
    }

    // -------------------------------
    // Genera una tabla HTML con las competencias del ciclo cruzadas con las materias del mismo
    // -------------------------------
    function generarApartadoCompetenciasModulos($idCiclo, $tipoCompetencias = 1)
    {
        // 1. Obtener todas las materias asociadas al ciclo mediante cursos_ciclos
        $sqlMaterias = "
            SELECT m.id, m.codigo_oficial, m.tipo, m.nombre
            FROM materias m
            JOIN cursos c ON m.idCurso = c.id
            JOIN cursos_ciclos cc ON c.id = cc.idCurso
            WHERE cc.idCiclo = {$idCiclo}
            ORDER BY m.codigo_oficial";
        $materias = consultarBaseDeDatos($sqlMaterias);
        if (empty($materias)) {
            return '<p>No hay materias asociadas a este ciclo.</p>';
        }

        // 2. Obtener competencias del ciclo (según tipo)
        $sqlCompetencias = "
            SELECT id, codigo
            FROM competencias_ciclos
            WHERE idCiclo = {$idCiclo} AND tipo = {$tipoCompetencias}
            ORDER BY orden";
        $competencias = consultarBaseDeDatos($sqlCompetencias);
        if (empty($competencias)) {
            return '<p>No hay competencias definidas para este ciclo y tipo.</p>';
        }

        // 3. Construir mapa de relaciones: materia -> [competencia1, competencia2, ...]
        $idsMaterias = array();
        foreach ($materias as $m) {
            $idsMaterias[] = (int)$m['id'];
        }
        $idsMateriasList = implode(',', $idsMaterias);

        $relacionMateriaCompetencia = array();
        $sqlRelaciones = "
            SELECT t.idMateria, ct.idCompetencia
            FROM competencias_ciclos cc
            JOIN competencias_temas ct ON cc.id = ct.idCompetencia
            JOIN temas t ON ct.idTema = t.id
            WHERE t.idMateria IN ({$idsMateriasList}) AND cc.tipo = {$tipoCompetencias}
            ORDER BY cc.codigo";
        $relaciones = consultarBaseDeDatos($sqlRelaciones);

        foreach ($relaciones as $r) {
            $relacionMateriaCompetencia[(int)$r['idMateria']][(int)$r['idCompetencia']] = true;
        }

        // 4. Generar tabla HTML
        $html = '';

        // Calcula el ancho por columna de competencia
        $numCompetencias = count($competencias);
        $anchoModulo = $numCompetencias > 16 ? 10 : 15; // %
        $anchoRestante = 100 - $anchoModulo; // %
        $anchoPorCompetencia = $numCompetencias > 0 ? ($anchoRestante / $numCompetencias) : 0;

        $font_size = $numCompetencias > 16 ? 8 : 12;
        $padding = $numCompetencias > 16 ? 2 : 4;

        $html .= "<table border=\"1\" cellpadding=\"$padding\" cellspacing=\"0\" width=\"100%\" style=\"font-size: {$font_size}px;\">";
        $html .= "<thead><tr style=\"font-weight: bold;\">";
        $html .= "<th align=\"center\" style=\"width: {$anchoModulo}%;\">Módulo</th>";

        foreach ($competencias as $comp) {
            $html .= "<th align=\"center\" style=\"width: {$anchoPorCompetencia}%;\">{$comp['codigo']}</th>";
        }
        $html .= '</tr></thead><tbody>';

        foreach ($materias as $materia) {
            if ($materia['tipo'] == 'TUTORIA' || empty($materia['codigo_oficial'])) continue; // Omitir ciertas materias
            $html .= '<tr>';
            $codigoModulo = $materia['codigo_oficial'];
            $html .= "<td align=\"center\" style=\"width: {$anchoModulo}%;\">$codigoModulo</td>";

            $idMateria = (int)$materia['id'];
            foreach ($competencias as $comp) {
                $idComp = (int)$comp['id'];
                $marca = isset($relacionMateriaCompetencia[$idMateria][$idComp]) ? 'X' : '';
                $html .= "<td align=\"center\" style=\"width:{$anchoPorCompetencia}%;\">{$marca}</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table><br><br>';

        if ($tipoCompetencias == 1) {
            $competencias = obtenerCompetenciasProfesionalesPccf($idCiclo);
        } else {
            $competencias = obtenerCompetenciasEmpleabilidad($idCiclo);
        }

        foreach ($competencias as $comp) {
            $html .= "<strong>{$comp['codigo']})</strong> {$comp['texto']}<br>";
        }

        return $html;
    }

    // -------------------------------
    // Genera una tabla HTML con los módulos del ciclo: curso, código oficial y nombre oficial
    // -------------------------------
    function generarApartadoDistribucionModulos($idCiclo)
    {
        // 1. Validar entrada
        if (empty($idCiclo) || !is_numeric($idCiclo)) {
            return '<p>ID de ciclo no válido.</p>';
        }
        
        // 2. Consultar módulos del ciclo con su curso
        $sql = "
            SELECT 
                c.nombre AS curso_nombre,
                cc.orden AS curso_orden,
                m.codigo_oficial,
                m.nombre_oficial
            FROM materias m
            INNER JOIN cursos c ON m.idCurso = c.id
            INNER JOIN cursos_ciclos cc ON c.id = cc.idCurso
            WHERE cc.idCiclo = {$idCiclo}
              AND m.tipo != 'TUTORIA'
            ORDER BY cc.orden, m.codigo_oficial";

        $modulos = consultarBaseDeDatos($sql);

        if (empty($modulos)) {
            return '<p>No hay módulos asociados a este ciclo.</p>';
        }

        // 3. Generar tabla HTML
        $html = '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<thead><tr nobr="true" style="font-weight: bold;">
                    <th width="10%" align="center">Curso</th>
                    <th width="70%" align="center">Nombre oficial del módulo</th>
                    <th width="20%" align="center">Código oficial</th>
                  </tr></thead><tbody>';

        foreach ($modulos as $m) {
            // Omitir módulos sin código o nombre oficial
            if (empty($m['codigo_oficial']) || empty($m['nombre_oficial'])) continue;
            
            $codigo = $m['codigo_oficial'];
            $nombre = $m['nombre_oficial'];
            $curso = !empty($m['curso_orden']) ? $m['curso_orden'] : '1';

            $html .= "<tr nobr=\"true\">
                        <td width=\"10%\" align=\"center\">{$curso}º</td>
                        <td width=\"70%\" align=\"left\">{$nombre}</td>
                        <td width=\"20%\" align=\"center\">{$codigo}</td>
                      </tr>";
        }

        $html .= '</tbody></table>';

        return $html;
    }

    // -------------------------------
    // Genera el contenido HTML para el apartado de "Resultados de Aprendizaje de Formación en Empresa"
    // -------------------------------
    function generarContenidoResultadosAprendizajeEmpresa($idCiclo)
    {
        $sql = "
            SELECT m.id, m.nombre_oficial, m.horas_empresa
            FROM materias m
            INNER JOIN cursos c ON m.idCurso = c.id
            INNER JOIN cursos_ciclos cc ON c.id = cc.idCurso
            WHERE cc.idCiclo = {$idCiclo}
              AND m.tipo != 'TUTORIA'
            ORDER BY cc.orden, m.nombre";

        $modulos = consultarBaseDeDatos($sql);

        $html = '';
        foreach ($modulos as $modulo) {
            if (empty($modulo['nombre_oficial'])) continue;

            $html .= "<h3>{$modulo['nombre_oficial']}</h3><br>";
            $html .= generarContenidoResultadosAprendizaje($modulo['id'], $modulo['horas_empresa']);
            $html .= '<br>';
        }

        return $html;
    }    

    // -------------------------------
    // Genera el contenido de cada apartado predefinido según su tipo
    // -------------------------------
    function generarApartadoPredefinido($tipo, $idCiclo)
    {
        if (empty($idCiclo) || empty($tipo)) {
            return '';
        }

        switch($tipo) {
            case TIPO_APARTADO_PCCF_IDENTIFICACION:
                return(generarContenidoIdentificacion($idCiclo));
            case TIPO_APARTADO_PCCF_COMPETENCIAS_PROFESIONALES:
                return(generarApartadoCompetenciasModulos($idCiclo, 1));
            case TIPO_APARTADO_PCCF_COMPETENCIAS_EMPLEABILIDAD:
                return(generarApartadoCompetenciasModulos($idCiclo, 2));
            case TIPO_APARTADO_PCCF_DISTRIBUCION_MODULOS:
                return(generarApartadoDistribucionModulos($idCiclo));
            case TIPO_APARTADO_PCCF_RA_EMPRESA:
                return(generarContenidoResultadosAprendizajeEmpresa($idCiclo));
            default:
                return('');
        }
    }
?>