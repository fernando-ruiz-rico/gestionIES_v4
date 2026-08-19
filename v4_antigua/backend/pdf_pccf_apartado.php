<?php
/** Genera un PDF de un apartado concreto del PCCF y sus subapartados. */
require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');
require_once('includes/constantes.php');
require_once('includes/utilidades.php');
require_once('includes/consultas_bd.php');
require_once('includes/generar_apartado_ra_empresas.php');
require_once('includes/generar_apartados_pccf.php');

class MiPDFPccfApartado extends TCPDF
{
    public function Header()
    {
        $this->setY(15);
        $this->SetFont('helvetica', 'I', 12);
        $this->Cell(0, 10, 'I.E.S. San Vicente', 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 10, 'Pág ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C');
    }
}

function generarPDFApartadoPccf($idCiclo, $idApartado)
{
    $ciclo = obtenerDatosCiclo($idCiclo);
    if (empty($ciclo)) die('Ciclo no encontrado.');

    $apartados = consultarBaseDeDatos('SELECT * FROM apartados_pccf ORDER BY orden');
    $idDepartamento = obtenerIdDepartamentoDeCiclo($idCiclo);
    $nivel = isset($ciclo['nivel']) ? $ciclo['nivel'] : '';
    $contenido = '';
    $tituloPrincipal = '';
    $principalActual = '';
    $empezar = FALSE;

    foreach ($apartados as $apartado) {
        $idActual = (int)$apartado['id'];
        $esPrincipal = !(bool)$apartado['subapartado'];
        if ($esPrincipal) $principalActual = $apartado['titulo'];
        if ($idActual === (int)$idApartado) {
            $empezar = TRUE;
            $tituloPrincipal = $esPrincipal ? $apartado['titulo'] : $principalActual;
        }
        if (!$empezar) continue;
        if ($esPrincipal && $idActual !== (int)$idApartado) break;
        if ($idActual == 12 && (strpos($nivel, 'Básico') !== FALSE || strpos($nivel, 'Especialización') !== FALSE)) continue;
        if ($idActual == 19 && strpos($nivel, 'Especialización') !== FALSE) continue;

        $contenido .= $esPrincipal ? '<h1>' . $apartado['titulo'] . '</h1>' : '<h2>' . $apartado['titulo'] . '</h2>';
        $tipo = (int)$apartado['tipo'];
        if ($tipo == TIPO_APARTADO_EDITABLE) $contenido .= obtenerContenidoApartadoPccf($idActual, $idCiclo, $idDepartamento);
        else $contenido .= generarApartadoPredefinido($tipo, $idCiclo);
    }

    if ($tituloPrincipal === '') die('Apartado no encontrado.');
    $pdf = new MiPDFPccfApartado();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle($ciclo['nombre'] . ' - ' . $tituloPrincipal);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 18);
    $pdf->Write(0, $ciclo['nombre'], '', 0, 'C', TRUE);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->writeHTML($contenido, TRUE, FALSE, TRUE, FALSE, '');
    $pdf->Output();
}

if (!empty($_REQUEST['idCiclo']) && !empty($_REQUEST['idApartado'])) {
    require_once('includes/database.php');
    generarPDFApartadoPccf((int)$_REQUEST['idCiclo'], (int)$_REQUEST['idApartado']);
    require_once('includes/database2.php');
}
?>
