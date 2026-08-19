<?php

// Genera el PDF con las preferencias horarias del profesor

require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');
require_once('lib/php/fpdi/fpdi.php');
include ('includes/database.php');

@session_start();

// Devuelve un código numérico asociado al día de la semana (Lunes => 0, Viernes => 4)
function xDia($dia)
{
    if ($dia == 'L') return 0;
    elseif ($dia == 'M') return 1;
    elseif ($dia == 'X') return 2;
    elseif ($dia == 'J') return 3;
    else return 4;
}

// Devuelve un índice numérico asociado a la franja horaria seleccionada:
// 0 => primera hora, 1 => segunda hora, etc
// Función muy específica para la plantilla elegida, sin contar los recreos.
// El código debe cambiarse a mano si cambian las franjas horarias
function yHora($hora)
{
    if ($hora == '07:55') return 0;
    elseif ($hora == '08:50') return 1;
    elseif ($hora == '09:45') return 2;
    elseif ($hora == '11:00') return 3;
    elseif ($hora == '11:55') return 4;
    elseif ($hora == '13:10') return 5;
    elseif ($hora == '14:05') return 6;
    elseif ($hora == '15:05') return 7;
    elseif ($hora == '15:55') return 8;
    elseif ($hora == '17:10') return 9;
    elseif ($hora == '18:00') return 10;
    elseif ($hora == '19:10') return 11;
    else return 12;
}

class MiPDF extends FPDI
{
    var $_tplIdx;    

    // Cabecera de las páginas
    public function Header() 
    {
        if (is_null($this->_tplIdx)) 
        {
            $this->setSourceFile('pdf/desiderata_horario.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
        $this->useTemplate($this->_tplIdx);
     }

    // Pie de las páginas
    public function Footer() 
    {
    }    
}

if ((isset($_SESSION['rol']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'jefeDepartamento')) ||  
    (!empty($_REQUEST['idProfesor']) && isset($_SESSION['idUsuario']) && $_SESSION['idUsuario'] == $_REQUEST['idProfesor']))
{
    // Creamos el PDF a partir de la plantilla con parámetros por defecto:
    // Orientación de la página (portrait), unidad de medida (mm), formato (A4), unicode(true), codificación (UTF-8), 
    // Así sería usando los parámetros: $pdf = new MiPDF(PDF_PAGE_ORIENTATION, 'mm', PDF_PAGE_FORMAT, true, 'UTF-8');
    $pdf = new MiPDF();

    if (empty($_REQUEST['idProfesor']))
    {
        if ($_REQUEST['selEsp'] == 'Todos')
            $resultado = mysqli_query($db, "SELECT profesores.id, profesores.nombre, profesores.telefono, profesores.observaciones_horario, departamentos.nombre AS depto FROM profesores, departamentos WHERE profesores.idDepartamento = departamentos.id AND profesores.activo = 1 AND profesores.idDepartamento = " . $_SESSION['departamentoUsuario'] . " ORDER BY orden");
        else
            $resultado = mysqli_query($db, "SELECT profesores.id, profesores.nombre, profesores.telefono, profesores.observaciones_horario, departamentos.nombre AS depto FROM profesores, departamentos WHERE profesores.idDepartamento = departamentos.id AND profesores.activo = 1 AND profesores.idDepartamento = " . $_SESSION['departamentoUsuario'] . " AND profesores.idEspecialidad = '" . $_REQUEST['selEsp'] . "' ORDER BY orden");
    }
    else
    {
        $resultado = mysqli_query($db, "SELECT profesores.id, profesores.nombre, profesores.telefono, profesores.observaciones_horario, departamentos.nombre AS depto FROM profesores, departamentos WHERE profesores.idDepartamento = departamentos.id AND profesores.id = " . $_REQUEST['idProfesor']);
    }
    while ($fila = mysqli_fetch_assoc($resultado))
    {
        $pdf->SetFont('Helvetica', '', '10');
        $pdf->SetTextColor(0, 0, 0);

        $id = $fila['id'];
        $nombre = $fila['nombre'];
        $telefono = $fila['telefono'];
        $observaciones = $fila['observaciones_horario'];
        $departamento = $fila['depto'];

        $pdf->addPage();

        // Escribimos el nombre del profesor
        $pdf->SetXY(30, 137);
        // El método Cell define una celda con estos parámetros: ancho, alto, contenido, borde, 
        // posición del cursor al terminar (0 = a la derecha de la celda), 
        // alineación del texto (L = izquierda) y si la celda tiene relleno (false).
        $pdf->Cell(100, 10, $nombre, 0, 0, 'L', false);

        // Escribimos el departamento
        $pdf->SetXY(145, 137);
        $pdf->Cell(100, 10, $departamento, 0, 0, 'L', false);

        // Escribimos fecha actual (formato 'dd de mm de yyyy')
        $pdf->SetXY(115, 240);
        $pdf->Cell(50, 10, date('d/m/Y'), 0, 0, 'L', false);
        
        // Escribimos el teléfono de contacto
        if ($telefono)
        {
            $pdf->SetXY(130, 266);
            $pdf->Cell(50, 10, $telefono, 0, 0, 'L', false);    
        }
        
        // Escribimos las observaciones sobre las preferencias de horario
        $pdf->SetXY(50, 257);
        $pdf->SetFont('Helvetica', '', '8');
        $pdf->MultiCell(140, 4, $observaciones, 0, 'J', false, 1, '', '', true, 0, true);

        // Preferencias de horario
        $resultHoras = mysqli_query($db, "SELECT * FROM preferencias_horario WHERE idProfesor = $id");
        while($fila2 = mysqli_fetch_assoc($resultHoras))
        {
            $dia = $fila2['dia'];
            $hora = $fila2['hora'];
            $preferencia = $fila2['preferencia'];

            $posX = 58 + 29.5*xDia($dia);
            $posY = 158 + 5*yHora($hora);

            if (yHora($hora) > 6)
                // Incremento para ubicarlo en tabla de tardes
                $posY += 12;

            if ($preferencia == 'R')
            {
                $pdf->SetXY($posX-5, $posY);
                $pdf->Cell(10, 10, 'X', 0, 0, 'L', false);                
            }
        }
        mysqli_free_result($resultHoras);
    }

    mysqli_free_result($resultado);

    include ('includes/database2.php');

    $pdf->Output();
} else {
    echo "Acceso no permitido";
}

?>