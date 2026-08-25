<?php
// Fase 8 — Generación de PDF con el acta de departamento seleccionada
// (parámetro "idActa"), usando la librería TCPDF incluida en la app.

session_start();
require_once '../config.php';
require_once '../lib/php/tcpdf/examples/tcpdf_include.php';
require_once '../lib/php/tcpdf/tcpdf.php';
require_once '../lib/pdf_compartidas.php';

$idActa = isset($_REQUEST['idActa']) ? intval($_REQUEST['idActa']) : 0;
if ($idActa <= 0)
{
    die('Acta inválida');
}

$conn = getDBConnection();
$db = new Db($conn);
try {
    $fila = $db->fetchOne("SELECT departamentos.nombre AS nombreDepartamento, actas_departamentos.id, actas_departamentos.fecha, actas_departamentos.texto
        FROM departamentos, actas_departamentos
        WHERE departamentos.id = actas_departamentos.idDepartamento AND actas_departamentos.id = ?", $idActa);
} catch (DbException $e) {
    die('Error consultando la base de datos: ' . $e->getMessage());
}

if ($fila)
{
    $departamento = $fila['nombreDepartamento'];
    $fecha = date('d/m/Y', strtotime($fila['fecha']));
    $texto = $fila['texto'];

    $pdf = new MiPDFBase();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle("Acta de departamento de " . $departamento);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', '', 20);
    $pdf->Write(0, "Departamento de " . $departamento, '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 16);
    $pdf->Write(0, "Reunión del " . $fecha, '', 0, 'C', true, 0, false, false, 0);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->WriteHTML($texto, true, false, true, false, '');

    $pdf->Output();
}
else
{
    die('No se encontró el acta');
}
$db->close();
