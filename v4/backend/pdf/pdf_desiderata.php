<?php
// Genera un PDF con la tabla de selecciones de uno o varios profesores, y con la tabla de
// preferencias horarias de cada uno/a (fiel a v3/pdf_desiderata.php)
//
// Parámetros:
//  - idEscenario: escenario de desideratas
//  - idProfesor : si se pasa, solo el profesor indicado; si no, los profesores
//                 de la especialidad "selEsp" (o todos con "selEsp=Todos")
//  - idDepartamento: departamento de los profesores (si no se pasa, el de la sesión)
//
// NOTA: muchas de las constantes que aparecen en este código están definidas en el
// fichero lib/php/tcpdf/examples/config/tcpdf_config_alt.php

require_once '../config.php';
require_once '../lib/php/tcpdf/examples/tcpdf_include.php';
require_once '../lib/php/tcpdf/tcpdf.php';
require_once '../lib/php/fpdi/fpdi.php';
require_once '../lib/horarios_compartidas.php';

// La sesión trae el departamento por defecto (v3: cabecera común de la página)
@session_start();

$db = Db::open();

define ('X_ASIGNATURA', 20);
define ('X_GRUPO', 100);
define ('X_LECTIVAS', 153);
define ('X_COMPLEMENTARIAS', 178);
define ('INCREMENTO_Y', 6);
define ('TOTAL_HORAS', 25);  // Total de horas entre lectivas, RD, AP, guardias y complementarias
define ('TOTAL_HORAS_LECTIVAS', 18);
define ('GUARDIAS_POR_DEFECTO', 3); // Número de horas de guardia si se tienen las horas lectivas estipuladas, sin reducción por horas complementarias
define ('DESCUENTO_TUTORIAS', 0);   // Horas complementarias reales por tutoria, en lugar de las oficiales

// Devuelve un índice numérico asociado a la franja horaria seleccionada:
// 0 => primera hora, 1 => segunda hora, etc.
// Se devuelve -1 si no se encuentra la hora buscada
function yHora($hora)
{
    global $db;
    $resultado = -1;
    $posicion = -1;
    $filas = $db->fetchAll("SELECT * FROM horas");
    foreach ($filas as $fila) {
        $posicion++;
        if ($hora == $fila['hora'] && $resultado == -1) {
            $resultado = $posicion;
            // Si es del turno de tarde sumamos una posición más para cuadrar la casilla en el PDF
            if ($fila['turno'] == 'T')
                $resultado++;
        }
    }
    return $resultado;
}

// Creamos el PDF a partir de la plantilla con parámetros por defecto
$pdf = new MiPDF();
$pdf->plantilla = 'pdf/plantilla.pdf';
$pdf->SetFont('Helvetica', '', '10');
$pdf->SetTextColor(0, 0, 0);

