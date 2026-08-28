<?php
// ============================================================================
// Librería compartida de generación de PDFs de Programaciones (Fase 2.1)
// ============================================================================
//
// Port a la arquitectura de v4 (mysqli + config.php) de los includes de v3:
//   - v3/includes/consultas_bd.php           (consultas auxiliares)
//   - v3/includes/generar_apartados_programaciones.php (contenido predefinido)
//   - v3/includes/generar_apartado_ra_empresas.php      (apartado FE)
//   - v3/includes/constantes.php             (tipos de apartado)
//
// La incluye cada uno de los endpoints pdf_programaciones*.php, que además
// cargan TCPDF (lib/php/tcpdf) y config.php. PHP 5 compatible.
//
// Los prefijos `pg*` evitan colisiones de nombres de función/clase con el
// resto del backend en la misma petición.

// ---------------------------------------------------------------------------
// Tipos de apartado (mismos valores que v3/includes/constantes.php)
// ---------------------------------------------------------------------------
if (!defined('PG_TIPO_APARTADO_EDITABLE')) {
    define('PG_TIPO_APARTADO_EDITABLE', 0);
}
define('PG_TIPO_APARTADO_CONTEXTO', 1);
define('PG_TIPO_APARTADO_RELACION_UC_MODULOS', 2);
define('PG_TIPO_APARTADO_RELACION_RA_COMPETENCIAS', 3);
define('PG_TIPO_APARTADO_SECUENCIA_TEMAS', 4);
define('PG_TIPO_APARTADO_FE', 5);
define('PG_TIPO_APARTADO_RA_CE', 6);
define('PG_TIPO_APARTADO_EVALUACION_RA', 10);
define('PG_TIPO_APARTADO_TEMAS', 13);

// ---------------------------------------------------------------------------
// Cabecera y pie de página (igual que el resto de PDFs de la app / v3): se
// heredan de la base compartida MiPDFBase (lib/pdf_compartidas.php)
// ---------------------------------------------------------------------------
require_once __DIR__ . '/pdf_compartidas.php';
class MiPDFProgramaciones extends MiPDFBase
{
}

// ---------------------------------------------------------------------------
// Consulta a BD a través de la clase Db (v4).
// Acepta un objeto Db; si le llega la conexión mysqli cruda (pendiente de
// migrar el endpoint que la pasa), se envuelve sola en un Db.
// ---------------------------------------------------------------------------
function pgConsultar($db, $sql, $params = [])
{
    if (!$db instanceof Db) {
        $db = new Db($db);
    }
    // La capa Db usa argumentos variables; se expande la lista de parámetros
    return $db->fetchAll(...array_merge([$sql], $params));
}

function pgConsultarUna($db, $sql, $params = [])
{
    $res = pgConsultar($db, $sql, $params);
    return !empty($res) ? $res[0] : null;
}

// ---------------------------------------------------------------------------
// Contexto de copia de aula (Programaciones de aula, Fase 2.4).
//
// Cuando se genera el PDF de una copia de aula, las tablas de copia
// (temas, resultados_aprendizaje, criterios_temas, criterios_evaluacion,
// competencias_temas, contenidos_programaciones) se leen de sus versiones
// *_aula, filtradas por el (grupo, profesor) de la copia. Para ello las
// funciones de contenido de esta librería aceptan un parámetro opcional
// $aula: null (o no pasado) → tablas compartidas (propuesta pedagógica);
// array ['idGrupo' => X, 'idProfesor' => Y] → tablas de copia de aula.
//
// Los dos helpers evitan repetir la lógica de nombre de tabla y de filtro.
// ---------------------------------------------------------------------------
function pgTablaAula($tabla, $aula)
{
    return $aula ? ($tabla . '_aula') : $tabla;
}

function pgFiltroAula($aula, $alias = '')
{
    if (!$aula) {
        return '';
    }
    // $alias: alias de tabla cuando la consulta une varias tablas de copia
    // (todas con idGrupo/idProfesor) y hay que cualificar las columnas.
    $pref = $alias ? ($alias . '.') : '';
    return " AND {$pref}idGrupo = " . (int)$aula['idGrupo'] . " AND {$pref}idProfesor = " . (int)$aula['idProfesor'];
}

// ---------------------------------------------------------------------------
// Curso académico (copia de obtenerCursoAcademico de v3/utilidades.php)
// ---------------------------------------------------------------------------
function pgCursoAcademico()
{
    $mes = (int)date('n');
    $anio = (int)date('Y');
    if ($mes >= 9) {
        return array($anio, $anio + 1);
    }
    return array($anio - 1, $anio);
}

// ---------------------------------------------------------------------------
// Datos de una materia
// ---------------------------------------------------------------------------
function pgObtenerDatosMateria($db, $idMateria)
{
    $row = pgConsultarUna($db,
        "SELECT cursos.nombre AS curso, cursos.categoria,
                 materias.nombre AS materia, materias.horas_empresa AS horas_empresa,
                 materias.horas AS horas,
                 departamentos.id AS id_departamento, departamentos.nombre AS departamento
           FROM cursos
           INNER JOIN materias ON cursos.id = materias.idCurso
           INNER JOIN departamentos ON materias.idDepartamento = departamentos.id
          WHERE materias.id = ?",
        array((int)$idMateria));
    return $row;
}

