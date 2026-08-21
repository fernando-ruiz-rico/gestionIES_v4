<?php
// ============================================================================
// Genera un PDF del PCCF (Proyecto Curricular de Ciclo Formativo)
// ============================================================================
//
// Endpoint autocontenido que replica la generación de PDF de v3
// (pdf_pccf.php), adaptado a la arquitectura de v4 (mysqli + config.php).
// Es una solicitud de navegador directa, sin dependencias de sesión.
//
// Modo de operación (parámetro `modo`):
//   - "completo" : todos los apartados del ciclo
//                  (portada + un apartado por página + índice)
//   - "apartado" : un solo apartado (idApartado obligatorio)
//
// Uso:
//   .../api/pccf/generar.php?modo=completo&idCiclo=<id>
//   .../api/pccf/generar.php?modo=apartado&idCiclo=<id>&idApartado=<id>

header('Content-Type: application/pdf; charset=utf-8');

require_once '../../config.php';
require_once __DIR__ . '/../../../frontend/lib/php/tcpdf/examples/tcpdf_include.php';
require_once __DIR__ . '/../../../frontend/lib/php/tcpdf/tcpdf.php';

// ============================================================================
// Consultas a la base de datos (patrón mysqli de v4)
// ============================================================================
function consultar($db, $sql, $params = [], $types = 'i')
{
    $stmt = mysqli_prepare($db, $sql);
    if ($stmt === false) {
        throw new Exception('Error preparando la consulta: ' . mysqli_error($db));
    }
    if (!empty($params)) {
        $refs = [];
        foreach ($params as $i => $p) {
            $refs[] = &$params[$i];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$refs);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function consultarUna($db, $sql, $params = [], $types = 'i')
{
    $res = consultar($db, $sql, $params, $types);
    return !empty($res) ? $res[0] : null;
}

// ============================================================================
// Datos de consulta
// ============================================================================
function obtenerDatosCiclo($db, $idCiclo)
{
    return consultarUna($db, "SELECT * FROM ciclos WHERE id = ?", [$idCiclo], 'i');
}

function obtenerIdDepartamentoDeCiclo($db, $idCiclo)
{
    $res = consultarUna($db,
        "SELECT m.idDepartamento FROM materias m
         JOIN cursos c ON m.idCurso = c.id
         JOIN cursos_ciclos cc ON c.id = cc.idCurso
         WHERE cc.idCiclo = ? AND m.idDepartamento IS NOT NULL LIMIT 1",
        [$idCiclo], 'i');
    return $res ? (int)$res['idDepartamento'] : 0;
}

function obtenerContenidoApartadoPccf($db, $idApartado, $idCiclo, $idDepartamento = 0)
{
    // Contenido personalizado
    $row = consultarUna($db,
        "SELECT texto FROM contenidos_pccf WHERE idApartado = ? AND idCiclo = ?",
        [$idApartado, $idCiclo], 'ii');
    if ($row && trim($row['texto']) !== '') {
        return $row['texto'];
    }
    // Contenido por defecto del departamento
    $row = consultarUna($db,
        "SELECT texto FROM contenidos_defecto_pccf WHERE idApartado = ? AND idDepartamento = ?",
        [$idApartado, $idDepartamento], 'ii');
    if ($row && trim($row['texto']) !== '') {
        return $row['texto'];
    }
    return '';
}

function obtenerApartados($db)
{
    return consultar($db, "SELECT * FROM apartados_pccf ORDER BY orden");
}

function obtenerCompetenciasProfesionalesPccf($db, $idCiclo)
{
    return consultar($db,
        "SELECT DISTINCT codigo, texto, orden FROM competencias_ciclos WHERE idCiclo = ? AND tipo = 1 ORDER BY orden",
        [$idCiclo], 'i');
}

function obtenerCompetenciasEmpleabilidad($db, $idCiclo)
{
    return consultar($db,
        "SELECT codigo, texto FROM competencias_ciclos WHERE idCiclo = ? AND tipo = '2' ORDER BY orden",
        [$idCiclo], 'i');
}

function obtenerMateriasCiclo($db, $idCiclo)
{
    return consultar($db,
        "SELECT m.id, m.nombre_oficial, m.horas_empresa, m.codigo_oficial, m.tipo
         FROM materias m
         INNER JOIN cursos c ON m.idCurso = c.id
         INNER JOIN cursos_ciclos cc ON c.id = cc.idCurso
         WHERE cc.idCiclo = ? AND m.tipo != 'TUTORIA'
         ORDER BY cc.orden, m.tipo",
        [$idCiclo], 'i');
}

function obtenerResultadosAprendizaje($db, $idMateria)
{
    return consultar($db, "SELECT * FROM resultados_aprendizaje WHERE idMateria = ?", [$idMateria], 'i');
}

// ============================================================================
// Genera el contenido HTML de cada apartado predefinido según su tipo
// ============================================================================
function generarContenidoIdentificacion($db, $idCiclo, $ciclo)
{
    list($anyo1, $anyo2) = cursoActual();
    $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">
                <tr><td width=\"35%\"><strong>Centro:</strong></td><td width=\"65%\">IES San Vicente</td></tr>
                <tr><td><strong>Familia Profesional:</strong></td><td>"
        . (isset($ciclo['familia']) ? $ciclo['familia'] : '') . "</td></tr>
                <tr><td><strong>Nivel:</strong></td><td>"
        . (isset($ciclo['nivel']) ? $ciclo['nivel'] : '') . "</td></tr>
                <tr><td><strong>Ciclo Formativo:</strong></td><td>"
        . (isset($ciclo['nombre']) ? $ciclo['nombre'] : '') . "</td></tr>
                <tr><td><strong>Horas:</strong></td><td>"
        . (isset($ciclo['horas']) ? $ciclo['horas'] : '') . "</td></tr>
                <tr><td><strong>Curso académico:</strong></td><td>"
        . $anyo1 . "/" . $anyo2 . "</td></tr>
            </table>";
    return $html;
}

function generarApartadoCompetenciasModulos($db, $idCiclo, $tipo)
{
    // Módulos del ciclo
    $materias = [];
    foreach (obtenerMateriasCiclo($db, $idCiclo) as $m) {
        if ($m['tipo'] === 'TUTORIA' || empty($m['codigo_oficial'])) {
            continue;
        }
        $materias[] = $m;
    }

    // Competencias del tipo indicado
    $sqlComp = $tipo === 1
        ? "SELECT DISTINCT codigo, texto FROM competencias_ciclos WHERE idCiclo = ? AND tipo = 1 ORDER BY orden"
        : "SELECT codigo, texto FROM competencias_ciclos WHERE idCiclo = ? AND tipo = '2' ORDER BY orden";
    $competencias = consultar($db, $sqlComp, [$idCiclo], 'i');

    $html = '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
    $html .= '<thead><tr><th>Módulo</th><th>Competencias</th></tr></thead><tbody>';
    foreach ($materias as $m) {
        $html .= '<tr><td>' . $m['nombre_oficial'] . '</td><td>';
        foreach ($competencias as $c) {
            $html .= $c['codigo'] . ' - ' . $c['texto'] . '<br>';
        }
        $html .= '</td></tr>';
    }
    $html .= '</tbody></table>';

    return $html;
}

function generarApartadoDistribicionModulos($db, $idCiclo)
{
    $html = '<table border="1" cellpadding="5" cellspacing="0" width="100%">'
        . '<thead><tr><th>Módulo</th><th>Código oficial</th></tr></thead><tbody>';
    foreach (obtenerMateriasCiclo($db, $idCiclo) as $m) {
        if (empty($m['nombre_oficial'])) {
            continue;
        }
        $html .= '<tr><td>' . $m['nombre_oficial'] . '</td><td>' . $m['codigo_oficial'] . '</td></tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

function generarContenidoResultadosAprendizajeEmpresa($db, $idCiclo)
{
    $html = '';
    foreach (obtenerMateriasCiclo($db, $idCiclo) as $modulo) {
        if (empty($modulo['nombre_oficial'])) {
            continue;
        }
        $html .= "<h3>" . $modulo['nombre_oficial'] . "</h3><br>";
        $resultados = obtenerResultadosAprendizaje($db, $modulo['id']);
        if (!empty($resultados)) {
            $html .= "<table border=\"1\" cellpadding=\"5\">";
            foreach ($resultados as $ra) {
                $html .= "<tr><td>" . $ra['orden'] . "</td><td>" . $ra['texto'] . "</td></tr>";
            }
            $html .= '</table><br>';
        } else {
            $html .= '<p>Sin resultados de aprendizaje definidos.</p><br>';
        }
    }
    return $html;
}

function generarApartadoPredefinido($db, $tipo, $idCiclo, $ciclo)
{
    if (empty($idCiclo) || empty($tipo)) {
        return '';
    }
    switch ($tipo) {
        case 1: // Identificación
            return generarContenidoIdentificacion($db, $idCiclo, $ciclo);
        case 4: // Competencias profesionales
            returngenerarApartadoCompetenciasModulos($db, $idCiclo, 1);
        case 5: // Competencias de empleabilidad
            return generarApartadoCompetenciasModulos($db, $idCiclo, 2);
        case 7: // Distribución de módulos
            return generarApartadoDistribicionModulos($db, $idCiclo);
        case 101: // RA de formación en empresa
            return generarContenidoResultadosAprendizajeEmpresa($db, $idCiclo);
        default:
            return '';
    }
}

// ============================================================================
// Clase personalizada de TCPDF
// ============================================================================
class MiPDF extends TCPDF
{
    public function Header()
    {
        $this->setY(15);
        $this->SetFont('helvetica', 'I', 12);
        $this->Cell(0, 10, "I.E.S. San Vicente", 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 10, 'Pág ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// ============================================================================
// Agrega un apartado al PDF (portada, título y contenido)
// ============================================================================
function agregarApartadoAlPDF($pdf, &$contadorPrincipal, &$contadorSecundario, $apartado, $contenido)
{
    $esSubartado = (bool)$apartado['subapartado'];
    $titulo = $apartado['titulo'];

    if (!$esSubartado) {
        $contadorSecundario = 0;
        $contadorPrincipal++;
        $pdf->AddPage();
        $pdf->Bookmark($contadorPrincipal . '. ' . $titulo, 0, 0, '', '');
        $pdf->WriteHTML('<h1>' . $contadorPrincipal . '. ' . $titulo . '</h1><br>', true, false, true, false, '');
    } else {
        $contadorSecundario++;
        $pdf->Bookmark("     $contadorPrincipal.$contadorSecundario. $titulo", 0, 0, '', '');
        $pdf->WriteHTML('<br><h2>' . $contadorPrincipal . '.' . $contadorSecundario . '. ' . $titulo . '</h2><br>', true, false, true, false, '');
    }

    if (!empty($contenido)) {
        $pdf->writeHTML($contenido, true, false, true, false, '');
    }
}

// ============================================================================
// Genera el PDF completo del ciclo
// ============================================================================
function generarPDFPccf($db, $idCiclo)
{
    $datosCiclo = obtenerDatosCiclo($db, $idCiclo);
    $nivelCiclo = $datosCiclo['nivel'];
    $idDepartamento = obtenerIdDepartamentoDeCiclo($db, $idCiclo);
    list($anyo1, $anyo2) = cursoActual();

    $pdf = new MiPDF();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle("PCCF {$datosCiclo['nombre']} ($anyo1/$anyo2)");
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Portada
    $pdf->AddPage();
    $pdf->Write(0, str_repeat(PHP_EOL, 10), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 30);
    $pdf->Write(0, $datosCiclo['nombre'] . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 25);
    $pdf->Write(0, 'Proyecto Curricular de Ciclo Formativo' . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Curso: $anyo1/$anyo2" . str_repeat(PHP_EOL, 3), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 12);

    // Contenidos
    $apartados = obtenerApartados($db);
    $contadorPrincipal = 0;
    $contadorSecundario = 0;

    foreach ($apartados as $apartado) {
        $id = (int)$apartado['id'];
        $tipo = (int)$apartado['tipo'];
        $requerido = (bool)$apartado['requerido'];

        if ($tipo == 0) { // EDITABLE
            if ($id == 12 && (strpos($nivelCiclo, 'Básico') !== false || strpos($nivelCiclo, 'Especialización') !== false)) {
                continue;
            }
            if ($id == 19 && strpos($nivelCiclo, 'Especialización') !== false) {
                continue;
            }
            $contenido = obtenerContenidoApartadoPccf($db, $id, $idCiclo, $idDepartamento);
        } else { // PREDEFINIDO
            $contenido = generarApartadoPredefinido($db, $tipo, $idCiclo, $datosCiclo);
        }

        if (!empty($contenido) || $requerido) {
            agregarApartadoAlPDF($pdf, $contadorPrincipal, $contadorSecundario, $apartado, $contenido);
        }
    }

    // Índice
    $pdf->addTOCPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->MultiCell(0, 0, 'Índice de contenidos', 0, 'C', 0, 1, '', '', true, 0);
    $pdf->Ln();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->addTOC(2);
    $pdf->endTOCPage();

    $pdf->Output();
}

// ============================================================================
// Genera el PDF de un solo apartado
// ============================================================================
function generarPDFPccfApartado($db, $idCiclo, $idApartado)
{
    $datosCiclo = obtenerDatosCiclo($db, $idCiclo);
    $apartado = consultarUna($db, "SELECT * FROM apartados_pccf WHERE id = ?", [$idApartado], 'i');
    if (!$apartado) {
        throw new Exception('Apartado no encontrado');
    }

    $pdf = new MiPDF();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle("PCCF - " . $apartado['titulo']);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $idDepartamento = obtenerIdDepartamentoDeCiclo($db, $idCiclo);
    $tipo = (int)$apartado['tipo'];

    if ($tipo == 0) {
        $contenido = obtenerContenidoApartadoPccf($db, $idApartado, $idCiclo, $idDepartamento);
    } else {
        $contenido = generarApartadoPredefinido($db, $tipo, $idCiclo, $datosCiclo);
    }

    if (!empty($contenido)) {
        $pdf->AddPage();
        $pdf->writeHTML($contenido, true, false, true, false, '');
    }

    $pdf->Output();
}

// ============================================================================
// Punto de entrada principal
// ============================================================================
try {
    $db = getDBConnection();
    if (!$db) {
        throw new Exception('Error de conexión a la base de datos');
    }

    $modo = isset($_GET['modo']) && $_GET['modo'] === 'apartado' ? 'apartado' : 'completo';
    $idCiclo = isset($_GET['idCiclo']) ? intval($_GET['idCiclo']) : 0;
    $idApartado = isset($_GET['idApartado']) ? intval($_GET['idApartado']) : 0;

    if ($idCiclo <= 0) {
        throw new Exception('Ciclo no válido');
    }

    if ($modo === 'apartado') {
        if ($idApartado <= 0) {
            throw new Exception('Apartado no válido');
        }
        generarPDFPccfApartado($db, $idCiclo, $idApartado);
    } else {
        generarPDFPccf($db, $idCiclo);
    }
} catch (Exception $e) {
    // En caso de error, mostramos el mensaje en un PDF de error.
    $pdf = new MiPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->writeHTML('<p style="color: red; padding: 20px;">Error: ' . $e->getMessage() . '</p>');
    $pdf->Output();
    exit;
}