if (!empty($_REQUEST['idEscenario']))
{
    $idEscenario = intval($_REQUEST['idEscenario']);

    // Recorremos los profesores solicitados: si no llega ningún "id" de profesor,
    // mostramos los de la especialidad seleccionada
    if (empty($_REQUEST['idProfesor']))
    {
        // Departamento: el indicado en la petición, o el de la sesión si no llega
        $idDepartamento = !empty($_REQUEST['idDepartamento']) ? intval($_REQUEST['idDepartamento']) : intval($_SESSION['idDepartamento']);
        if ($_REQUEST['selEsp'] == 'Todos')
            $resultado = $db->fetchAll("SELECT * FROM profesores WHERE idDepartamento = $idDepartamento AND activo = 1 ORDER BY orden");
        else
            $resultado = $db->fetchAll("SELECT * FROM profesores WHERE idDepartamento = $idDepartamento AND activo = 1 AND idEspecialidad = '" . $_REQUEST['selEsp'] . "' ORDER BY orden");
    }
    else
        $resultado = $db->fetchAll("SELECT * FROM profesores WHERE id = " . intval($_REQUEST['idProfesor']));

    // Creamos una página para cada profesor
    foreach ($resultado as $fila) {
        $id = $fila['id'];
        $nombre = $fila['nombre'] . ' (' . $fila['abreviatura'] . ')';
        $telefono = $fila['telefono'];
        $observaciones = $fila['observaciones_horario'];

        $pdf->addPage();

        // Escribimos el nombre del profesor
        $pdf->SetXY(45, 17);
        $pdf->Cell(100, 10, $nombre . "    -    Teléfono: " . $telefono, 0, 0, 'L', false);

        // Tabla de asignaturas elegidas
        $resultado2 = $db->fetchAll("SELECT materias.nombre AS nombreMat, materias_grupos.horas_complementarias AS horasComp, materias.tipo, seleccion.horas AS horasMat, cursos.nombre as nombreCur, grupos.nombre as nombreGrupo, grupos.mostrar, grupos.horas_complementarias_dual AS horasDual, seleccion.orden AS ordenSel
                                      FROM materias, materias_grupos, cursos, grupos, seleccion
                                      WHERE cursos.id = materias.idCurso AND cursos.id = grupos.idCurso AND grupos.id = seleccion.idGrupo AND materias_grupos.idGrupo = seleccion.idGrupo AND materias_grupos.idMateria = materias.id AND materias.id = seleccion.idMateria AND seleccion.idProfesor = $id AND seleccion.idEscenario = $idEscenario
                                      ORDER BY seleccion.orden");
        // Comenzamos escribiendo materias en Y = 32 e iremos incrementándolo para cada materia
        $posY = 32;
        $total = 0;
        $totalComplementarias = 0;
        $extraDual = 0;
        foreach ($resultado2 as $fila2)
        {
            // Acumulamos un extra si se imparte en algún grupo con docencia en FP dual para las horas complementarias
            if ($fila2['horasDual'] > 0)
                $extraDual = $fila2['horasDual'];

            // Asignatura
            $pdf->SetXY(X_ASIGNATURA, $posY);
            $pdf->Cell(100, 10, $fila2['nombreMat'], 0, 0, 'L', false);

            // Curso
            $pdf->SetXY(X_GRUPO, $posY);
            $pdf->Cell(50, 10, $fila2['nombreCur'] . ($fila2['mostrar'] ? " " . $fila2['nombreGrupo'] : ""), 0, 0, 'C', false);

            // Horas lectivas
            $pdf->SetXY(X_LECTIVAS, $posY);
            $pdf->Cell(30, 10, $fila2['horasMat'], 0, 0, 'L', false);

            // Horas complementarias
            if ($fila2['horasComp'] > 0)
            {
                $pdf->SetXY(X_COMPLEMENTARIAS, $posY);
                $pdf->Cell(30, 10, $fila2['horasComp'], 0, 0, 'L', false);
            }

            $total += $fila2['horasMat'];
            $totalComplementarias += $fila2['horasComp'];
            $posY += INCREMENTO_Y;
        }

        // Horas complementarias adicionales por pertenecer a un grupo con dual (si procede)
        if ($extraDual > 0)
        {
            $totalComplementarias += $extraDual;

            $pdf->SetXY(X_ASIGNATURA, $posY);
            $pdf->Cell(100, 10, "Docencia en grupo con FP Dual", 0, 0, 'L', false);

            $pdf->SetXY(X_COMPLEMENTARIAS, $posY);
            $pdf->Cell(30, 10, $extraDual, 0, 0, 'L', false);

            $posY += INCREMENTO_Y;
        }

        // Reunión de departamento y atención a padres
        $pdf->SetXY(X_ASIGNATURA, $posY);
        $pdf->Cell(100, 10, "Reunión Departamento + Atención a Padres", 0, 0, 'L', false);

        $pdf->SetXY(X_COMPLEMENTARIAS, $posY);
        $pdf->Cell(30, 10, 2, 0, 0, 'L', false);

        $posY += INCREMENTO_Y;

        // Guardias (si procede)
        $guardiasLectivas = 0;
        if ($total < TOTAL_HORAS_LECTIVAS)
        {
            // Sumamos 1 guardia lectiva si no llega al mínimo de horas lectivas
            /* COMENTADO DE MOMENTO
            $guardiasLectivas = 1;
            */
        }
        $total += $guardiasLectivas;
        $guardiasCompl = GUARDIAS_POR_DEFECTO - $totalComplementarias;
        if ($total > TOTAL_HORAS_LECTIVAS)
        {
            $guardiasCompl -= ($total - TOTAL_HORAS_LECTIVAS);
        }

        $pdf->SetXY(X_ASIGNATURA, $posY);
        $pdf->Cell(100, 10, "Guardias", 0, 0, 'L', false);

        if ($guardiasLectivas > 0)
        {
            $pdf->SetXY(X_LECTIVAS, $posY);
            $pdf->Cell(30, 10, $guardiasLectivas, 0, 0, 'L', false);
        }

        $pdf->SetXY(X_COMPLEMENTARIAS, $posY);
        $pdf->Cell(30, 10, $guardiasCompl > 0 ? $guardiasCompl : "-", 0, 0, 'L', false);

        $totalComplementarias += $guardiasCompl;

        // Total horas (lectivas y complementarias)
        $pdf->SetXY(X_LECTIVAS, 98);
        $pdf->Cell(30, 10, $total . "h", 0, 0, 'L', false);

        // Preferencias de horario
        $resultHoras = $db->fetchAll("SELECT * FROM preferencias_horario WHERE idProfesor = $id");
        foreach ($resultHoras as $fila2)
        {
            $dia = $fila2['dia'];
            $hora = $fila2['hora'];
            $preferencia = $fila2['preferencia'];

            // Coordenadas donde poner la marca, sumando a partir de unas coordenadas base (60, 126),
            // dependiendo del día elegido (X) y hora elegida (Y)
            $posX = 60 + 28.5 * xDia($dia);
            $posY = 126 + 6 * yHora($hora);

            // Las preferencias rojas (altas) se marcan con 'XXXXX', y las amarillas (bajas) con un '?'
            if ($preferencia == 'R')
            {
                $pdf->SetXY($posX - 5, $posY);
                $pdf->Cell(10, 10, 'XXXXX', 0, 0, 'L', false);
            }
            else
            {
                $pdf->SetXY($posX, $posY);
                $pdf->Cell(10, 10, '?', 0, 0, 'L', false);
            }
        }

        // Escribimos las observaciones sobre el horario (si las hay)
        if (!empty($observaciones))
        {
            $pdf->SetXY(20, 245);
            $pdf->MultiCell(170, 5, $observaciones, 0, 'J', false, 1, '', '', true, 0, true);
        }
    }

    $db->close();

    $pdf->Output();
}
?>
