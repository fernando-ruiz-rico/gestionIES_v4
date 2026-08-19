<?php
// Genera un PDF con todos los temas de una materia
// Cada tema en una página nueva
// Contenidos por defecto cuando corresponda
// RAs y criterios asociados por tema
// Todos los campos HTML renderizados

require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');
require_once('includes/utilidades.php');
require_once('includes/consultas_bd.php');

class MiPDF extends TCPDF
{
    public function Header()
    {
        $this->setY(15);
        $this->SetFont('helvetica', 'I', 12);
        $this->Cell(0, 10, "I.E.S. San Vicente - " . $this->title, 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 10, 'Pág ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

function generarPDF($idMateria)
{
    $idCiclo = obtenerIdCicloPorMateria($idMateria);

    $datosMateria = obtenerDatosMateria($idMateria);
    if (!$datosMateria) {
        die('Materia no encontrada.');
    }

    $materia = $datosMateria['materia'];
    $curso = $datosMateria['curso'];
    $idDepartamento = $datosMateria['id_departamento'];

    // Cargar contenidos por defecto del departamento (si existen)
    $contenidosDefecto = null;
    if ($idDepartamento) {
        $contenidosDefecto = obtenerContenidosDefectoTema($idDepartamento);
    }

    $pdf = new MiPDF();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $titulo = "Temas de {$materia} ({$curso})";
    $pdf->SetTitle($titulo);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->SetFont('helvetica', '', 16);

    // Obtener temas
    $temas = obtenerTemasDeMateria($idMateria);
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

            // Tabla de datos básicos: 3 columnas, 2 filas
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
            $camposSinDefecto = [
                'descripcion'     => 'Descripción',
                'justificacion'   => 'Justificación',
                'secuenciacion'   => 'Secuenciación',
                'contenidos'      => $idCiclo > 0 ? "Contenidos" : "Saberes básicos",
                'evaluacion'      => 'Evaluación'
            ];

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
            $camposConDefecto = [
                'contexto'        => 'Contexto',
                'recursos'        => 'Recursos',
                'metodologia'     => 'Metodología',
                'adaptaciones'    => 'Adaptaciones'
            ];

            foreach ($camposConDefecto as $campo => $titulo) {
                $usarDefecto = !empty($tema["{$campo}_defecto"]) && $tema["{$campo}_defecto"] == 1;
                $contenido = '';

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

            // === Resultados de Aprendizaje y Criterios POR TEMA ===
            $sqlCriterios = "
                SELECT ce.idRA, ce.codigo AS codigo, ce.texto AS criterio
                FROM criterios_temas ct
                INNER JOIN criterios_evaluacion ce ON ct.idRA = ce.idRA AND ct.codigo = ce.codigo
                WHERE ct.idTema = " . (int)$tema['id'] . "
                ORDER BY ce.idRA, ce.codigo";
            $criteriosConRA = consultarBaseDeDatos($sqlCriterios);

            if (!empty($criteriosConRA)) {
                $rasAgrupados = [];
                $prefijo = $idCiclo > 0 ? 'RA' : 'CE';
                foreach ($criteriosConRA as $fila) {
                    $idRA = $fila['idRA'];
                    if (!isset($rasAgrupados[$idRA])) {
                        $sqlRA = "SELECT orden, texto FROM resultados_aprendizaje WHERE id = " . (int)$idRA . " AND idMateria = " . (int)$idMateria;
                        $raData = consultarBaseDeDatos($sqlRA);
                        $textoRA = !empty($raData) ? $prefijo . $raData[0]['orden'] . '. ' . $raData[0]['texto'] : "Resultado de aprendizaje no encontrado (ID: $idRA)";
                        $rasAgrupados[$idRA] = [
                            'texto' => $textoRA,
                            'criterios' => []
                        ];
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

            // === Competencias ===
            $competencias = obtenerCompetenciasDeTema($tema['id']);
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

    $pdf->Output("Temas_$idMateria.pdf", 'I');
}

// Punto de entrada
if (!empty($_GET['idMateria'])) {
    require_once('includes/database.php');
    generarPDF((int)$_GET['idMateria']);
    require_once('includes/database2.php');
} else {
    die('Parámetro idMateria requerido.');
}
?>