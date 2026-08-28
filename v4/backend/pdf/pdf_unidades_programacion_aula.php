<?php
// ============================================================================
// Genera el PDF de TODAS las Unidades de Programación (temas) de la copia
// de aula de un (materia, grupo, profesor) (Fase 2.4)
// ============================================================================
//
// Endpoint autocontenido (solicitud de navegador directa, sin sesión). Espejo
// de pdf_unidades_programacion.php, pero lee las tablas de copia de aula
// (temas_aula, resultados_aprendizaje_aula, criterios_temas_aula,
// criterios_evaluacion_aula, competencias_temas_aula) filtradas por el
// (grupo, profesor) de la copia.
//
// Uso:
//   .../pdf_unidades_programacion_aula.php?idMateria=<id>&idGrupo=<g>&idProfesor=<p>
//
// Es el PDF que abre la interfaz cuando el apartado seleccionado es de tipo
// TEMAS (13), y también el botón "PDF de Unidades" de «Programaciones de
// aula». PHP 5 compatible (suelo efectivo 5.4).

header('Content-Type: application/pdf; charset=utf-8');

require_once '../config.php';
require_once '../lib/php/tcpdf/examples/tcpdf_include.php';
require_once '../lib/php/tcpdf/tcpdf.php';
require_once '../lib/pdf_compartidas.php';
require_once '../lib/programaciones_pdf.php';

