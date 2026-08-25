<?php
// ============================================================================
// Cabecera y pie de página comunes de los PDF (TCPDF) de la app
// ============================================================================
//
// Debe cargarse DESPUÉS de lib/php/tcpdf/tcpdf.php (la clase base extiende
// TCPDF). Antes cada PDF traía su propia subclase con exactamente la misma
// cabecera/pie; ahora comparten esta base.

// Cabecera/pie estándar ("I.E.S. San Vicente" + número de página), igual que
// antes en todas las subclases MiPDF* de la app.
class MiPDFBase extends TCPDF
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
