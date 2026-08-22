<?php
// ============================================================================
// Genera el PDF de "Resultados de aprendizaje y coordinación con empresas"
// ============================================================================
//
// Endpoint autocontenido (solicitud de navegador directa, sin sesión) que
// replica las tres vistas de solo lectura de v3, generadas ahora en PDF con
// TCPDF (misma librería que usa pdf_acta/pdf_seleccion/pccf):
//
//   - "resumen" : v3 resultados_aprendizaje_vista_previa.php
//                (resumen general con % de empresa y totales de horas)
//   - "ra"      : v3 resultados_aprendizaje_vista_previa_empresa.php
//                (RAs con formación en empresa, por módulo abreviado)
//   - "ce"      : v3 criterios_evaluacion_vista_previa_empresa.php
//                (CE de RA con formación en empresa, asociados a temas)
//
// Uso:
//   .../pdf_resultados_aprendizaje.php?modo=resumen
//   .../pdf_resultados_aprendizaje.php?modo=ra
//   .../pdf_resultados_aprendizaje.php?modo=ce
//
// PHP 5 compatible (suelo efectivo 5.4 por los literales []).

header('Content-Type: application/pdf; charset=utf-8');

require_once 'config.php';
require_once 'lib/php/tcpdf/examples/tcpdf_include.php';
require_once 'lib/php/tcpdf/tcpdf.php';