// Cabecera con el título del documento (como la propuesta); el pie lo hereda
// de la base compartida
class MiPDFProgramacionesUnidadesAula extends MiPDFBase
{
    public function Header()
    {
        $this->setY(15);
        $this->SetFont('helvetica', 'I', 12);
        $this->Cell(0, 10, "I.E.S. San Vicente - " . $this->title, 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }
}

function pgAulaGenerarPDFTemas($db, $idMateria, $aula)
{
    // $db es la conexión cruda: la siguen usando las funciones de la
    // librería compartida lib/programaciones_pdf.php. Las consultas
    // propias de este endpoint se ejecutan con la capa Db, envolviendo
    // la misma conexión.
    $dbDb = new Db($db);

    $idCiclo = pgObtenerIdCicloPorMateria($db, $idMateria);

    $datosMateria = pgObtenerDatosMateria($db, $idMateria);
    if (!$datosMateria) {
        die('Materia no encontrada.');
    }

    $materia = $datosMateria['materia'];
    $curso = $datosMateria['curso'];
    $idDepartamento = $datosMateria['id_departamento'];

    $contenidosDefecto = null;
    if ($idDepartamento) {
        // El contenido por defecto sigue compartiendo el catálogo.
        $contenidosDefecto = pgObtenerContenidosDefectoTema($db, $idDepartamento);
    }

    $pdf = new MiPDFProgramacionesUnidadesAula();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $titulo = "Temas de {$materia} ({$curso})";
    $pdf->SetTitle($titulo);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->SetFont('helvetica', '', 16);

    $temas = pgObtenerTemasDeMateria($db, $idMateria, $aula);
    if (empty($temas)) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, "No hay temas definidos para esta materia.", '', 0, 'C', false, 0, false, false, 0);
    } else {
        foreach ($temas as $tema) {
            $pdf->AddPage();

            // Título del tema
            $pdf->SetFont('helvetica', 'B', 16);
            $prefijo = $idCiclo > 0 ? 'Tema ' : 'SA';
            $pdf->Write(0, "{$prefijo}{$tema['orden']}: {$tema['titulo']}", '', 0, 'L', true, 0, false, false, 0);
            $pdf->Ln(6);

            // Tabla de datos básicos
            $tabla = '
            <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:11px;">
                <tr>
                    <th style="text-align:center; background-color:#f2f2f2;">Horas</th>
                    <th style="text-align:center; background-color:#f2f2f2;">Trimestre</th>
                    <th style="text-align:center; background-color:#f2f2f2;">Peso en evaluación</th>
                </tr>
                <tr>
                    <td style="text-align:center;">' . $tema['horas'] . '</td>
                    <td style="text-align:center;">' . $tema['trimestre'] . '</td>
                    <td style="text-align:center;">' . $tema['peso_evaluacion'] . '%</td>
                </tr>
            </table>';
            $pdf->SetFont('helvetica', '', 11);
            $pdf->writeHTML($tabla, true, false, true, false, '');
            $pdf->Ln(1);

            // === Campos que NO usan contenido por defecto ===
            $camposSinDefecto = array(
                'descripcion'     => 'Descripción',
                'justificacion'   => 'Justificación',
                'secuenciacion'   => 'Secuenciación',
                'contenidos'      => $idCiclo > 0 ? "Contenidos" : "Saberes básicos",
                'evaluacion'      => 'Evaluación'
            );
            foreach ($camposSinDefecto as $campo => $titulo) {
                $contenido = trim($tema[$campo]);
                if (!empty($contenido)) {
                    $pdf->SetFont('helvetica', 'B', 12);
                    $pdf->Write(0, $titulo, '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(1);
                    $pdf->SetFont('helvetica', '', 11);
                    $pdf->writeHTMLCell(0, 0, $pdf->GetX() + 5, $pdf->GetY(), $contenido, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(4);
                }
            }

            // === Campos que SÍ pueden usar contenido por defecto ===
            $camposConDefecto = array(
                'contexto'        => 'Contexto',
                'recursos'        => 'Recursos',
                'metodologia'     => 'Metodología',
                'adaptaciones'    => 'Adaptaciones'
            );
            foreach ($camposConDefecto as $campo => $titulo) {
                $usarDefecto = !empty($tema[$campo . '_defecto']) && $tema[$campo . '_defecto'] == 1;
                if ($usarDefecto && $contenidosDefecto && isset($contenidosDefecto[$campo])) {
                    $contenido = trim($contenidosDefecto[$campo]);
                } else {
                    $contenido = trim($tema[$campo]);
                }
                if (!empty($contenido)) {
                    $pdf->SetFont('helvetica', 'B', 12);
                    $pdf->Write(0, $titulo, '', 0, 'L', true, 0, false, false, 0);
                    $pdf->SetFont('helvetica', '', 11);
                    $pdf->writeHTMLCell(0, 0, $pdf->GetX() + 5, $pdf->GetY(), $contenido, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(4);
                }
            }

            // === RAs y criterios POR TEMA (tablas de aula) ===
            $criteriosConRA = $dbDb->fetchAll(
                "SELECT ce.idRA, ce.codigo AS codigo, ce.texto AS criterio
                   FROM criterios_temas_aula ct
                   INNER JOIN criterios_evaluacion_aula ce ON ct.idRA = ce.idRA AND ct.codigo = ce.codigo
                  WHERE ct.idTema = ?
                  ORDER BY ce.idRA, ce.codigo",
                (int)$tema['id']);

            if (!empty($criteriosConRA)) {
                $rasAgrupados = [];
                $prefijo = $idCiclo > 0 ? 'RA' : 'CE';
                foreach ($criteriosConRA as $fila) {
                    $idRA = $fila['idRA'];
                    if (!isset($rasAgrupados[$idRA])) {
                        $raData = $dbDb->fetchAll(
                            "SELECT orden, texto FROM resultados_aprendizaje_aula WHERE id = ? AND idMateria = ?",
                            (int)$idRA, (int)$idMateria);
                        $textoRA = !empty($raData)
                            ? $prefijo . $raData[0]['orden'] . '. ' . $raData[0]['texto']
                            : "Resultado de aprendizaje no encontrado (ID: $idRA)";
                        $rasAgrupados[$idRA] = ['texto' => $textoRA, 'criterios' => []];
                    }
                    $rasAgrupados[$idRA]['criterios'][] = $fila['codigo'] . ') ' . $fila['criterio'];
                }

                $tituloRACE = $idCiclo > 0 ? 'Resultados de Aprendizaje y Criterios de Evaluación' : 'Competencias Específicas y Criterios de Evaluación';
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Write(0, $tituloRACE, '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(4);

                foreach ($rasAgrupados as $idRA => $datos) {
                    $pdf->SetFont('helvetica', 'B', 11);
                    $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $datos['texto'], 0, 1, 0, true, 'J', true);
                    $pdf->Ln(4);

                    $pdf->SetFont('helvetica', 'I', 11);
                    $pdf->Write(0, "Criterios de Evaluación", '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(2);
                    $pdf->SetFont('helvetica', '', 11);
                    $critHtml = '<ul>';
                    foreach ($datos['criterios'] as $criterio) {
                        $critHtml .= '<li>' . htmlspecialchars($criterio, ENT_NOQUOTES, 'UTF-8') . '</li>';
                    }
                    $critHtml .= '</ul>';
                    $pdf->writeHTML($critHtml, true, false, true, false, '');
                    $pdf->Ln(4);
                }
            } else {
                $pdf->SetFont('helvetica', '', 11);
                $pdf->Write(0, "No hay resultados de aprendizaje ni criterios de evaluación asociados a este tema.", '', 0, 'L', false, 0, false, false, 0);
                $pdf->Ln(6);
            }

            // === Competencias (tabla de aula) ===
            $competencias = pgObtenerCompetenciasDeTema($db, $tema['id'], $aula);
            if (!empty($competencias)) {
                $pdf->SetFont('helvetica', 'B', 12);
                $tituloCompetencias = $idCiclo > 0 ? 'Competencias profesionales y para la empleabilidad' : 'Competencias clave';
                $pdf->Write(0, $tituloCompetencias, '', 0, 'L', true, 0, false, false, 0);
                $pdf->SetFont('helvetica', '', 11);
                $compHtml = '<ul>';
                foreach ($competencias as $comp) {
                    $compHtml .= '<li>' . htmlspecialchars($comp['texto'], ENT_NOQUOTES, 'UTF-8') . '</li>';
                }
                $compHtml .= '</ul>';
                $pdf->writeHTML($compHtml, true, false, true, false, '');
            }
        }
    }

    $pdf->Output("TemasAula_{$idMateria}.pdf", 'I');
}

// Punto de entrada
if (!empty($_GET['idMateria']) && !empty($_GET['idGrupo']) && !empty($_GET['idProfesor'])) {
    $db = getDBConnection();
    if (!$db) {
        die('Error de conexión a la base de datos.');
    }
    try {
        $aula = array('idGrupo' => (int)$_GET['idGrupo'], 'idProfesor' => (int)$_GET['idProfesor']);
        pgAulaGenerarPDFTemas($db, (int)$_GET['idMateria'], $aula);
    } catch (Exception $e) {
        die('Error generando el PDF: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
} else {
    die('Parámetros idMateria, idGrupo e idProfesor requeridos.');
}
?>