// ---------------------------------------------------------------------------
// idCiclo de una materia (0 si no es de ciclo → ESO/BACH)
// ---------------------------------------------------------------------------
function pgObtenerIdCicloPorMateria($db, $idMateria)
{
    $row = pgConsultarUna($db,
        "SELECT c.id
           FROM ciclos c
           INNER JOIN cursos_ciclos cc ON cc.idCiclo = c.id
           INNER JOIN cursos cu ON cu.id = cc.idCurso
           INNER JOIN materias m ON m.idCurso = cu.id
          WHERE m.id = ?
          LIMIT 1",
        array((int)$idMateria));
    return $row ? (int)$row['id'] : 0;
}

// ---------------------------------------------------------------------------
// Profesores que imparten la materia (del escenario actual)
// ---------------------------------------------------------------------------
function pgObtenerProfesoresMateria($db, $idMateria)
{
    $rows = pgConsultar($db,
        "SELECT p.nombre
           FROM profesores p
           INNER JOIN seleccion s ON p.id = s.idProfesor
           WHERE s.idMateria = ?
             AND s.idEscenario IN (SELECT id FROM escenarios_desideratas WHERE actual = 1)
           GROUP BY p.id
          ORDER BY p.orden",
        array((int)$idMateria));
    $profesores = [];
    foreach ($rows as $fila) {
        $profesores[] = $fila['nombre'];
    }
    return $profesores;
}

// ---------------------------------------------------------------------------
// Profesor de una copia de aula (solo el profesor de la copia, por su id).
// Devuelve un array con el nombre (mismo formato que pgObtenerProfesoresMateria).
// ---------------------------------------------------------------------------
function pgObtenerProfesoresAula($db, $idProfesor)
{
    $row = pgConsultarUna($db, "SELECT nombre FROM profesores WHERE id = ?", array((int)$idProfesor));
    return $row ? array($row['nombre']) : array();
}

// ---------------------------------------------------------------------------
// Apartados de una categoría de curso
// ---------------------------------------------------------------------------
function pgObtenerApartadosProgramacion($db, $categoria)
{
    return pgConsultar($db,
        "SELECT * FROM apartados_programaciones
          WHERE categoria IS NULL OR categoria = 'TODOS' OR categoria LIKE ?
          ORDER BY orden",
        array('%' . (string)$categoria . '%'));
}

// ---------------------------------------------------------------------------
// Contenido de un apartado (personalizado o por defecto)
// ---------------------------------------------------------------------------
function pgObtenerContenidoApartado($db, $idApartado, $idMateria, $idDepartamento = 0, $aula = null)
{
    // El texto personalizado de la copia sale de contenidos_programaciones_aula
    // (filtrada por el grupo/profesor de la copia); el por defecto sigue
    // compartiendo el catálogo (no se copia).
    $row = pgConsultarUna($db,
        "SELECT texto FROM " . pgTablaAula('contenidos_programaciones', $aula) . " WHERE idApartado = ? AND idMateria = ?" . pgFiltroAula($aula), array((int)$idApartado, (int)$idMateria));
    if ($row && trim($row['texto']) !== '') {
        return $row['texto'];
    }
    $defecto = pgConsultarUna($db,
        "SELECT texto FROM contenidos_defecto_programaciones WHERE idApartado = ? AND idDepartamento = ?", array((int)$idApartado, (int)$idDepartamento));
    if ($defecto && trim($defecto['texto']) !== '') {
        return $defecto['texto'];
    }
    return '';
}

// ---------------------------------------------------------------------------
// Impresión del mensaje de RA/CE clave
// ---------------------------------------------------------------------------
function pgImprimirMensajeRAClave($resultadosAprendizaje, $idCiclo)
{
    if ($idCiclo <= 0) {
        return '';
    }
    $hayClave = false;
    foreach ($resultadosAprendizaje as $r) {
        if (!empty($r['es_clave'])) {
            $hayClave = true;
            break;
        }
    }
    if ($hayClave) {
        $textoRACE = $idCiclo > 0 ? 'Resultado de aprendizaje' : 'Competencia específica';
        return '<p style="margin-top:10px;"><em>* ' . $textoRACE . ' clave: se debe superar para aprobar la materia.</em></p>';
    }
    return '';
}

// ---------------------------------------------------------------------------
// Temas de una materia
// ---------------------------------------------------------------------------
function pgObtenerTemasDeMateria($db, $idMateria, $aula = null)
{
    return pgConsultar($db, "SELECT * FROM " . pgTablaAula('temas', $aula) . " WHERE idMateria = ?" . pgFiltroAula($aula) . " ORDER BY orden", array((int)$idMateria));
}

// ---------------------------------------------------------------------------
// Contenidos por defecto de los temas de un departamento
// ---------------------------------------------------------------------------
function pgObtenerContenidosDefectoTema($db, $idDepartamento)
{
    $rows = pgConsultar($db, "SELECT * FROM contenidos_defecto_temas WHERE idDepartamento = ?", array((int)$idDepartamento));
    return empty($rows) ? null : $rows[0];
}

