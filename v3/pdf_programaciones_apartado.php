<?php

require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');
require_once('includes/constantes.php');
require_once('includes/utilidades.php');
require_once('includes/consultas_bd.php');
require_once('includes/generar_apartado_ra_empresas.php');
require_once('includes/generar_apartados_programaciones.php');

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
// Encuentra el título del apartado principal para usar en el encabezado del PDF
// -------------------------------
function encontrarTituloParaPDF($apartados, $idApartadoSolicitado)
{
    // Recorremos en orden para encontrar el apartado solicitado
    // y determinar el último apartado PRINCIPAL anterior o igual a él
    $ultimoPrincipal = null;

    foreach ($apartados as $apartado) {
        $id = (int)$apartado['id'];
        $esPrincipal = !(bool)$apartado['subapartado'];

        if ($esPrincipal) {
            $ultimoPrincipal = $apartado;
        }

        if ($id === (int)$idApartadoSolicitado) {
            // Si el apartado solicitado es principal, usamos su título
            if ($esPrincipal) {
                return $apartado['titulo'];
            }
            // Si es subapartado, usamos el título del último principal
            return $ultimoPrincipal ? $ultimoPrincipal['titulo'] : 'Apartado';
        }
    }

    // Si no se encontró el apartado, devolvemos null
    return null;
}

// -------------------------------
// Construye el HTML del PDF incluyendo el apartado solicitado y sus subapartados consecutivos
// -------------------------------
function construirContenidoPDF($apartados, $idApartadoSolicitado, $idMateria, $idCiclo, $idDepartamento, $profesores, $horasEmpresa)
{
    $html = '';
    $empezar = false;

    foreach ($apartados as $apartado) {
        $idActual = (int)$apartado['id'];
        $tipo = (int)$apartado['tipo'];
        $esPrincipal = !(bool)$apartado['subapartado'];
        $titulo = $apartado['titulo'];

        // Si encontramos el apartado solicitado, empezamos a incluir
        if ($idActual === (int)$idApartadoSolicitado) {
            $empezar = true;
        }

        // Si ya empezamos, procesamos este apartado
        if ($empezar) {
            // Si es un apartado PRINCIPAL y NO es el solicitado → detenerse ANTES de incluirlo
            if ($esPrincipal && $idActual !== (int)$idApartadoSolicitado) {
                break; // Detenemos aquí, sin incluir este apartado
            }

            // Escribir título
            if ($esPrincipal) {
                $html .= '<h1>' . $titulo . '</h1>';
            } else {
                $html .= '<h2>' . $titulo . '</h2>';
            }

            if ($tipo == TIPO_APARTADO_EDITABLE) {
                $html .= obtenerContenidoApartado($idActual, $idMateria, $idDepartamento);
            } 
            else {
                $html .= generarApartadoPredefinido($tipo, $idMateria, $idCiclo, $idDepartamento, $profesores, $horasEmpresa);
            }
        }
    }

    return $html;
}

// -------------------------------
// Genera el PDF del apartado solicitado (principal o subapartado)
// -------------------------------
function generarPDFApartado($idMateria, $idApartado)
{
    $datosMateria = obtenerDatosMateria($idMateria);
    if (!$datosMateria) {
        die('Materia no encontrada.');
    }

    $curso = $datosMateria['curso'];
    $materia = $datosMateria['materia'];
    $horasEmpresa = $datosMateria['horas_empresa'];
    $idDepartamento = $datosMateria['id_departamento'];
    $categoria = !empty($datosMateria['categoria']) ? $datosMateria['categoria'] : '';
    $idCiclo = obtenerIdCicloPorMateria($idMateria);

    $apartados = obtenerApartadosProgramacion($categoria);

    // Determinar el título para el PDF (el del apartado principal que contiene el solicitado)
    $tituloPDF = encontrarTituloParaPDF($apartados, $idApartado);
    if ($tituloPDF === null) {
        die('Apartado no encontrado.');
    }

    $contenido = construirContenidoPDF($apartados, $idApartado, $idMateria, $idCiclo, $idDepartamento, null, $horasEmpresa);

    $pdf = new MiPDF();
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

// -------------------------------
// Punto de entrada del script
// -------------------------------
if (!empty($_REQUEST['idApartado']) && !empty($_REQUEST['idMateria'])) {
    require_once('includes/database.php');
    generarPDFApartado((int)$_REQUEST['idMateria'], (int)$_REQUEST['idApartado']);
    require_once('includes/database2.php');
}