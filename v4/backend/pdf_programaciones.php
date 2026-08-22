<?php
// ============================================================================
// Genera el PDF COMPLETO de la programación de una materia (Fase 2.1)
// ============================================================================
//
// Endpoint autocontenido (solicitud de navegador directa, sin sesión) que
// replica v3/pdf_programaciones.php sobre la base de datos de v4.
//
// Uso:
//   .../pdf_programaciones.php?idMateria=<id>
//
// Portada + apartados (editables con su contenido, predefinidos generados al
// vuelo) + índice. PHP 5 compatible (suelo efectivo 5.4).

header('Content-Type: application/pdf; charset=utf-8');

require_once 'config.php';
require_once 'lib/php/tcpdf/examples/tcpdf_include.php';
require_once 'lib/php/tcpdf/tcpdf.php';
require_once 'lib/programaciones_pdf.php';

// ---------------------------------------------------------------------------
// Agrega un apartado al PDF (fiel a v3/pdf_programaciones.php)
// ---------------------------------------------------------------------------
function pgAgregarApartadoAlPDF($pdf, &$contadorPrincipal, &$contadorSecundario, $apartado, $contenido, $tipo)
{
    $esSubapartado = (bool)$apartado['subapartado'];
    $titulo = $apartado['titulo'];

    if (!$esSubapartado) {
        $contadorSecundario = 0;
        $contadorPrincipal++;
        $pdf->AddPage();
        $pdf->Bookmark($contadorPrincipal . '. ' . $titulo, 0, 0, '', '');
        $pdf->WriteHTML('<h1>' . $contadorPrincipal . '. ' . $titulo . '</h1><br>', true, false, true, false, '');
    } else {
        $contadorSecundario++;
        // El apartado de Resultados de Aprendizaje en FE empieza en nueva página
        if ($tipo == PG_TIPO_APARTADO_FE) {
            $pdf->AddPage();
        }
        $pdf->Bookmark("     $contadorPrincipal.$contadorSecundario. $titulo", 0, 0, '', '');
        $pdf->WriteHTML('<br><h2>' . $contadorPrincipal . '.' . $contadorSecundario . '. ' . $titulo . '</h2><br>', true, false, true, false, '');
    }

    if (!empty($contenido)) {
        if ($tipo == PG_TIPO_APARTADO_TEMAS && is_array($contenido)) {
            $totalTemas = count($contenido);
            for ($i = 0; $i < $totalTemas; $i++) {
                if ($i > 0) {
                    // Añadir página nueva antes del segundo tema, tercero, etc.
                    $pdf->AddPage();
                }
                $pdf->writeHTML($contenido[$i], true, false, true, false, '');
            }
        } else {
            // Apartado normal (no temas)
            $pdf->writeHTML($contenido, true, false, true, false, '');
        }
    }
}

// ---------------------------------------------------------------------------
// Genera el PDF completo
// ---------------------------------------------------------------------------
function pgGenerarPDFProgramacion($db, $idMateria)
{
    // 1. Datos básicos
    $datos = pgObtenerDatosMateria($db, $idMateria);
    if (!$datos) {
        die('Materia no encontrada.');
    }

    $curso = $datos['curso'];
    $materia = $datos['materia'];
    $horasEmpresa = $datos['horas_empresa'];
    $idDepartamento = $datos['id_departamento'];
    $departamento = $datos['departamento'];
    $categoria = !empty($datos['categoria']) ? $datos['categoria'] : '';
    $idCiclo = pgObtenerIdCicloPorMateria($db, $idMateria);

    // 2. Profesores y curso académico
    $profesores = pgObtenerProfesoresMateria($db, $idMateria);
    list($anyo1, $anyo2) = pgCursoAcademico();

    // 3. Inicializar PDF
    $pdf = new MiPDFProgramaciones();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle($materia . " (" . $curso . ")");
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // 4. Portada
    $pdf->AddPage();
    $pdf->Write(0, str_repeat(PHP_EOL, 3), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 30);
    $pdf->Write(0, $materia . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, $curso . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 16);
    $titulo = $idCiclo ? 'Programación didáctica' : 'Propuesta pedagógica';
    $pdf->Write(0, $titulo . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Curso: $anyo1/$anyo2" . str_repeat(PHP_EOL, 3), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Departamento de " . $departamento . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', 'I', 12);
    if ($idCiclo > 0) {
        foreach ($profesores as $prof) {
            $pdf->Write(0, $prof . PHP_EOL, '', 0, 'C', true, 0, false, false, 0);
        }
    }
    $pdf->SetFont('helvetica', '', 12);

    // 5. Contenidos
    $apartados = pgObtenerApartadosProgramacion($db, $categoria);
    $contadorPrincipal = 0;
    $contadorSecundario = 0;

    foreach ($apartados as $apartado) {
        $id = (int)$apartado['id'];
        $tipo = (int)$apartado['tipo'];
        $requerido = (bool)$apartado['requerido'];

        if ($tipo == PG_TIPO_APARTADO_EDITABLE) {
            // Apartado editable
            $contenido = pgObtenerContenidoApartado($db, $id, $idMateria, $idDepartamento);
        } else {
            // Apartado predefinido
            $contenido = pgGenerarApartadoPredefinido($db, $tipo, $idMateria, $idCiclo, $idDepartamento, $profesores, $horasEmpresa);
        }

        if (!empty($contenido) || $requerido) {
            pgAgregarApartadoAlPDF($pdf, $contadorPrincipal, $contadorSecundario, $apartado, $contenido, $tipo);
        }
    }

    // 6. Índice
    $pdf->addTOCPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->MultiCell(0, 0, 'Índice de contenidos', 0, 'C', 0, 1, '', '', true, 0);
    $pdf->Ln();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->addTOC(2);
    $pdf->endTOCPage();

    // 7. Salida
    $pdf->Output();
}

// ---------------------------------------------------------------------------
// Punto de entrada principal
// ---------------------------------------------------------------------------
if (!empty($_REQUEST['idMateria'])) {
    $idMateria = (int)$_REQUEST['idMateria'];
    $db = getDBConnection();
    if (!$db) {
        die('Error de conexión a la base de datos.');
    }
    try {
        pgGenerarPDFProgramacion($db, $idMateria);
    } catch (Exception $e) {
        die('Error generando el PDF: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
} else {
    die('Falta el parámetro idMateria.');
}
?>
