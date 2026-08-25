<?php
// Genera el PDF con las preferencias horarias de uno o varios profesores (fiel a v3/pdf_preferencias.php)
//
// Parámetros:
//  - idProfesor : si se pasa, solo el profesor indicado
//  - selEsp     : especialidad (o "Todos") si no se indica profesor
//  - idDepartamento: departamento de los profesores (si no se pasa, el de la sesión)

require_once 'config.php';
require_once 'lib/php/tcpdf/examples/tcpdf_include.php';
require_once 'lib/php/tcpdf/tcpdf.php';
require_once 'lib/php/fpdi/fpdi.php';
require_once 'lib/horarios_compartidas.php';

// La sesión trae los permisos y el departamento por defecto
@session_start();

$db = Db::open();

// Devuelve un índice numérico asociado a la franja horaria seleccionada:
// 0 => primera hora, 1 => segunda hora, etc.
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

// Solo lo ven los permisos superiores, o el propio profesor
$super = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'jefeDepartamento');
$esPropio = !empty($_REQUEST['idProfesor']) && isset($_SESSION['idUsuario']) && $_SESSION['idUsuario'] == $_REQUEST['idProfesor'];

if ($super || $esPropio)
{
    // Creamos el PDF a partir de la plantilla con parámetros por defecto
    $pdf = new MiPDF();
    $pdf->plantilla = 'pdf/desiderata_horario.pdf';

    if (empty($_REQUEST['idProfesor']))
    {
        // Departamento: el indicado en la petición, o el de la sesión si no llega
        $idDepartamento = !empty($_REQUEST['idDepartamento']) ? intval($_REQUEST['idDepartamento']) : intval($_SESSION['idDepartamento']);
        if ($_REQUEST['selEsp'] == 'Todos')
            $resultado = $db->fetchAll("SELECT profesores.id, profesores.nombre, profesores.telefono, profesores.observaciones_horario, departamentos.nombre AS depto
                                        FROM profesores, departamentos
                                        WHERE profesores.idDepartamento = departamentos.id AND profesores.activo = 1 AND profesores.idDepartamento = $idDepartamento
                                        ORDER BY orden");
        else
            $resultado = $db->fetchAll("SELECT profesores.id, profesores.nombre, profesores.telefono, profesores.observaciones_horario, departamentos.nombre AS depto
                                        FROM profesores, departamentos
                                        WHERE profesores.idDepartamento = departamentos.id AND profesores.activo = 1 AND profesores.idDepartamento = $idDepartamento AND profesores.idEspecialidad = '" . $_REQUEST['selEsp'] . "'
                                        ORDER BY orden");
    }
    else
        $resultado = $db->fetchAll("SELECT profesores.id, profesores.nombre, profesores.telefono, profesores.observaciones_horario, departamentos.nombre AS depto
                                     FROM profesores, departamentos
                                     WHERE profesores.idDepartamento = departamentos.id AND profesores.id = " . intval($_REQUEST['idProfesor']));

    foreach ($resultado as $fila)
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
        $pdf->Cell(100, 10, $nombre, 0, 0, 'L', false);

        // Escribimos el departamento
        $pdf->SetXY(145, 137);
        $pdf->Cell(100, 10, $departamento, 0, 0, 'L', false);

        // Escribimos la fecha actual
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
        $resultHoras = $db->fetchAll("SELECT * FROM preferencias_horario WHERE idProfesor = $id");
        foreach ($resultHoras as $fila2)
        {
            $dia = $fila2['dia'];
            $hora = $fila2['hora'];
            $preferencia = $fila2['preferencia'];

            $posX = 58 + 29.5 * xDia($dia);
            $posY = 158 + 5 * yHora($hora);

            if (yHora($hora) > 6)
                // Incremento para ubicarlo en la tabla de tardes
                $posY += 12;

            if ($preferencia == 'R')
            {
                $pdf->SetXY($posX - 5, $posY);
                $pdf->Cell(10, 10, 'X', 0, 0, 'L', false);
            }
        }
    }

    $db->close();

    $pdf->Output();
}
else
{
    echo "Acceso no permitido";
}
?>
