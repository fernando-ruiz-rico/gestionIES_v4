<?php
// ============================================================================
// Genera el PDF de SEGUIMIENTO de las programaciones de aula (Fase 8)
// ============================================================================
//
// Endpoint autocontenido (solicitud de navegador directa) que replica
// v3/pdf_programaciones_seguimiento.php sobre la base de datos de v4.
//
// Portada + 5 secciones:
//   1. Seguimiento de la programación (temporalización)
//   2. Valoración de los resultados académicos
//   3. Inclusión del alumnado
//   4. Valoración de las horas de atención a pendientes, desdobles...
//      (funcionamiento del departamento, tabla de datos comunes)
//   5. Actividades extraescolares programadas para la siguiente evaluación
//
// Uso:
//   .../pdf_programaciones_seguimiento.php?departamento=<id>&curso=<curso>&evaluacion=<id>&categoria=FP|ESO/BACH
//
// - `departamento`: opcional; si no viene, se usa el de la sesión (como en v3).
// - `curso`: el curso del seguimiento (el actual; el front lo envía).
// - `categoria`: 'FP' (Ciclos Formativos) o 'ESO/BACH'; sin 'FP' se usa el
//   criterio ESO/BACH (igual que v3, que no enviaba nada desde la página común).
//
// PHP 5 compatible.

header('Content-Type: application/pdf; charset=utf-8');

@session_start();
require_once 'config.php';
require_once 'lib/php/tcpdf/examples/tcpdf_include.php';
require_once 'lib/php/tcpdf/tcpdf.php';

// Subclase de TCPDF: cabecera y pie de página (igual que v3)
class MiPDFSeguimiento extends TCPDF
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

$db = getDBConnection();
if (!$db) {
    die('Error de conexión a la base de datos');
}

$curso = isset($_REQUEST['curso']) ? trim($_REQUEST['curso']) : '';
$idEvaluacion = isset($_REQUEST['evaluacion']) ? intval($_REQUEST['evaluacion']) : 0;
$idDepartamento = isset($_REQUEST['departamento']) ? intval($_REQUEST['departamento']) : 0;

// Si no viene el departamento, el de la sesión (mismo criterio que v3)
if ($idDepartamento <= 0 && isset($_SESSION['departamentoUsuario'])) {
    $idDepartamento = intval($_SESSION['departamentoUsuario']);
}

// Mismo criterio que v3: 'FP' → solo FP; lo demás → ESO o BACH
$categoria = (isset($_REQUEST['categoria']) && $_REQUEST['categoria'] == 'FP')
    ? "categoria = 'FP'"
    : "categoria = 'ESO' OR categoria = 'BACH'";
$categoriaTexto = (isset($_REQUEST['categoria']) && $_REQUEST['categoria'] == 'FP') ? 'FP' : 'ESO/BACH';

if ($curso == '' || $idEvaluacion <= 0) {
    closeDBConnection($db);
    die('Falta el curso o la evaluación');
}