// ---------------------------------------------------------------------------
// Competencias asociadas a un tema
// ---------------------------------------------------------------------------
function pgObtenerCompetenciasDeTema($db, $idTema, $aula = null)
{
    return pgConsultar($db,
        "SELECT cc.codigo, cc.texto
           FROM " . pgTablaAula('competencias_temas', $aula) . " cmt
           INNER JOIN competencias_ciclos cc ON cmt.idCompetencia = cc.id
          WHERE cmt.idTema = ?",
        array((int)$idTema));
}

// ---------------------------------------------------------------------------
// Competencias profesionales de un ciclo para una materia
// ---------------------------------------------------------------------------
function pgObtenerCompetenciasProfesionales($db, $idCiclo, $idMateria = 0)
{
    return pgConsultar($db,
        "SELECT DISTINCT cc.codigo, cc.texto, cc.orden
           FROM competencias_ciclos cc
           INNER JOIN competencias_materias cm ON cc.id = cm.idCompetencia
          WHERE cc.idCiclo = ? AND cc.tipo = 1 AND cm.idMateria = ?
          ORDER BY cc.orden",
        array((int)$idCiclo, (int)$idMateria));
}

// ---------------------------------------------------------------------------
// Competencias de empleabilidad de un ciclo
// ---------------------------------------------------------------------------
function pgObtenerCompetenciasEmpleabilidad($db, $idCiclo)
{
    return pgConsultar($db,
        "SELECT cc.codigo, cc.texto
           FROM competencias_ciclos cc
          WHERE cc.idCiclo = ? AND cc.tipo = '2'
          ORDER BY cc.orden",
        array((int)$idCiclo));
}

// ---------------------------------------------------------------------------
// Apartado "Evaluación del aprendizaje"
// ---------------------------------------------------------------------------
function pgGenerarContenidoEvaluacionAprendizaje($db, $idMateria, $idCiclo, $aula = null)
{
    $resultados = pgConsultar($db, "SELECT * FROM " . pgTablaAula('resultados_aprendizaje', $aula) . " WHERE idMateria = ?" . pgFiltroAula($aula) . " ORDER BY orden", array((int)$idMateria));
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
        $porcentajeEval = (int)$ra['porcentaje_evaluacion'];
        $html .= "<tr nobr=\"true\">
                    <td align=\"center\" width=\"10%\">{$raNumero}</td>
                    <td width=\"75%\">{$ra['texto']}</td>
                    <td align=\"center\" width=\"15%\">{$porcentajeEval}%</td>
                </tr>";
        $sumaPorcentajes += $porcentajeEval;
    }
    $html .= "<tr nobr=\"true\">
                <td colspan=\"2\" align=\"right\"><strong>Total:</strong></td>
                <td align=\"center\"><strong>{$sumaPorcentajes}%</strong></td>
            </tr>";
    $html .= '</tbody></table>';
    $html .= pgImprimirMensajeRAClave($resultados, $idCiclo);
    return $html;
}

