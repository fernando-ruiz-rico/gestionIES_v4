<?php
// ============================================================================
// Genera el PDF de UN apartado de la programación de una materia (Fase 2.1)
// ============================================================================
//
// Endpoint autocontenido (solicitud de navegador directa, sin sesión) que
// replica v3/pdf_programaciones_apartado.php sobre la base de datos de v4.
//
// Uso:
//   .../pdf_programaciones_apartado.php?idMateria=<id>&idApartado=<id>
//
// Incluye el apartado solicitado y sus subapartados consecutivos (hasta el
// siguiente principal). Si el apartado solicitado es de tipo TEMAS (13), la
// interfaz abre el PDF de unidades (pdf_unidades_programacion.php), no este.
//
// PHP 5 compatible (suelo efectivo 5.4).

header('Content-Type: application/pdf; charset=utf-8');

require_once 'config.php';
require_once 'lib/php/tcpdf/examples/tcpdf_include.php';
require_once 'lib/php/tcpdf/tcpdf.php';
require_once 'lib/programaciones_pdf.php';

// ---------------------------------------------------------------------------
// Encuentra el título del apartado principal para el encabezado del PDF
// ---------------------------------------------------------------------------
function pgEncontrarTituloParaPDF($apartados, $idApartadoSolicitado)
{
    $ultimoPrincipal = null;
    foreach ($apartados as $apartado) {
        $id = (int)$apartado['id'];
        $esPrincipal = !(bool)$apartado['subapartado'];

        if ($esPrincipal) {
            $ultimoPrincipal = $apartado;
        }

        if ($id === (int)$idApartadoSolicitado) {
            if ($esPrincipal) {
                return $apartado['titulo'];
            }
            return $ultimoPrincipal ? $ultimoPrincipal['titulo'] : 'Apartado';
        }
    }
    return null;
}

// ---------------------------------------------------------------------------
// Construye el HTML del apartado solicitado + subapartados consecutivos
// ---------------------------------------------------------------------------
function pgConstruirContenidoPDF($db, $apartados, $idApartadoSolicitado, $idMateria, $idCiclo, $idDepartamento, $profesores, $horasEmpresa)
{
    $html = '';
    $empezar = false;

    foreach ($apartados as $apartado) {
        $idActual = (int)$apartado['id'];
        $tipo = (int)$apartado['tipo'];
        $esPrincipal = !(bool)$apartado['subapartado'];
        $titulo = $apartado['titulo'];

        if ($idActual === (int)$idApartadoSolicitado) {
            $empezar = true;
        }

        if ($empezar) {
            // Si es un apartado principal y NO es el solicitado → detenerse antes de incluirlo
            if ($esPrincipal && $idActual !== (int)$idApartadoSolicitado) {
                break;
            }

            if ($esPrincipal) {
                $html .= '<h1>' . $titulo . '</h1>';
            } else {
                $html .= '<h2>' . $titulo . '</h2>';
            }

            if ($tipo == PG_TIPO_APARTADO_EDITABLE) {
                $html .= pgObtenerContenidoApartado($db, $idActual, $idMateria, $idDepartamento);
            } else {
                $contenido = pgGenerarApartadoPredefinido($db, $tipo, $idMateria, $idCiclo, $idDepartamento, $profesores, $horasEmpresa);
                if (is_string($contenido)) {
                    $html .= $contenido;
                }
            }
        }
    }

    return $html;
}

// ---------------------------------------------------------------------------
// Genera el PDF del apartado solicitado (principal o subapartado)
// ---------------------------------------------------------------------------
function pgGenerarPDFApartado($db, $idMateria, $idApartado)
{
    $datosMateria = pgObtenerDatosMateria($db, $idMateria);
    if (!$datosMateria) {
        die('Materia no encontrada.');
    }

    $curso = $datosMateria['curso'];
    $materia = $datosMateria['materia'];
    $horasEmpresa = $datosMateria['horas_empresa'];
    $idDepartamento = $datosMateria['id_departamento'];
    $categoria = !empty($datosMateria['categoria']) ? $datosMateria['categoria'] : '';
    $idCiclo = pgObtenerIdCicloPorMateria($db, $idMateria);

    $apartados = pgObtenerApartadosProgramacion($db, $categoria);

    $tituloPDF = pgEncontrarTituloParaPDF($apartados, $idApartado);
    if ($tituloPDF === null) {
        die('Apartado no encontrado.');
    }

    $profesores = pgObtenerProfesoresMateria($db, $idMateria);
    $contenido = pgConstruirContenidoPDF($db, $apartados, $idApartado, $idMateria, $idCiclo, $idDepartamento, $profesores, $horasEmpresa);

    $pdf = new MiPDFProgramaciones();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle($materia . " (" . $curso . ") - " . $tituloPDF);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', '', 20);
    $pdf->Write(0, $materia . " (" . $curso . ")", '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 16);
    $pdf->Write(0, $tituloPDF . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->writeHTML($contenido, true, false, true, false, '');

    $pdf->Output();
}

// ---------------------------------------------------------------------------
// Punto de entrada del script
// ---------------------------------------------------------------------------
if (!empty($_REQUEST['idApartado']) && !empty($_REQUEST['idMateria'])) {
    $db = getDBConnection();
    if (!$db) {
        die('Error de conexión a la base de datos.');
    }
    try {
        pgGenerarPDFApartado($db, (int)$_REQUEST['idMateria'], (int)$_REQUEST['idApartado']);
    } catch (Exception $e) {
        die('Error generando el PDF: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
} else {
    die('Faltan los parámetros idMateria e idApartado.');
}
?>
