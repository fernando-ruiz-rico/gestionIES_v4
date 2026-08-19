<?php
// Genera un PDF con el contenido completo de la separata de criterios de evaluación

require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');
require_once('includes/constantes.php');
require_once('includes/utilidades.php');
require_once('includes/consultas_bd.php');
require_once('includes/generar_apartado_resumen_ce.php');
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
// Obtiene los datos identificativos de la programación de aula (materia, grupo, profesor, etc)
// -------------------------------
function obtenerDatosIdentificativos($idMateria, $idGrupo, $idProfesor)
{
    $datosIdentificativos = array();

    // 1. Datos básicos de la materia
    $datos = obtenerDatosMateria($idMateria);
    if (!$datos) {
        die('Materia no encontrada.');
    }

    $datosIdentificativos['curso'] = $datos['curso'];
    $datosIdentificativos['materia'] = $datos['materia'];
    $datosIdentificativos['horas'] = $datos['horas'];
    $datosIdentificativos['departamento'] = $datos['departamento'];
    $datosIdentificativos['id_departamento'] = $datos['id_departamento'];
    $datosIdentificativos['id_ciclo'] = obtenerIdCicloPorMateria($idMateria);

    // 2. Profesores y curso académico
    $datos = consultarBaseDeDatos("SELECT nombre FROM profesores WHERE id = $idProfesor");
    if (!$datos || count($datos) == 0) {
        die('Profesor no encontrado.');
    }
    $datosIdentificativos['profesor'] = $datos[0]['nombre'];
    list($anyo1, $anyo2) = obtenerCursoAcademico();
    $datosIdentificativos['cursoAcademico'] = $anyo1 . "/" . $anyo2;

    // 3. Datos del grupo
    $datos = consultarBaseDeDatos("SELECT nombre FROM grupos WHERE id = $idGrupo");
    if (!$datos || count($datos) == 0) {
        die('Grupo no encontrado.');
    }
    $datosIdentificativos['grupo'] = $datos[0]['nombre'];

    return $datosIdentificativos;
}

// -------------------------------
// Genera el PDF completo
// -------------------------------
function generarPDFSeparataCE($idMateria, $idGrupo, $idProfesor)
{
    $contadorIndice = 0;
    $idCiclo = obtenerIdCicloPorMateria($idMateria);

    // Cargar datos identificativos
    $datosIdentificativos = obtenerDatosIdentificativos($idMateria, $idGrupo, $idProfesor);

    // Solución temporal para quitar el grupo de la materia para grupos de la ESO/BACH
    $materia = preg_replace('/\s*\(grupos?\s+[^\)]*\)/i', '', $datosIdentificativos['materia']);
    $curso = $datosIdentificativos['curso'];
    $cursoAcademico = $datosIdentificativos['cursoAcademico'];
    $grupo = $datosIdentificativos['grupo'];
    $profesor = $datosIdentificativos['profesor'];
    $departamento = $datosIdentificativos['departamento'];
    $idCiclo = $datosIdentificativos['id_ciclo'];
    $idDepartamento = $datosIdentificativos['id_departamento'];

    // Inicializar PDF
    $pdf = new MiPDF();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle("{$materia} ({$curso} {$grupo}, {$profesor})");
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Portada
    $pdf->AddPage();
    $pdf->Write(0, str_repeat(PHP_EOL, 5), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 30);
    $pdf->Write(0, $materia . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "{$curso} {$grupo}". str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 16);
    $pdf->Write(0, "Separata de criterios de calificación e instrumentos de evaluación" . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Curso: {$cursoAcademico}" . str_repeat(PHP_EOL, 3), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Departamento de " . $departamento . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Write(0, $profesor, '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 12);

    // Evaluación
    $titulo = "Evaluación";
    $pdf->AddPage();
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $evaluacion = obtenerContenidoApartado(55, $idMateria, $idDepartamento);
    if (!empty($evaluacion)) $pdf->WriteHTML("$evaluacion<br>", true, false, true, false, '');

    // Tabla resumen de criterios de evaluación por tema
    $sa_up = $idCiclo > 0 ? 'Unidades de Programación' : 'Situaciones de Aprendizaje';
    $titulo = "Tablas resumen $sa_up - Criterios de Evaluación";
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $tablaResumenCE = generarTablasResumenCriteriosEvaluacion($idMateria, $idCiclo);
    $pdf->WriteHTML($tablaResumenCE, true, false, true, false, '');

    $titulo = "Porcentajes de evaluación";
    $pdf->WriteHTML("<br><h1>$titulo</h1><br>", true, false, true, false, '');
    $tablaPorcentajes = generarContenidoEvaluacionAprendizaje($idMateria, $idCiclo);
    $pdf->WriteHTML($tablaPorcentajes, true, false, true, false, '');

    // Salida
    $pdf->Output();
}

// -------------------------------
// Punto de entrada principal
// -------------------------------
if (!empty($_REQUEST['idMateria'])) {
    require_once('includes/database.php');
    generarPDFSeparataCE((int)$_REQUEST['idMateria'], (int)$_REQUEST['idGrupo'], (int)$_REQUEST['idProfesor']);
    require_once('includes/database2.php');
}
