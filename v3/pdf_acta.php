<?php

// Página que utiliza la librería TCPD incluida en la app para generar un PDF con el acta
// seleccionada del departamento (parámetro "idActa")

require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');

// Subclase de TCPDF que se utiliza para generar el PDF
// Especificamos contenido de la cabecera y pie de cada página generada
class MiPDF extends TCPDF
{
    // Cabecera de las páginas
    public function Header() 
    {
        $this->setY(15);
        $this->SetFont('helvetica', 'I', 12);
        $this->Cell(0, 10, "I.E.S. San Vicente", 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }

    // Pie de las páginas
    public function Footer() 
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 10, 'Pág '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }    
}

if (!empty($_REQUEST['idActa']))
{
    include('includes/database.php');

    $result = mysqli_query($db, "SELECT departamentos.nombre, actas_departamentos.* FROM departamentos, actas_departamentos WHERE departamentos.id = actas_departamentos.idDepartamento AND actas_departamentos.id = " . $_REQUEST['idActa']);
    if (mysqli_num_rows($result) > 0)
    {
        $fila = mysqli_fetch_assoc($result);
        $departamento = $fila['nombre'];
        $fecha = date('d/m/Y', strtotime($fila['fecha']));
        $texto = $fila['texto'];
        
        // Generación del PDF

        // Orientación de la página (portrait), unidad de medida (mm), formato (A4), unicode(true), codificación (UTF-8), 
        // Así sería usando los parámetros: $pdf = new MiPDF(PDF_PAGE_ORIENTATION, 'mm', PDF_PAGE_FORMAT, true, 'UTF-8');
        $pdf = new MiPDF();

        $pdf->SetAuthor('I.E.S. San Vicente');
        $pdf->SetTitle("Acta de departamento de " . $departamento);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->AddPage();

        // Título de la página con el nombre del departamento y fecha de la reunión
        $pdf->SetFont('helvetica', '', 20);
        $txt = "Departamento de " . $departamento; 
        $pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
        $pdf->SetFont('helvetica', '', 16);
        $txt = "Reunión del " . $fecha . PHP_EOL . PHP_EOL;
        $pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

        // Contenido de la página con el texto de la reunión reflejado en el acta
        $pdf->SetFont('helvetica', '', 12);
        $txt = $texto;
        $pdf->WriteHTML($txt, true, false, true, false, '');

        $pdf->Output();
    }
    mysqli_free_result($result);
    
    include('includes/database2.php');
}