// ---------------------------------------------------------------------------
// Apartado "Contexto"
// ---------------------------------------------------------------------------
function pgGenerarContenidoContexto($db, $idMateria, $idCiclo, $profesores = array())
{
    $materia = pgConsultar($db,
        "SELECT m.nombre_oficial, m.codigo_oficial, m.creditos_ects, m.horas_anuales, m.idDepartamento, m.idCurso
           FROM materias m WHERE m.id = ?",
        array((int)$idMateria));
    if (empty($materia)) {
        return '<p>No se encontró la materia especificada.</p>';
    }
    $m = $materia[0];

    if (empty($idCiclo)) {
        $nombreCiclo = 'No disponible';
        $familia = 'No disponible';
        $nivel = 'No disponible';
    } else {
        $ciclo = pgConsultarUna($db, "SELECT nombre, familia, nivel FROM ciclos WHERE id = ?", array((int)$idCiclo));
        if (!empty($ciclo)) {
            $nombreCiclo = $ciclo['nombre'];
            $familia = $ciclo['familia'];
            $nivel = $ciclo['nivel'];
        } else {
            $nombreCiclo = 'No disponible';
            $familia = 'No disponible';
            $nivel = 'No disponible';
        }
    }

    $listaProfesores = !empty($profesores) ? implode(', ', $profesores) : 'No asignado';
    $nombreOficial = isset($m['nombre_oficial']) ? $m['nombre_oficial'] : 'No disponible';
    $codigoOficial = isset($m['codigo_oficial']) ? $m['codigo_oficial'] : 'No disponible';
    $horasAnuales = isset($m['horas_anuales']) ? (int)$m['horas_anuales'] : 0;
    $creditosEcts = isset($m['creditos_ects']) ? (int)$m['creditos_ects'] : 0;
    $trCreditosEcts = $creditosEcts > 0
        ? "<tr><td><strong>Créditos ECTS:</strong></td><td>{$creditosEcts}</td></tr>"
        : '';
    list($anyo1, $anyo2) = pgCursoAcademico();

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

// ---------------------------------------------------------------------------
// Apartado "Relación de unidades de competencia y módulos profesionales"
// ---------------------------------------------------------------------------
function pgGenerarContenidoRelacionUCModulos($db, $idMateria)
{
    $resCiclo = pgConsultar($db,
        "SELECT cic.id
           FROM materias m
           JOIN cursos cur ON m.idCurso = cur.id
           JOIN cursos_ciclos cc ON cur.id = cc.idCurso
           JOIN ciclos cic ON cc.idCiclo = cic.id
          WHERE m.id = ?
          LIMIT 1",
        array((int)$idMateria));
    if (empty($resCiclo)) {
        return '<p>No se pudo determinar el ciclo formativo asociado a esta materia.</p>';
    }
    $idCiclo = (int)$resCiclo[0]['id'];

    $unidadesCompetencia = pgConsultar($db,
        "SELECT uc.codigo, uc.texto
           FROM unidades_ciclos ucic
           JOIN unidades_competencia uc ON ucic.codigoUnidad = uc.codigo
          WHERE ucic.idCiclo = ?
          ORDER BY uc.codigo",
        array($idCiclo));
    if (empty($unidadesCompetencia)) {
        return '<p>No hay unidades de competencia asociadas al ciclo de esta materia.</p>';
    }

    $codigosUC = array();
    foreach ($unidadesCompetencia as $uc) {
        $codigosUC[] = $uc['codigo'];
    }
    $listaCodigosUC = implode(',', array_fill(0, count($codigosUC), '?'));

    $relacionesCualifUC = pgConsultar($db,
        "SELECT cu.codigoCualificacion, cu.codigoUnidad, cp.texto AS nombre_cualificacion
           FROM cualificaciones_unidades cu
           JOIN cualificaciones_profesionales cp ON cu.codigoCualificacion = cp.codigo
          WHERE cu.codigoUnidad IN ({$listaCodigosUC})
          ORDER BY cu.codigoCualificacion, cu.codigoUnidad",
        $codigosUC);

    $cualificaciones = [];
    foreach ($relacionesCualifUC as $fila) {
        $cualif = $fila['codigoCualificacion'];
        if (!isset($cualificaciones[$cualif])) {
            $cualificaciones[$cualif] = ['nombre' => $fila['nombre_cualificacion'], 'ucs' => []];
        }
        $cualificaciones[$cualif]['ucs'][] = $fila['codigoUnidad'];
    }
    if (empty($cualificaciones)) {
        return '<p>No se encontraron cualificaciones profesionales asociadas a las unidades de competencia del ciclo.</p>';
    }

    $modulosConUC = pgConsultar($db,
        "SELECT m.id, m.nombre_oficial, m.codigo_oficial, ucm.idUC
           FROM unidades_competencia_materias ucm
           JOIN materias m ON ucm.idMateria = m.id
           JOIN cursos cur ON m.idCurso = cur.id
           JOIN cursos_ciclos cc ON cur.id = cc.idCurso
          WHERE cc.idCiclo = ?
          ORDER BY m.codigo_oficial",
        array($idCiclo));

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

    $mapaUCTexto = [];
    foreach ($unidadesCompetencia as $uc) {
        $mapaUCTexto[$uc['codigo']] = $uc['texto'];
    }

    $html = '';
    foreach ($cualificaciones as $codCualif => $datos) {
        $html .= "<h3>Cualificación Profesional: {$codCualif} - {$datos['nombre']}</h3>";
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<thead><tr>
                    <th width="17%">Unidad de Competencia</th>
                    <th width="45%">Denominación</th>
                    <th width="38%">Módulos Profesionales que la incorporan</th>
                </tr></thead><tbody>';
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

// ---------------------------------------------------------------------------
// Contenidos de ESO/Bachillerato (SA)
// ---------------------------------------------------------------------------
function pgGenerarContenidosESOBACH($db, $idMateria, $aula = null)
{
    $filas = pgConsultar($db, "SELECT orden, titulo, contenidos FROM " . pgTablaAula('temas', $aula) . " WHERE idMateria = ?" . pgFiltroAula($aula) . " ORDER BY orden", array((int)$idMateria));
    if (empty($filas)) {
        return '';
    }
    $html = '';
    foreach ($filas as $fila) {
        $html .= "<br><h3>SA{$fila['orden']}. {$fila['titulo']}</h3>";
        $html .= "<p>{$fila['contenidos']}</p>";
    }
    return $html;
}

// ---------------------------------------------------------------------------
// Apartado "Secuencia de temas y distribución temporal"
// ---------------------------------------------------------------------------
function pgGenerarContenidoDistribucionTemas($db, $idMateria, $idCiclo, $aula = null)
{
    $temas = pgConsultar($db,
        "SELECT id, orden, titulo, peso_evaluacion, horas, trimestre
           FROM " . pgTablaAula('temas', $aula) . " WHERE idMateria = ?" . pgFiltroAula($aula) . "
          ORDER BY orden",
        array((int)$idMateria));
    if (empty($temas)) {
        return '<p>No hay temas definidos para esta materia.</p>';
    }

    $resultadosAprendizaje = pgConsultar($db, "SELECT id, orden, es_clave FROM " . pgTablaAula('resultados_aprendizaje', $aula) . " WHERE idMateria = ?" . pgFiltroAula($aula), array((int)$idMateria));
    $mapaRaOrden = [];
    $mapaRaEsClave = [];
    foreach ($resultadosAprendizaje as $ra) {
        $id = (int)$ra['id'];
        $mapaRaOrden[$id] = (int)$ra['orden'];
        $mapaRaEsClave[$id] = (int)$ra['es_clave'];
    }

    $idsTemas = [];
    foreach ($temas as $t) {
        $idsTemas[] = (int)$t['id'];
    }
    $listaIdsTemas = implode(',', array_fill(0, count($idsTemas), '?'));
    $relaciones = pgConsultar($db,
        "SELECT idTema, idRA, codigo FROM " . pgTablaAula('criterios_temas', $aula) . " WHERE idTema IN ({$listaIdsTemas})", $idsTemas);

    $temaRaCriterios = [];
    foreach ($relaciones as $rel) {
        $idTema = (int)$rel['idTema'];
        $idRA = (int)$rel['idRA'];
        $codigo = $rel['codigo'];
        if (!isset($temaRaCriterios[$idTema])) {
            $temaRaCriterios[$idTema] = [];
        }
        if (!isset($temaRaCriterios[$idTema][$idRA])) {
            $temaRaCriterios[$idTema][$idRA] = [];
        }
        $temaRaCriterios[$idTema][$idRA][] = $codigo;
    }

    $tituloUPSA = $idCiclo > 0 ? 'UP' : 'SA';
    $tituloRACE = $idCiclo > 0 ? 'RA' : 'Comp.';
    $tituloTemas = $idCiclo > 0 ? "Contenidos" : "Saberes básicos";
    $columnaPorcentaje = $idCiclo > 0 ? "<th width=\"9%\" align=\"center\">% Eval.</th>" : '';
    $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">
                <thead>
                    <tr nobr=\"true\">
                        <th width=\"8%\" align=\"center\">{$tituloUPSA}</th>
                        <th width=\"10%\" align=\"center\">{$tituloRACE}</th>
                        <th width=\"16%\" align=\"center\">Criterios de Evaluación</th>
                        <th width=\"40%\" align=\"center\">{$tituloTemas}</th>
                        {$columnaPorcentaje}
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

        $raDelTema = isset($temaRaCriterios[$tema['id']]) ? array_keys($temaRaCriterios[$tema['id']]) : array();
        $numRAs = count($raDelTema);

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

        usort($raDelTema, function ($a, $b) use ($mapaRaOrden) {
            $ordA = isset($mapaRaOrden[$a]) ? $mapaRaOrden[$a] : 999;
            $ordB = isset($mapaRaOrden[$b]) ? $mapaRaOrden[$b] : 999;
            return $ordA - $ordB;
        });

        for ($i = 0; $i < $numRAs; $i++) {
            $idRA = $raDelTema[$i];
            $raOrden = isset($mapaRaOrden[$idRA]) ? $mapaRaOrden[$idRA] : '?';
            $raEsClave = isset($mapaRaEsClave[$idRA]) ? $mapaRaEsClave[$idRA] : 0;
            $esClave = ($raEsClave && $idCiclo > 0) ? '*' : '';
            $raTexto = ($idCiclo > 0 ? 'RA' : 'CE') . $raOrden;
            $codigos = $temaRaCriterios[$tema['id']][$idRA];
            sort($codigos);
            $ceTexto = implode(', ', array_unique($codigos));
            if ($i === 0) {
                $tdPorcentaje = $idCiclo > 0 ? "<td width=\"9%\" align=\"center\" rowspan=\"{$numRAs}\">{$porcentaje}%</td>" : '';
                $html .= "<tr nobr=\"true\">
                            <td width=\"8%\" align=\"center\" rowspan=\"{$numRAs}\">{$orden}</td>
                            <td width=\"10%\" align=\"center\">{$raTexto}{$esClave}</td>
                            <td width=\"16%\" align=\"center\">{$ceTexto}</td>
                            <td width=\"40%\" rowspan=\"{$numRAs}\">{$titulo}</td>
                            {$tdPorcentaje}
                            <td width=\"9%\" align=\"center\" rowspan=\"{$numRAs}\">{$horas}</td>
                            <td width=\"8%\" align=\"center\" rowspan=\"{$numRAs}\">{$trimestre}</td>
                        </tr>";
            } else {
                $html .= "<tr nobr=\"true\">
                            <td width=\"10%\" align=\"center\">{$raTexto}{$esClave}</td>
                            <td width=\"16%\" align=\"center\">{$ceTexto}</td>
                        </tr>";
            }
        }
    }

    $tdTotal = $idCiclo > 0 ? "<td width=\"9%\" align=\"center\"><strong>{$totalPorcentaje}%</strong></td>" : '';
    $html .= "<tr>
                <td colspan=\"4\" align=\"right\"><strong>TOTALES:</strong></td>
                {$tdTotal}
                <td align=\"center\"><strong>{$totalHoras}</strong></td>
                <td></td>
            </tr>";
    $html .= '</tbody></table>';
    $html .= pgImprimirMensajeRAClave($resultadosAprendizaje, $idCiclo);
    if (empty($idCiclo)) {
        $html .= pgGenerarContenidosESOBACH($db, $idMateria, $aula);
    }
    return $html;
}

// ---------------------------------------------------------------------------
// Apartado "Contribución de los RA a las competencias"
// ---------------------------------------------------------------------------
function pgGenerarContenidoRACompetencias($db, $idMateria, $idCiclo, $tipoCompetencias = 1, $aula = null)
{
    $resultadosAprendizaje = pgConsultar($db, "SELECT id, orden, texto FROM " . pgTablaAula('resultados_aprendizaje', $aula) . " WHERE idMateria = ?" . pgFiltroAula($aula) . " ORDER BY orden", array((int)$idMateria));
    if (empty($resultadosAprendizaje)) {
        return '<p>No hay resultados de aprendizaje definidos para esta materia.</p>';
    }
    $mapaRA = [];
    $prefijo = $idCiclo > 0 ? 'RA' : 'CE';
    foreach ($resultadosAprendizaje as $ra) {
        $mapaRA[$ra['id']] = $prefijo . $ra['orden'];
    }
    $idsRA = array_keys($mapaRA);

    $competencias = pgConsultar($db,
        "SELECT DISTINCT cc.id, cc.codigo
           FROM competencias_ciclos cc
           JOIN " . pgTablaAula('competencias_temas', $aula) . " ct ON cc.id = ct.idCompetencia
           JOIN " . pgTablaAula('temas', $aula) . " t ON ct.idTema = t.id
          WHERE t.idMateria = ?" . pgFiltroAula($aula, 't') . " AND cc.tipo = ?
          ORDER BY cc.codigo",
        array((int)$idMateria, (int)$tipoCompetencias));
    if (empty($competencias)) {
        return '';
    }

    $idsRAList = implode(',', array_fill(0, count($idsRA), '?'));
    $criterios = pgConsultar($db, "SELECT idRA, codigo FROM " . pgTablaAula('criterios_evaluacion', $aula) . " WHERE idRA IN ({$idsRAList})", $idsRA);
    $criteriosPorRA = [];
    foreach ($criterios as $c) {
        $criteriosPorRA[$c['idRA']][] = $c['codigo'];
    }

    $codigosCriterio = [];
    foreach ($criteriosPorRA as $lista) {
        $codigosCriterio = array_merge($codigosCriterio, $lista);
    }
    $codigosCriterio = array_unique($codigosCriterio);
    $relacionRACompetencia = [];
    if (!empty($codigosCriterio)) {
        $temasPorCriterio = pgConsultar($db,
            "SELECT ce.idRA, ct.idTema
               FROM " . pgTablaAula('criterios_temas', $aula) . " ct
               JOIN " . pgTablaAula('criterios_evaluacion', $aula) . " ce ON ct.idRA = ce.idRA AND ct.codigo = ce.codigo
              WHERE ce.idRA IN ({$idsRAList})",
            $idsRA);
        $idsTemas = [];
        foreach ($temasPorCriterio as $t) {
            $idsTemas[] = (int)$t['idTema'];
        }
        $idsTemas = array_unique($idsTemas);
        if (!empty($idsTemas)) {
            $listaTemas = implode(',', array_fill(0, count($idsTemas), '?'));
            $compPorTema = pgConsultar($db,
                "SELECT ct.idTema, ct.idCompetencia FROM " . pgTablaAula('competencias_temas', $aula) . " ct WHERE ct.idTema IN ({$listaTemas})", $idsTemas);
            $temaACompetencias = [];
            foreach ($compPorTema as $fila) {
                $temaACompetencias[(int)$fila['idTema']][] = (int)$fila['idCompetencia'];
            }
            foreach ($temasPorCriterio as $t) {
                $idRA = (int)$t['idRA'];
                $idTema = (int)$t['idTema'];
                if (isset($temaACompetencias[$idTema])) {
                    foreach ($temaACompetencias[$idTema] as $idComp) {
                        $relacionRACompetencia[$idRA][$idComp] = true;
                    }
                }
            }
        }
    }

    $html = '';
    if ($idCiclo > 0) {
        $html = ($tipoCompetencias == 1) ? '<h2>Competencias profesionales</h2>' : '<h2>Competencias para la empleabilidad</h2>';
    }
    $html .= '<table border="1" cellpadding="5" cellspacing="0">';
    $html .= '<thead><tr><th align="center">Comp.</th>';
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
            $marca = isset($relacionRACompetencia[$idRA][$idComp]) ? 'X' : '';
            $html .= "<td align=\"center\">{$marca}</td>";
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table><br><br>';

    if ($tipoCompetencias == 1) {
        $listComp = pgObtenerCompetenciasProfesionales($db, $idCiclo, $idMateria);
    } else {
        $listComp = pgObtenerCompetenciasEmpleabilidad($db, $idCiclo);
    }
    foreach ($listComp as $comp) {
        $html .= "<strong>{$comp['codigo']})</strong> {$comp['texto']}<br>";
    }
    return $html;
}

// ---------------------------------------------------------------------------
// Apartado "Resultados de aprendizaje de Formación en Empresa" (FE)
// ---------------------------------------------------------------------------
function pgGenerarContenidoResultadosAprendizaje($db, $idMateria, $horasEmpresa, $aula = null)
{
    $resultados = pgConsultar($db, "SELECT * FROM " . pgTablaAula('resultados_aprendizaje', $aula) . " WHERE idMateria = ?" . pgFiltroAula($aula) . " ORDER BY orden", array((int)$idMateria));
    if (empty($resultados)) {
        // Mismo contrato que v3 (generar_apartado_ra_empresas.php)
        return array('existe' => false, 'texto' => '');
    }
    $html = "Horas destinadas a la empresa: {$horasEmpresa}<br><br>";
    $html .= "<table border=\"1\" cellpadding=\"5\">
                <thead>
                    <tr>
                        <th align=\"center\" width=\"75%\" colspan=\"2\">Resultados de aprendizaje</th>
                        <th align=\"center\" width=\"12%\">Empresa</th>
                        <th align=\"center\" width=\"13%\">Centro educativo</th>
                    </tr>
                </thead>";
    $html .= '<tbody>';
    foreach ($resultados as $ra) {
        $raNumero = 'RA' . $ra['orden'];
        $porcEmpresa = (int)$ra['porcentaje_empresa'];
        $porcCentro = 100 - $porcEmpresa;
        $html .= "<tr nobr=\"true\">
                    <td align=\"center\" width=\"10%\">{$raNumero}</td>
                    <td width=\"65%\">{$ra['texto']}</td>
                    <td align=\"center\" width=\"12%\">{$porcEmpresa}%</td>
                    <td align=\"center\" width=\"13%\">{$porcCentro}%</td>
                </tr>";
    }
    $html .= '</tbody></table>';
    return $html;
}

// ---------------------------------------------------------------------------
// Apartado "RA/CE" (Resultados de Aprendizaje / Competencias Específicas + CE)
// ---------------------------------------------------------------------------
function pgGenerarApartadoRACE($db, $idMateria, $idCiclo, $aula = null)
{
    $esCiclo = ((int)$idCiclo) > 0;
    $resultados = pgConsultar($db, "SELECT id, orden, texto FROM " . pgTablaAula('resultados_aprendizaje', $aula) . " WHERE idMateria = ?" . pgFiltroAula($aula) . " ORDER BY orden", array((int)$idMateria));
    if (empty($resultados)) {
        return '<p>No hay resultados de aprendizaje ni competencias específicas definidos para esta materia.</p>';
    }
    $idsRA = [];
    foreach ($resultados as $ra) {
        $idsRA[] = (int)$ra['id'];
    }
    $listaIdsRA = implode(',', array_fill(0, count($idsRA), '?'));
    $criterios = pgConsultar($db, "SELECT idRA, codigo, texto FROM " . pgTablaAula('criterios_evaluacion', $aula) . " WHERE idRA IN ({$listaIdsRA}) ORDER BY idRA, codigo", $idsRA);
    $criteriosPorRA = [];
    foreach ($criterios as $c) {
        $idRA = (int)$c['idRA'];
        if (!isset($criteriosPorRA[$idRA])) {
            $criteriosPorRA[$idRA] = [];
        }
        $criteriosPorRA[$idRA][] = ['codigo' => $c['codigo'], 'texto' => $c['texto']];
    }

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

// ---------------------------------------------------------------------------
// Apartado "Desarrollo de las Unidades de Programación" (temas)
// Devuelve ARRAY de HTML (uno por tema) — mismo contrato que v3.
// ---------------------------------------------------------------------------
function pgGenerarContenidoTemas($db, $idMateria, $idDepartamento, $idCiclo, $aula = null)
{
    $temas = pgObtenerTemasDeMateria($db, $idMateria, $aula);
    if (empty($temas)) {
        return array('<p>No hay temas definidos para esta materia.</p>');
    }
    $contenidosDefecto = null;
    if ($idDepartamento) {
        $contenidosDefecto = pgObtenerContenidosDefectoTema($db, $idDepartamento);
    }

    $temasHTML = [];
    foreach ($temas as $tema) {
        $html = '';
        $prefijo = $idCiclo > 0 ? 'Tema ' : 'SA';
        $html .= "<h2>{$prefijo}{$tema['orden']}: {$tema['titulo']}</h2>";
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
                $html .= '<br>';
            }
        }

        $camposConDefecto = array(
            'contexto'      => 'Contexto',
            'recursos'      => 'Recursos',
            'metodologia'   => 'Metodología',
            'adaptaciones'  => 'Adaptaciones'
        );
        foreach ($camposConDefecto as $campo => $titulo) {
            $usarDefecto = !empty($tema[$campo . '_defecto']) && $tema[$campo . '_defecto'] == 1;
            if ($usarDefecto && $contenidosDefecto && isset($contenidosDefecto[$campo])) {
                $contenido = trim($contenidosDefecto[$campo]);
            } else {
                $contenido = trim($tema[$campo]);
            }
            if (!empty($contenido)) {
                $html .= '<h3>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h3>';
                $html .= $contenido;
                $html .= '<br>';
            }
        }

        // RA y criterios
        $criteriosConRA = pgConsultar($db,
            "SELECT ce.idRA, ce.codigo, ce.texto AS criterio
               FROM " . pgTablaAula('criterios_temas', $aula) . " ct
               INNER JOIN " . pgTablaAula('criterios_evaluacion', $aula) . " ce ON ct.idRA = ce.idRA AND ct.codigo = ce.codigo
              WHERE ct.idTema = ?
              ORDER BY ce.idRA, ce.codigo",
            array((int)$tema['id']));
        if (!empty($criteriosConRA)) {
            $rasAgrupados = [];
            $pref = $idCiclo > 0 ? 'RA' : 'CE';
            foreach ($criteriosConRA as $fila) {
                $idRA = (int)$fila['idRA'];
                if (!isset($rasAgrupados[$idRA])) {
                    $raData = pgConsultar($db, "SELECT orden, texto FROM " . pgTablaAula('resultados_aprendizaje', $aula) . " WHERE id = ? AND idMateria = ?", array($idRA, (int)$idMateria));
                    $textoRA = !empty($raData)
                        ? $pref . $raData[0]['orden'] . '. ' . $raData[0]['texto']
                        : "Resultado de aprendizaje no encontrado (ID: {$idRA})";
                    $rasAgrupados[$idRA] = ['texto' => $textoRA, 'criterios' => []];
                }
                $rasAgrupados[$idRA]['criterios'][] = $fila['codigo'] . ') ' . $fila['criterio'];
            }
            $tituloRACE = $idCiclo > 0
                ? 'Resultados de Aprendizaje y Criterios de Evaluación'
                : 'Competencias Específicas y Criterios de Evaluación';
            $html .= "<h3>{$tituloRACE}</h3>";
            foreach ($rasAgrupados as $datos) {
                $html .= "<p><strong>{$datos['texto']}</strong></p>";
                $html .= '<ul style="list-style: none; padding-left: 0;">';
                foreach ($datos['criterios'] as $criterio) {
                    $html .= "<li>{$criterio}</li>";
                }
                $html .= '</ul>';
            }
        } else {
            $html .= '<p>No hay resultados de aprendizaje ni criterios de evaluación asociados a este tema.</p>';
        }

        // Competencias
        $competencias = pgObtenerCompetenciasDeTema($db, $tema['id'], $aula);
        if (!empty($competencias)) {
            $tituloCompetencias = $idCiclo > 0
                ? 'Competencias profesionales y para la empleabilidad'
                : 'Competencias clave';
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

// ---------------------------------------------------------------------------
// Despacho del contenido predefinido según el tipo de apartado
// ---------------------------------------------------------------------------
function pgGenerarApartadoPredefinido($db, $tipo, $idMateria, $idCiclo, $idDepartamento, $profesores = [], $horasEmpresa = 0, $aula = null)
{
    if (empty($tipo) || empty($idMateria) || empty($idDepartamento)) {
        return '';
    }
    if (empty($profesores)) {
        $profesores = pgObtenerProfesoresMateria($db, $idMateria);
    }
    $idMateria = (int)$idMateria;
    $idDepartamento = (int)$idDepartamento;

    switch ($tipo) {
        case PG_TIPO_APARTADO_CONTEXTO:
            return pgGenerarContenidoContexto($db, $idMateria, $idCiclo, $profesores);
        case PG_TIPO_APARTADO_RELACION_UC_MODULOS:
            return pgGenerarContenidoRelacionUCModulos($db, $idMateria);
        case PG_TIPO_APARTADO_RELACION_RA_COMPETENCIAS:
            if (empty($idCiclo)) {
                return pgGenerarContenidoRACompetencias($db, $idMateria, $idCiclo, 1, $aula);
            }
            return pgGenerarContenidoRACompetencias($db, $idMateria, $idCiclo, 1, $aula) .
                   pgGenerarContenidoRACompetencias($db, $idMateria, $idCiclo, 2, $aula);
        case PG_TIPO_APARTADO_SECUENCIA_TEMAS:
            return pgGenerarContenidoDistribucionTemas($db, $idMateria, $idCiclo, $aula);
        case PG_TIPO_APARTADO_FE:
            return pgGenerarContenidoResultadosAprendizaje($db, $idMateria, $horasEmpresa, $aula);
        case PG_TIPO_APARTADO_RA_CE:
            return pgGenerarApartadoRACE($db, $idMateria, $idCiclo, $aula);
        case PG_TIPO_APARTADO_EVALUACION_RA:
            return pgGenerarContenidoEvaluacionAprendizaje($db, $idMateria, $idCiclo, $aula);
        case PG_TIPO_APARTADO_TEMAS:
            return pgGenerarContenidoTemas($db, $idMateria, $idDepartamento, $idCiclo, $aula);
        default:
            return '';
    }
}
?>
