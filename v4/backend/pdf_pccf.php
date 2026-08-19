<?php
// Genera un PDF con el contenido completo de una programación

require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');
require_once('includes/constantes.php');
require_once('includes/utilidades.php');
require_once('includes/consultas_bd.php');
require_once('includes/generar_apartado_ra_empresas.php');
require_once('includes/generar_apartados_pccf.php');

// -------------------------------
// Clase personalizada de TCPDF
// -------------------------------
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

// -------------------------------
// Agrega un apartado al PDF
// -------------------------------
function agregarApartadoAlPDF($pdf, &$contadorPrincipal, &$contadorSecundario, $apartado, $contenido, $tipo)
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
        if ($tipo == TIPO_APARTADO_FE) $pdf->AddPage();
        $pdf->Bookmark("     $contadorPrincipal.$contadorSecundario. $titulo", 0, 0, '', '');
        $pdf->WriteHTML('<br><h2>' . $contadorPrincipal . '.' . $contadorSecundario . '. ' . $titulo . '</h2><br>', true, false, true, false, '');
    }

    if (!empty($contenido)) {
        if ($tipo == TIPO_APARTADO_TEMAS && is_array($contenido)) {
            $totalTemas = count($contenido);
            for ($i = 0; $i < $totalTemas; $i++) {
                if ($i > 0) {
                    // Añadir página nueva antes del segundo tema, tercero, etc.
                    $pdf->AddPage();
                }
                $pdf->writeHTML($contenido[$i], true, false, true, false, '');
            }
        }
        else {
            // Apartado normal (no temas)
            $pdf->writeHTML($contenido, true, false, true, false, '');
        }
    }
}

// -------------------------------
// Genera el PDF completo
// -------------------------------
function generarPDFPccf($idCiclo)
{
    // 1. Obtener datos del ciclo formativo y el dpto. asociado
    $datosCiclo = obtenerDatosCiclo($idCiclo);
    $nivelCiclo = $datosCiclo['nivel'];
    $idDepartamento = obtenerIdDepartamentoDeCiclo($idCiclo);
    list($anyo1, $anyo2) = obtenerCursoAcademico();
    $profesores = array();
    $filasProfesores = consultarBaseDeDatos("SELECT DISTINCT p.nombre FROM profesores p INNER JOIN seleccion s ON p.id = s.idProfesor INNER JOIN materias m ON s.idMateria = m.id INNER JOIN cursos_ciclos cc ON m.idCurso = cc.idCurso WHERE cc.idCiclo = " . (int)$idCiclo . " ORDER BY p.nombre");
    foreach ($filasProfesores as $filaProfesor) $profesores[] = $filaProfesor['nombre'];

    // 3. Inicializar PDF
    $pdf = new MiPDF();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle("PCCF {$datosCiclo['nombre']} ($anyo1/$anyo2)");
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // 4. Portada
    $pdf->AddPage();
    $pdf->Write(0, str_repeat(PHP_EOL, 10), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 30);
    $pdf->Write(0, $datosCiclo['nombre'] . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 25);
    $pdf->Write(0, 'Proyecto Curricular de Ciclo Formativo' . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Curso: $anyo1/$anyo2" . str_repeat(PHP_EOL, 3), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', 'I', 12);
    foreach ($profesores as $prof) {
        $pdf->Write(0, $prof . PHP_EOL, '', 0, 'C', true, 0, false, false, 0);
    }
    $pdf->SetFont('helvetica', '', 12);

    // 5. Contenidos
    $sqlApartados = "SELECT * FROM apartados_pccf ORDER BY orden";
    $apartados = consultarBaseDeDatos($sqlApartados);

    $contadorPrincipal = 0;
    $contadorSecundario = 0;

    foreach ($apartados as $apartado) {
        $id = (int)$apartado['id'];
        $tipo = (int)$apartado['tipo'];
        $requerido = (bool)$apartado['requerido'];

        if ($tipo == TIPO_APARTADO_EDITABLE) {
            if ($id == 12 && (strpos($nivelCiclo, 'Básico') !== false || strpos($nivelCiclo, 'Especialización') !== false)) continue;
            if ($id == 19 && strpos($nivelCiclo, 'Especialización') !== false) continue;

            // Apartado editable
            $contenido = obtenerContenidoApartadoPccf($id, $idCiclo, $idDepartamento);
        } else {
            // Apartado predefinido
            $contenido = generarApartadoPredefinido($tipo, $idCiclo);
        }

        if (!empty($contenido) || $requerido) {
            agregarApartadoAlPDF($pdf, $contadorPrincipal, $contadorSecundario, $apartado, $contenido, $tipo);
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

// -------------------------------
// Punto de entrada principal
// -------------------------------
if (!empty($_REQUEST['idCiclo'])) {
    require_once('includes/database.php');
    generarPDFPccf((int)$_REQUEST['idCiclo']);
    require_once('includes/database2.php');
}
