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

$db = getDBConnection();
$sqlProf = "SELECT nombre FROM profesores WHERE id=" . $idProfesor;
$resultProf = mysqli_query($db, $sqlProf);
$nombreProfesor = 'Desconocido';
if ($resultProf && mysqli_num_rows($resultProf) > 0)
{
    $filaProf = mysqli_fetch_assoc($resultProf);
    $nombreProfesor = $filaProf['nombre'];
    mysqli_free_result($resultProf);
}

$sqlEsc = "SELECT nombre FROM escenarios_desideratas WHERE id=" . $idEscenario;
$resultEsc = mysqli_query($db, $sqlEsc);
$nombreEscenario = 'Desconocido';
if ($resultEsc && mysqli_num_rows($resultEsc) > 0)
{
    $filaEsc = mysqli_fetch_assoc($resultEsc);
    $nombreEscenario = $filaEsc['nombre'];
    mysqli_free_result($resultEsc);
}

$sql = "SELECT m.nombre AS nombreMateria, m.horas, c.abreviatura AS abrevCurso, g.abreviatura AS abrevGrupo
        FROM seleccion, materias AS m, cursos AS c, grupos AS g
        WHERE seleccion.idMateria = m.id AND m.idCurso = c.id AND seleccion.idGrupo = g.id
        AND seleccion.idProfesor=" . $idProfesor . " AND seleccion.idEscenario=" . $idEscenario . "
        ORDER BY seleccion.orden";
$result = mysqli_query($db, $sql);
if (!$result)
{
    die('Error consultando la base de datos: ' . mysqli_error($db));
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
while ($fila = mysqli_fetch_assoc($result))
{
    $totalHoras += intval($fila['horas']);
    $pdf->Cell(100, 8, $fila['nombreMateria'], 1);
    $pdf->Cell(50, 8, $fila['abrevCurso'] . $fila['abrevGrupo'], 1);
    $pdf->Cell(30, 8, $fila['horas'], 1);
    $pdf->Ln();
}
mysqli_free_result($result);

$pdf->Cell(100, 8, 'TOTAL', 1);
$pdf->Cell(50, 8, '', 1);
$pdf->Cell(30, 8, $totalHoras, 1);

$pdf->Output();
closeDBConnection($db);