// ---------------------------------------------------------------------------
// Datos de la portada
// ---------------------------------------------------------------------------
$stmt = mysqli_prepare($db, 'SELECT nombre FROM evaluaciones WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $idEvaluacion);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$evaluacion = 'Evaluación';
if (mysqli_num_rows($result) > 0) {
    $fila = mysqli_fetch_assoc($result);
    $evaluacion = $fila['nombre'];
}
mysqli_free_result($result);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($db, 'SELECT nombre FROM departamentos WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $idDepartamento);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$nomDepartamento = '';
if (mysqli_num_rows($result) > 0) {
    $fila = mysqli_fetch_assoc($result);
    $nomDepartamento = $fila['nombre'];
}
mysqli_free_result($result);
mysqli_stmt_close($stmt);

// Carga de los datos comunes del departamento (tabla de datos generales)
// Nota: igual que en v3, los datos comunes del departamento siguen
// guardándose en seguimiento_programaciones_departamento.
$funcionamiento_departamento = "No hay datos disponibles";
$actividades_extraescolares = "No hay datos disponibles";
$temporalizacion_defecto = "No hay datos disponibles"; // Se usaría si el profe deja vacía la temporalización
$inclusion_defecto = "No hay datos disponibles";     // Se usa si no hay ninguna inclusión

$stmt = mysqli_prepare($db, 'SELECT funcionamiento_departamento, actividades_extraescolares, temporalizacion_defecto'
    . ' FROM seguimiento_programaciones_departamento'
    . ' WHERE idDepartamento = ? AND curso = ? AND evaluacion = ?');
mysqli_stmt_bind_param($stmt, 'iss', $idDepartamento, $curso, $idEvaluacion);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    $fila = mysqli_fetch_assoc($result);
    $funcionamiento_departamento = $fila['funcionamiento_departamento'];
    $actividades_extraescolares = $fila['actividades_extraescolares'];
    if (!empty($fila['temporalizacion_defecto'])) {
        $temporalizacion_defecto = $fila['temporalizacion_defecto'];
    }
}
mysqli_free_result($result);
mysqli_stmt_close($stmt);

// ---------------------------------------------------------------------------
// Cursos del tipo elegido (una sola carga; v3 recorría 3 veces con data_seek)
// ---------------------------------------------------------------------------
$stmt = mysqli_prepare($db, 'SELECT id, nombre FROM cursos WHERE ' . $categoria . ' ORDER BY orden');
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cursos = array();
while ($fila = mysqli_fetch_assoc($result)) {
    $cursos[] = $fila;
}
mysqli_free_result($result);
mysqli_stmt_close($stmt);

// Filas de seguimiento_programaciones_aula de un grupo del departamento y
// curso/evaluación pedidos (igual que v3).
// $campos: columnas de spa a devolver; $filtroExtra: condiciones extra de v3
// (sección de inclusión).
function sp_buscarFilas($db, $filaGrupo, $idDepartamento, $curso, $idEvaluacion, $campos, $filtroExtra = '')
{
    $sql = 'SELECT ' . $campos . ', m.nombre AS materia, p.nombre AS profesor'
        . ' FROM seguimiento_programaciones_aula spa'
        . ' INNER JOIN materias m ON spa.idMateria = m.id'
        . ' INNER JOIN profesores p ON spa.idProfesor = p.id'
        . ' WHERE spa.idGrupo = ' . intval($filaGrupo['id'])
        . ' AND m.idDepartamento = ' . intval($idDepartamento)
        . ' AND spa.curso = "' . mysqli_real_escape_string($db, $curso) . '"'
        . ' AND spa.evaluacion = ' . intval($idEvaluacion)
        . $filtroExtra
        . ' ORDER BY m.nombre, p.nombre';
    $result = mysqli_query($db, $sql);
    if (!$result) {
        die('Error consultando la base de datos: ' . mysqli_error($db));
    }
    return $result;
}

// ---------------------------------------------------------------------------
// Preparar el documento PDF
// ---------------------------------------------------------------------------
$pdf = new MiPDFSeguimiento();
$pdf->SetAuthor('I.E.S. San Vicente');
$pdf->SetTitle("Seguimiento programaciones " . $categoriaTexto . ". Departamento de " . $nomDepartamento . ". Curso " . $curso . ", " . $evaluacion);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Imprimir primera página con título
$pdf->AddPage();

$pdf->Write(0, str_repeat(PHP_EOL, 15), '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('helvetica', '', 30);
$pdf->Write(0, "Seguimiento de programaciones" . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('helvetica', '', 16);
$pdf->Write(0, "Curso " . $curso . ", " . $evaluacion . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
$pdf->Write(0, "Departamento de " . $nomDepartamento . " (" . $categoriaTexto . ")" . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);

// ---------------------------------------------------------------------------
// SECCIÓN 1: TEMPORALIZACIÓN
// ---------------------------------------------------------------------------
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Write(0, "1. SEGUIMIENTO DE LA PROGRAMACIÓN (con respecto a la temporalización que figura en las Propuestas Pedagógicas)" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);

foreach ($cursos as $filaCurso) {
    $stmt = mysqli_prepare($db, 'SELECT id, nombre FROM grupos WHERE idCurso = ? ORDER BY orden');
    mysqli_stmt_bind_param($stmt, 'i', $filaCurso['id']);
    mysqli_stmt_execute($stmt);
    $resultGrupos = mysqli_stmt_get_result($stmt);
    while ($filaGrupo = mysqli_fetch_assoc($resultGrupos)) {
        $resultSeguimiento = sp_buscarFilas($db, $filaGrupo, $idDepartamento, $curso, $idEvaluacion, 'spa.temporalizacion');
        if (mysqli_num_rows($resultSeguimiento) > 0) {
            $pdf->SetFont('helvetica', 'B', 13);
            $pdf->SetTextColor(0, 0, 128);
            $pdf->Write(0, PHP_EOL . PHP_EOL . $filaCurso['nombre'] . ' ' . $filaGrupo['nombre'], '', 0, 'L', true, 0, false, false, 0);
            $pdf->SetTextColor(0, 0, 0);
            while ($filaSpa = mysqli_fetch_assoc($resultSeguimiento)) {
                $contenidoTemp = trim($filaSpa['temporalizacion']);
                if ($contenidoTemp != '') {
                    $pdf->SetFont('helvetica', 'B', 12);
                    $pdf->Write(0, PHP_EOL . PHP_EOL . $filaSpa['materia'] . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
                    $pdf->SetFont('helvetica', '', 12);
                    $pdf->WriteHTML($contenidoTemp . PHP_EOL, true, false, true, false, '');
                }
            }
        }
        mysqli_free_result($resultSeguimiento);
    }
    mysqli_free_result($resultGrupos);
    mysqli_stmt_close($stmt);
}

// ---------------------------------------------------------------------------
// SECCIÓN 2: RESULTADOS
// ---------------------------------------------------------------------------
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Write(0, "2. VALORACIÓN DE LOS RESULTADOS ACADÉMICOS (con especial atención a los grupos de desdoble o refuerzo, si los hay, detallando cumplimiento de programación, incidencia sobre la convivencia del grupo y resultados académicos)" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);

foreach ($cursos as $filaCurso) {
    $stmt = mysqli_prepare($db, 'SELECT id, nombre FROM grupos WHERE idCurso = ? ORDER BY orden');
    mysqli_stmt_bind_param($stmt, 'i', $filaCurso['id']);
    mysqli_stmt_execute($stmt);
    $resultGrupos = mysqli_stmt_get_result($stmt);
    while ($filaGrupo = mysqli_fetch_assoc($resultGrupos)) {
        $resultSeguimiento = sp_buscarFilas($db, $filaGrupo, $idDepartamento, $curso, $idEvaluacion, 'spa.resultados, spa.inclusion, spa.num_aprobados, spa.num_suspensos, spa.num_otros');
        if (mysqli_num_rows($resultSeguimiento) > 0) {
            $pdf->SetFont('helvetica', 'B', 13);
            $pdf->SetTextColor(0, 0, 128);
            $pdf->Write(0, PHP_EOL . PHP_EOL . $filaCurso['nombre'] . ' ' . $filaGrupo['nombre'], '', 0, 'L', true, 0, false, false, 0);
            $pdf->SetTextColor(0, 0, 0);
            while ($filaSpa = mysqli_fetch_assoc($resultSeguimiento)) {
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Write(0, PHP_EOL . PHP_EOL . $filaSpa['materia'] . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
                $pdf->SetFont('helvetica', '', 12);

                // Datos numéricos y porcentaje de aprobados
                $aprobados = (int)$filaSpa['num_aprobados'];
                $suspensos = (int)$filaSpa['num_suspensos'];
                $otros = (int)$filaSpa['num_otros'];
                $total_alumnos = $aprobados + $suspensos + $otros;
                $porcentaje = 0;
                if ($total_alumnos > 0) {
                    $porcentaje = round(($aprobados / $total_alumnos) * 100, 2);
                }
                $pdf->SetFont('helvetica', 'I', 12);
                $stats = "Aprobados: " . $aprobados . " ($porcentaje%)  |  Suspensos: $suspensos  |  Otros: $otros";
                $pdf->Write(0, $stats . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
                $pdf->SetFont('helvetica', '', 12);

                if (!empty($filaSpa['resultados'])) {
                    $pdf->WriteHTML($filaSpa['resultados'] . PHP_EOL, true, false, true, false, '');
                }
                $pdf->Ln(2);
            }
        }
        mysqli_free_result($resultSeguimiento);
    }
    mysqli_free_result($resultGrupos);
    mysqli_stmt_close($stmt);
}

// ---------------------------------------------------------------------------
// SECCIÓN 3: INCLUSIÓN DEL ALUMNADO
// ---------------------------------------------------------------------------
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Write(0, "3. INCLUSIÓN DEL ALUMNADO. VALORACIÓN DE LOS RESULTADOS DE ALUMNADO A QUIEN SE LE HA APLICADO ALGÚN TIPO DE RESPUESTA EDUCATIVA" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);

// Filtro de v3: solo inclusiones con contenido de verdad (no HTML vacío)
$filtroInclusion = " AND spa.inclusion IS NOT NULL"
    . " AND spa.inclusion != ''"
    . " AND spa.inclusion NOT LIKE '<p><br></p>'"
    . " AND spa.inclusion NOT LIKE '<p>&nbsp;</p>'"
    . " AND spa.inclusion NOT LIKE '<br>'"
    . " AND spa.inclusion REGEXP '>[^<]+'";

$inclusion_vacio = true;

foreach ($cursos as $filaCurso) {
    $stmt = mysqli_prepare($db, 'SELECT id, nombre FROM grupos WHERE idCurso = ? ORDER BY orden');
    mysqli_stmt_bind_param($stmt, 'i', $filaCurso['id']);
    mysqli_stmt_execute($stmt);
    $resultGrupos = mysqli_stmt_get_result($stmt);
    while ($filaGrupo = mysqli_fetch_assoc($resultGrupos)) {
        $resultSeguimiento = sp_buscarFilas($db, $filaGrupo, $idDepartamento, $curso, $idEvaluacion, 'spa.inclusion', $filtroInclusion);
        if (mysqli_num_rows($resultSeguimiento) > 0) {
            $pdf->SetFont('helvetica', 'B', 13);
            $pdf->SetTextColor(0, 0, 128);
            $pdf->Write(0, PHP_EOL . PHP_EOL . $filaCurso['nombre'] . ' ' . $filaGrupo['nombre'], '', 0, 'L', true, 0, false, false, 0);
            $pdf->SetTextColor(0, 0, 0);
            while ($filaSpa = mysqli_fetch_assoc($resultSeguimiento)) {
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Write(0, PHP_EOL . PHP_EOL . $filaSpa['materia'] . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
                $pdf->SetFont('helvetica', '', 12);
                $pdf->WriteHTML($filaSpa['inclusion'] . PHP_EOL, true, false, true, false, '');
            }
            $inclusion_vacio = false;
        }
        mysqli_free_result($resultSeguimiento);
    }
    mysqli_free_result($resultGrupos);
    mysqli_stmt_close($stmt);
}

if ($inclusion_vacio) {
    // Si no se ha añadido nada, texto por defecto (igual que v3)
    $pdf->SetFont('helvetica', '', 12);
    $pdf->WriteHTML($inclusion_defecto . PHP_EOL, true, false, true, false, '');
}

// ---------------------------------------------------------------------------
// SECCIÓN 4: FUNCIONAMIENTO DEL DEPARTAMENTO (datos comunes)
// ---------------------------------------------------------------------------
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Write(0, "4. VALORACIÓN DE LAS HORAS DE ATENCIÓN A PENDIENTES, DESDOBLES, REFUERZOS, TALLERES DE ACOMPAÑAMIENTO Y MANTENIMIENTO (INFORMÁTICA) A LO LARGO DEL TRIMESTRE" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->WriteHTML($funcionamiento_departamento . PHP_EOL, true, false, true, false, '');

// ---------------------------------------------------------------------------
// SECCIÓN 5: ACTIVIDADES EXTRAESCOLARES (datos comunes)
// ---------------------------------------------------------------------------
$pdf->SetFont('helvetica', 'B', 14);
$proximaEvaluacion = $idEvaluacion + 1;
$pdf->Write(0, PHP_EOL . PHP_EOL . "5. ACTIVIDADES EXTRAESCOLARES PROGRAMADAS PARA LA " . $proximaEvaluacion . "ª EVALUACIÓN" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->WriteHTML($actividades_extraescolares . PHP_EOL, true, false, true, false, '');

$pdf->Output();

closeDBConnection($db);
?>