// ============================================================================
// Cabecera y pie de página (misma que usa el resto de PDFs de la app)
// ============================================================================
class MiPDFResultados extends TCPDF
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
// Consultas a la base de datos (patrón mysqli de v4)
// ============================================================================
function consultar($db, $sql, $params = array(), $types = 'i')
{
    $stmt = mysqli_prepare($db, $sql);
    if ($stmt === false) {
        throw new Exception('Error preparando la consulta: ' . mysqli_error($db));
    }
    if (!empty($params)) {
        // Compatible con PHP 5: sin "..." (PHP 5.6+); se pasan los valores por copia,
        // que es suficiente porque se ejecuta la sentencia enseguida con los mismos.
        $args = array($stmt, $types);
        foreach ($params as $p) {
            $args[] = $p;
        }
        call_user_func_array('mysqli_stmt_bind_param', $args);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// Ciclos de la familia de Informática (misma consulta que las vistas de v3)
function obtenerCiclos($db)
{
    return consultar($db,
        "SELECT * FROM ciclos WHERE familia LIKE '%Informática%' AND nivel LIKE '%Ciclo Formativo%' ORDER BY nombre");
}

// Cursos asociados a un ciclo
function obtenerCursosCiclo($db, $idCiclo)
{
    return consultar($db,
        "SELECT cursos.id, cursos.nombre FROM cursos, cursos_ciclos
         WHERE cursos.id = cursos_ciclos.idCurso AND cursos_ciclos.idCiclo = ?
         ORDER BY cursos_ciclos.orden", array($idCiclo), 'i');
}

// Materias del curso con docencia en empresa (misma consulta que las vistas de v3)
function obtenerMateriasCurso($db, $idCurso)
{
    return consultar($db,
        "SELECT DISTINCT materias.id, materias.nombre_oficial, materias.horas_empresa
         FROM materias
         WHERE (idDepartamento = 1 OR idDepartamento = 2 OR idDepartamento = 8)
           AND (materias.idCurso = ? AND materias.horas_empresa > 0)", array($idCurso), 'i');
}

// Resultados de aprendizaje de una materia
function obtenerRA($db, $idMateria)
{
    return consultar($db, "SELECT * FROM resultados_aprendizaje WHERE idMateria = ? ORDER BY orden", array($idMateria), 'i');
}

// Criterios de evaluación de una materia: solo los de RA con % de empresa
// asociados a temas de la propia materia (misma consulta que la vista de v3)
function obtenerCE($db, $idMateria)
{
    $sql =
        "SELECT r.orden AS ra_orden, c.codigo AS ce_codigo, c.texto
         FROM criterios_evaluacion c
         INNER JOIN resultados_aprendizaje r ON c.idRA = r.id
         WHERE r.idMateria = ? AND r.porcentaje_empresa > 0
           AND EXISTS (
               SELECT 1 FROM criterios_temas ct
               INNER JOIN temas t ON t.id = ct.idTema
               WHERE ct.idRA = c.idRA AND ct.codigo = c.codigo AND t.idMateria = r.idMateria
           )
         ORDER BY r.orden, c.codigo";
    return consultar($db, $sql, array($idMateria), 'i');
}

// ============================================================================
// Acronimo de un módulo (equivalente PHP 5 de obtenerAcronimo de v3)
// ============================================================================
function obtenerAcronimo($texto)
{
    if ($texto == 'Programación') return 'PRO';
    if (strpos($texto, 'Inglés') !== false) return 'ING';

    $excluirOriginal = array('de', 'del', 'la', 'las', 'el', 'los', 'en', 'y', 'a', 'al', 'e', 'para', 'GM', '(GM)', 'GS', '(GS)', 'aplicada');
    $excluir = array();
    foreach ($excluirOriginal as $palabra) {
        $excluir[] = strtolower($palabra);
    }

    $palabras = preg_split('/[\s-]+/', strtolower($texto));
    $acronimo = '';
    foreach ($palabras as $palabra) {
        if (!in_array($palabra, $excluir) && !empty($palabra)) {
            // mb_substr si mbstring está disponible (como en v3); no lo está, ASCII
            $letra = function_exists('mb_substr') ? mb_substr($palabra, 0, 1) : substr($palabra, 0, 1);
            $acronimo .= $letra;
        }
    }
    return strtoupper($acronimo);
}

// ============================================================================
// Escapado y HTML común
// ============================================================================
function e($x)
{
    return htmlentities((string)$x, ENT_QUOTES, 'UTF-8');
}

// Título y subtítulo comunes a las tres vistas de v3
function cabeceraVista($pdf, $titulo, $nota)
{
    $html = '<h1 style="margin-top:5px;">' . e($titulo) . '</h1>'
        . '<h2 style="font-size:13pt; margin-bottom:5px;">Listado por ciclo, curso y materia</h2>';
    if ($nota !== '') {
        $html .= '<p><em>' . e($nota) . '</em></p>';
    }
    $pdf->writeHTML($html, true);
}

// ============================================================================
// Modo "resumen": v3 resultados_aprendizaje_vista_previa.php
// ============================================================================
function generarPDFResumen($db, $pdf)
{
    cabeceraVista($pdf, 'Resultados de aprendizaje y coordinación con empresas',
        'NOTA: se recomienda que cada módulo ceda entre un 10% y un 20% de sus resultados de aprendizaje a la empresa. ' .
        'Se marcan con * los porcentajes de los módulos que no lo cumplan, a modo orientativo.');

    $ciclos = obtenerCiclos($db);
    foreach ($ciclos as $ciclo) {
        $totalHorasCiclo = 0;
        $html = '<h2 style="font-size:15pt; color:#008000; border-bottom:1px solid #000000; margin-top:12px;">' . e($ciclo['nombre']) . '</h2>';

        $cursos = obtenerCursosCiclo($db, $ciclo['id']);
        foreach ($cursos as $curso) {
            $horasCurso = 0;
            $html .= '<h3 style="color:#000099; margin-top:10px;">' . e($curso['nombre']) . '</h3>';

            $materias = obtenerMateriasCurso($db, $curso['id']);
            foreach ($materias as $materia) {
                $horasCurso += $materia['horas_empresa'];
                $html .= '<h4>' . e($materia['nombre_oficial']) . ' (' . $materia['horas_empresa'] . ' h. en la empresa)</h4>';

                $totalPorcentaje = 0;
                $totalResultados = 0;
                $html .= '<blockquote style="margin-left:15px;">';
                $html .= '<ul>';
                $ras = obtenerRA($db, $materia['id']);
                foreach ($ras as $ra) {
                    $totalResultados++;
                    $totalPorcentaje += $ra['porcentaje_empresa'];
                    $html .= '<li>RA' . $ra['orden'] . '. ' . e($ra['texto']) . ' (<em>' . $ra['porcentaje_empresa'] . '% empresa</em>)</li>';
                }
                $html .= '</ul>';

                $media = ($totalResultados == 0) ? 0 : ($totalPorcentaje / $totalResultados);
                $asterisco = ($media >= 10 && $media <= 20) ? '' : '*';
                $html .= '<p>Porcentaje de RA asignado a empresa: ' . round($media, 2) . '% ' . $asterisco . '</p>';
                $html .= '</blockquote>';
            }

            $html .= '<p style="color:#990000;"><strong>TOTAL HORAS EMPRESA ' . e($curso['nombre']) . ': ' . $horasCurso . ' h.</strong></p>';
            $totalHorasCiclo += $horasCurso;
        }

        $html .= '<p style="color:#990000;"><strong>TOTAL HORAS EMPRESA EN EL CICLO: ' . $totalHorasCiclo . ' h.</strong></p>';
        $pdf->writeHTML($html, true);
    }
}

// ============================================================================
// Modo "ra": v3 resultados_aprendizaje_vista_previa_empresa.php
// ============================================================================
function generarPDFRA($db, $pdf)
{
    cabeceraVista($pdf, 'Resultados de Aprendizaje con Formación en Empresa', '');

    $ciclos = obtenerCiclos($db);
    foreach ($ciclos as $ciclo) {
        $totalHorasCiclo = 0;
        $html = '<h2 style="font-size:15pt; color:#008000; border-bottom:1px solid #000000; margin-top:12px;">' . e($ciclo['nombre']) . '</h2>';

        $cursos = obtenerCursosCiclo($db, $ciclo['id']);
        foreach ($cursos as $curso) {
            $horasCurso = 0;
            $html .= '<h3 style="color:#000099; margin-top:10px;">' . e($curso['nombre']) . '</h3>';

            $materias = obtenerMateriasCurso($db, $curso['id']);
            foreach ($materias as $materia) {
                $horasCurso += $materia['horas_empresa'];
                $modulo = obtenerAcronimo($materia['nombre_oficial']);
                $html .= '<h4>' . e($materia['nombre_oficial']) . ' (' . $materia['horas_empresa'] . ' h. en la empresa)</h4>';

                $html .= '<ul>';
                $ras = obtenerRA($db, $materia['id']);
                foreach ($ras as $ra) {
                    // En v3 solo se listan los que tienen porcentaje asignado a empresa
                    if (!empty($ra['porcentaje_empresa'])) {
                        $html .= '<li><strong>' . $modulo . ' - RA' . $ra['orden'] . '</strong>: ' . e($ra['texto']) . '</li>';
                    }
                }
                $html .= '</ul>';
            }

            $html .= '<p style="color:#990000;"><strong>TOTAL HORAS EMPRESA ' . e($curso['nombre']) . ': ' . $horasCurso . ' h.</strong></p>';
            $totalHorasCiclo += $horasCurso;
        }

        $html .= '<p style="color:#990000;"><strong>TOTAL HORAS EMPRESA EN EL CICLO: ' . $totalHorasCiclo . ' h.</strong></p>';
        $pdf->writeHTML($html, true);
    }
}

// ============================================================================
// Modo "ce": v3 criterios_evaluacion_vista_previa_empresa.php
// ============================================================================
function generarPDFCE($db, $pdf)
{
    cabeceraVista($pdf, 'Criterios de Evaluación de RA con Formación en Empresa', '');

    $ciclos = obtenerCiclos($db);
    foreach ($ciclos as $ciclo) {
        $html = '<h2 style="font-size:15pt; color:#008000; border-bottom:1px solid #000000; margin-top:12px;">' . e($ciclo['nombre']) . '</h2>';

        $cursos = obtenerCursosCiclo($db, $ciclo['id']);
        foreach ($cursos as $curso) {
            $html .= '<h3 style="color:#000099; margin-top:10px;">' . e($curso['nombre']) . '</h3>';

            $materias = obtenerMateriasCurso($db, $curso['id']);
            foreach ($materias as $materia) {
                $modulo = obtenerAcronimo($materia['nombre_oficial']);
                $html .= '<h4>' . e($materia['nombre_oficial']) . ' (' . $materia['horas_empresa'] . ' h. en la empresa)</h4>';

                $html .= '<ul>';
                $ces = obtenerCE($db, $materia['id']);
                foreach ($ces as $ce) {
                    $html .= '<li><strong>' . $modulo . ' - RA' . $ce['ra_orden'] . ' - CE' . e($ce['ce_codigo']) . '</strong>: ' . e($ce['texto']) . '</li>';
                }
                $html .= '</ul>';
            }
        }

        $pdf->writeHTML($html, true);
    }
}

// ============================================================================
// Punto de entrada
// ============================================================================
$modo = isset($_GET['modo']) ? $_GET['modo'] : '';
if ($modo !== 'resumen' && $modo !== 'ra' && $modo !== 'ce') {
    $modo = 'resumen';
}

$db = getDBConnection();
if (!$db) {
    die('Error de conexión a la base de datos');
}

try {
    $pdf = new MiPDFResultados();
    $pdf->SetCreator('GestionIES v4');
    $pdf->SetMargins(15, 20, 15, '');
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    if ($modo === 'ra') {
        generarPDFRA($db, $pdf);
    } elseif ($modo === 'ce') {
        generarPDFCE($db, $pdf);
    } else {
        generarPDFResumen($db, $pdf);
    }

    $pdf->Output('ResultadosAprendizaje_' . $modo . '.pdf', 'I');
    exit;
} catch (Exception $e) {
    $pdf = new MiPDFResultados();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->writeHTML('<p style="color: red; padding: 20px;">Error: ' . e($e->getMessage()) . '</p>');
    $pdf->Output();
    exit;
}
?>
