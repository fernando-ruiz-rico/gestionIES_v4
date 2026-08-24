<?php
// Fase 8 — Generación de PDF con la selección de materias de un profesor.
// (parámetros "idProfesor" y "idEscenario"), usando la librería TCPDF incluida.

session_start();
require_once 'config.php';
require_once 'lib/php/tcpdf/examples/tcpdf_include.php';
require_once 'lib/php/tcpdf/tcpdf.php';

class MiPDFSeleccion extends TCPDF
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
        $this->Cell(0, 10, 'Pág '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$idProfesor = isset($_REQUEST['idProfesor']) ? intval($_REQUEST['idProfesor']) : 0;
$idEscenario = isset($_REQUEST['idEscenario']) ? intval($_REQUEST['idEscenario']) : 0;
if ($idProfesor <= 0 || $idEscenario <= 0)
{
    die('Parámetros inválidos');
}

// Endpoint no JSON (imprime el PDF y no devuelve JSON): se conserva la
// apertura original getDBConnection() y el flujo de errores original;
// solo las consultas pasan por Db, con el SQL parametrizado.
$dbConn = getDBConnection();
$db = new Db($dbConn);

$nombreProfesor = 'Desconocido';
try
{
    $filaProf = $db->fetchOne("SELECT nombre FROM profesores WHERE id = ?", $idProfesor);
    if ($filaProf !== null)
    {
        $nombreProfesor = $filaProf['nombre'];
    }
}
catch (DbException $e)
{
    // Mismo flujo que el original: si la consulta falla, se sigue con 'Desconocido'.
}

$nombreEscenario = 'Desconocido';
try
{
    $filaEsc = $db->fetchOne("SELECT nombre FROM escenarios_desideratas WHERE id = ?", $idEscenario);
    if ($filaEsc !== null)
    {
        $nombreEscenario = $filaEsc['nombre'];
    }
}
catch (DbException $e)
{
    // Mismo flujo que el original: si la consulta falla, se sigue con 'Desconocido'.
}

$sql = "SELECT m.nombre AS nombreMateria, m.horas, c.abreviatura AS abrevCurso, g.abreviatura AS abrevGrupo
        FROM seleccion, materias AS m, cursos AS c, grupos AS g
        WHERE seleccion.idMateria = m.id AND m.idCurso = c.id AND seleccion.idGrupo = g.id
        AND seleccion.idProfesor = ? AND seleccion.idEscenario = ?
        ORDER BY seleccion.orden";
try
{
    $filas = $db->fetchAll($sql, $idProfesor, $idEscenario);
}
catch (DbException $e)
{
    die('Error consultando la base de datos: ' . $e->getMessage());
}

$pdf = new MiPDFSeleccion();
$pdf->SetAuthor('I.E.S. San Vicente');
$pdf->SetTitle("Selección de " . $nombreProfesor);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->AddPage();

$pdf->SetFont('helvetica', '', 16);
$pdf->Write(0, $nombreProfesor, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Write(0, "Escenario: " . $nombreEscenario, '', 0, 'C', true, 0, false, false, 0);
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(100, 10, 'Materia', 1);
$pdf->Cell(50, 10, 'Curso', 1);
$pdf->Cell(30, 10, 'Horas', 1);
$pdf->Ln();
$pdf->SetFont('helvetica', '', 12);

$totalHoras = 0;
foreach ($filas as $fila)
{
    $totalHoras += intval($fila['horas']);
    $pdf->Cell(100, 8, $fila['nombreMateria'], 1);
    $pdf->Cell(50, 8, $fila['abrevCurso'] . $fila['abrevGrupo'], 1);
    $pdf->Cell(30, 8, $fila['horas'], 1);
    $pdf->Ln();
}

$pdf->Cell(100, 8, 'TOTAL', 1);
$pdf->Cell(50, 8, '', 1);
$pdf->Cell(30, 8, $totalHoras, 1);

$pdf->Output();
closeDBConnection($dbConn);
