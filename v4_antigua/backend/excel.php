<?php

// Genera un fichero Excel con el resumen de una desiderata

$horasGrupo = array();

// Encabezado de la hoja (título y primera fila de la tabla para filtros)
function escribirCabecera($objExcel)
{
    $objExcel->setActiveSheetIndex(0)
            ->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $objExcel->setActiveSheetIndex(0)
            ->getStyle('A3:H3')->getFont()->setBold(true);
    $objExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Planificación curso')
            ->setCellValue('A3', 'Módulo')
            ->setCellValue('B3', 'Clase/Tut')
            ->setCellValue('C3', 'Curso')
            ->setCellValue('D3', 'Horas')
            ->setCellValue('E3', 'Oficial')
            ->setCellValue('F3', 'Real')
            ->setCellValue('G3', 'Nombre')
            ->setCellValue('H3', 'Depto');    
    $objExcel->setActiveSheetIndex(0)
            ->getColumnDimension('A')->setWidth(40);
}

// Rellena la tabla principal (izquierda) con los cursos, sus materias y los profesores que las han elegido
function escribirCursosYMaterias($escenario, $objExcel, $db)
{
    global $horasGrupo;
    $contadorCursosGrupos = 0;
    
    $resultCursos = mysqli_query($db, "SELECT cursos.id AS idCurso, cursos.abreviatura AS abrCurso, grupos.id AS idGrupo, grupos.abreviatura AS abrGrupo, grupos.mostrar FROM cursos, grupos WHERE cursos.id = grupos.idCurso AND cursos.id IN (SELECT DISTINCT materias.idCurso FROM materias, seleccion WHERE materias.id = seleccion.idMateria AND seleccion.idEscenario=$escenario) and grupos.id IN (SELECT DISTINCT seleccion.idGrupo FROM seleccion WHERE seleccion.idEscenario=$escenario) ORDER BY cursos.orden, grupos.orden");
    $color = "BBBBBB";
    $i = 4;
    while($fila = mysqli_fetch_assoc($resultCursos))
    {
        $horasGrupo[$contadorCursosGrupos] = array('curso' => $fila['abrCurso'], 'grupo' => ($fila['mostrar']?$fila['abrGrupo']:''), 'total' => 0);
        
        // Hemos obtenido los cursos, y aquí las materias de cada curso
        $resultMaterias = mysqli_query($db, "SELECT * FROM materias WHERE idCurso = " . $fila['idCurso'] . " AND id IN (SELECT idMateria FROM seleccion WHERE idEscenario = $escenario)");
        while($fila2 = mysqli_fetch_assoc($resultMaterias))
        {
            $cant = $fila2['cantidad'];            
            $contCant = 0;
            $resultProfesores = mysqli_query($db, "SELECT profesores.abreviatura, profesores.idEspecialidad, departamentos.nombre AS nomDepto, seleccion.horas FROM profesores, departamentos, seleccion WHERE profesores.id = seleccion.idProfesor AND profesores.idDepartamento = departamentos.id AND idMateria = " . $fila2['id'] . " AND seleccion.idGrupo = " . $fila['idGrupo'] . " AND seleccion.idEscenario = $escenario");
            // Primero rellenamos las filas de las materias que han sido seleccionadas
            while($fila3 = mysqli_fetch_assoc($resultProfesores))
            {
                $horasGrupo[$contadorCursosGrupos]['total'] += $fila3['horas'];
                $contCant++;
                // Si la especialidad a la que pertenece el profesor no es la que tiene asignada esa materia, pintamos la especialidad del profesor en color rojo
                if ($fila2['idEspecialidad'] != $fila3['idEspecialidad'])
                    $objExcel->setActiveSheetIndex(0)
                            ->getStyle("F$i")->getFont()->getColor()->setRGB('FF0000');
                $objExcel->setActiveSheetIndex(0)
                        ->getStyle('A'.$i.':H'.$i)
                        ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                $objExcel->setActiveSheetIndex(0)
                        ->getStyle('A'.$i.':H'.$i)
                        ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
                $objExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $fila2['nombre'])
                        ->setCellValue('B'.$i, $fila2['computables_horas_grupo']?'C':'T')
                        ->setCellValue('C'.$i, $fila['abrCurso'] . ($fila['mostrar']?$fila['abrGrupo']:''))
                        ->setCellValue('D'.$i, $fila3['horas'])
                        ->setCellValue('E'.$i, $fila2['idEspecialidad'])
                        ->setCellValue('F'.$i, $fila3['idEspecialidad'])
                        ->setCellValue('G'.$i, $fila3['abreviatura'])
                        ->setCellValue('H'.$i, $fila3['nomDepto']);
                $i++;
            }
            mysqli_free_result($resultProfesores);
        
            // Después rellenamos las filas de materias que no ha seleccionado nadie (columnas F y G en blanco)
            // Cuando todo sea correcto, no se debería entrar en este "for"
            for($contCant; $contCant < $cant; $contCant++)
            {
                $objExcel->setActiveSheetIndex(0)
                        ->getStyle('A'.$i.':H'.$i)
                        ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                $objExcel->setActiveSheetIndex(0)
                        ->getStyle('A'.$i.':H'.$i)
                        ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
                $objExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $fila2['nombre'])
                        ->setCellValue('B'.$i, $fila2['computables_horas_grupo']?'C':'T')
                        ->setCellValue('C'.$i, $fila['abrCurso'] . ($fila['mostrar']?$fila['abrGrupo']:''))
                        ->setCellValue('D'.$i, $fila2['horas'])
                        ->setCellValue('E'.$i, $fila2['idEspecialidad'])
                        ->setCellValue('F'.$i, '')
                        ->setCellValue('G'.$i, '')
                        ->setCellValue('H'.$i, '');
                $i++;
            }
        }
        mysqli_free_result($resultMaterias);
        
        $contadorCursosGrupos++;
            
        
        // Materias que no ha seleccionado nadie (tampoco se debería entrar aquí)
        $resultMaterias = mysqli_query($db, "SELECT * FROM materias WHERE idCurso = " . $fila['idCurso'] . " AND idDepartamento IN (SELECT idDepartamento FROM departamentos_escenarios WHERE idEscenario = $escenario) AND id NOT IN (SELECT idMateria FROM seleccion WHERE idEscenario = $escenario)");
        while ($fila2 = mysqli_fetch_assoc($resultMaterias))
        {
            $objExcel->setActiveSheetIndex(0)
                    ->getStyle('A'.$i.':H'.$i)
                    ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
            $objExcel->setActiveSheetIndex(0)
                    ->getStyle('A'.$i.':H'.$i)
                    ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
            $objExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $fila2['nombre'])
                    ->setCellValue('B'.$i, $fila2['computables_horas_grupo']?'C':'T')
                    ->setCellValue('C'.$i, $fila['abrCurso'] . ($fila['mostrar']?$fila['abrGrupo']:''))
                    ->setCellValue('D'.$i, $fila2['horas'])
                    ->setCellValue('E'.$i, $fila2['idEspecialidad'])
                    ->setCellValue('F'.$i, '')
                    ->setCellValue('G'.$i, '')
                    ->setCellValue('H'.$i, '');
            $i++;
        }
        mysqli_free_result($resultMaterias);
        
        // Alternamos color de fondo con cada curso
        if ($color == "BBBBBB")
            $color = "FFFFFF";
        else
            $color = "BBBBBB";
    }
    mysqli_free_result($resultCursos);
}

// Tabla derecha de horas de cada tipo de profesor
function profesoresPorTipo($escenario, $objExcel, $db)
{
    // Cabecera de la tabla
    $objExcel->setActiveSheetIndex(0)
            ->getStyle('J3')->getFont()->setBold(true);
    $objExcel->setActiveSheetIndex(0)
            ->getStyle('J5:M5')
            ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'BBBBBB'))));
    $objExcel->setActiveSheetIndex(0)
            ->getStyle('J5:M5')
            ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
    $objExcel->setActiveSheetIndex(0)
            ->setCellValue('J3', 'Profesores por tipo')
            ->setCellValue('J5', 'Depto.')
            ->setCellValue('K5', 'Tipo prof')
            ->setCellValue('L5', 'Horas')
            ->setCellValue('M5', 'Profesores');
            
    $i = 6;
    $total = 0;
    
    $resultDeptos = mysqli_query($db, "SELECT * FROM departamentos WHERE id IN (SELECT idDepartamento FROM departamentos_escenarios WHERE idEscenario = $escenario) ORDER BY nombre");
    while ($fila = mysqli_fetch_assoc($resultDeptos))
    {
        $resultEsp = mysqli_query($db, "SELECT * FROM especialidades WHERE idDepartamento = " . $fila['id']);
        while ($fila2 = mysqli_fetch_assoc($resultEsp))
        {
            // Obtenemos las especialidades de los profesores del departamento, y la suma de horas que han seleccionado
            $resultCant = mysqli_query($db, "SELECT SUM(seleccion.horas) AS total FROM materias, seleccion, profesores WHERE materias.id = seleccion.idMateria AND profesores.id = seleccion.idProfesor AND profesores.idEspecialidad = '" . $fila2['id'] . "' AND profesores.idDepartamento = " .$fila['id'] . " AND seleccion.idEscenario=$escenario");
            $fila3 = mysqli_fetch_assoc($resultCant);
            mysqli_free_result($resultCant);
            $cant = $fila3['total'];
            $total += $cant;
            $objExcel->setActiveSheetIndex(0)
                    ->getStyle("J$i:M$i")
                    ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));

            // Mostramos el grupo, las horas acumuladas por sus profesores, y cuántos profesores hacen falta para esas horas (diviendo entre la constante HORAS, que guarda las horas de docencia por profesor)
            $objExcel->setActiveSheetIndex(0)
                    ->setCellValue('J'.$i, $fila['nombre'])
                    ->setCellValue('K'.$i, $fila2['id'])
                    ->setCellValue('L'.$i, $cant)
                    ->setCellValue('M'.$i, $cant / HORAS);
            $i++;
        }

        mysqli_free_result($resultEsp);        
    }
    
    return $i;
}

// Tabla derecha que muestra las horas de cada grupo (suma de las horas de sus materias más las tutorías si es el caso)
function horasPorGrupo($escenario, $objExcel, $db, $fila)
{
    global $horasGrupo;
    
    $i = $fila + 3;
    
    // Cabecera
    
    $objExcel->setActiveSheetIndex(0)
            ->getStyle("J$i")->getFont()->setBold(true);
    $objExcel->setActiveSheetIndex(0)
            ->setCellValue("J$i", 'Horas por grupo');
    $i+=2;
    $objExcel->setActiveSheetIndex(0)
            ->getStyle("J$i:K$i")
            ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'BBBBBB'))));
    $objExcel->setActiveSheetIndex(0)
            ->getStyle("J$i:K$i")
            ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
    $objExcel->setActiveSheetIndex(0)
            ->setCellValue("J$i", 'Grupo')
            ->setCellValue("K$i", 'Horas');
            
    $i++;
    
    for ($j = 0; $j < count($horasGrupo); $j++)
    {
        $nomGrupo = $horasGrupo[$j]['curso'] . $horasGrupo[$j]['grupo'];
        $horas = $horasGrupo[$j]['total'];
        
        $objExcel->setActiveSheetIndex(0)
                ->getStyle("J$i:K$i")
                ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
        $objExcel->setActiveSheetIndex(0)
                ->setCellValue('J'.$i, $nomGrupo)
                ->setCellValue('K'.$i, $horas);
        $i++;        
    }
    
    return $i;
}

// Tabla derecha que muestra cuántas horas lectivas ha seleccionado cada profesor
function horasPorProfesor($escenario, $objExcel, $db, $fila)
{
    $i = $fila + 2;
    
    // Cabecera
    
    $objExcel->setActiveSheetIndex(0)
            ->getStyle("J$i")->getFont()->setBold(true);
    $objExcel->setActiveSheetIndex(0)
            ->setCellValue("J$i", 'Horas por profesor');
    $i += 2;
    $objExcel->setActiveSheetIndex(0)
            ->getStyle("J$i:L$i")
            ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'BBBBBB'))));
    $objExcel->setActiveSheetIndex(0)
            ->getStyle("J$i:L$i")
            ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
    $objExcel->setActiveSheetIndex(0)
            ->setCellValue("J$i", 'Depto')
            ->setCellValue("K$i", 'Prof.')
            ->setCellValue("L$i", 'Horas')
            ->setCellValue("M$i", 'Sobra');
            
    $color = "FFFFFF";
    $i++;
    
    $resultDeptos = mysqli_query($db, "SELECT * FROM departamentos WHERE id IN (SELECT idDepartamento FROM departamentos_escenarios WHERE idEscenario = $escenario) ORDER BY nombre");
    while ($fila = mysqli_fetch_assoc($resultDeptos))
    {    
        $resultGrupos = mysqli_query($db, "SELECT * FROM especialidades WHERE idDepartamento = " . $fila['id']);
        while ($fila2 = mysqli_fetch_assoc($resultGrupos))
        {
            // Dividimos en especialidades de profesores, y obtenemos la suma de horas de cada profesor de una especialidad
            $filaInicio = $i;
            $totalParcial = 0;
            $resultCant = mysqli_query($db, "SELECT profesores.abreviatura, SUM(seleccion.horas) AS total FROM materias, seleccion, profesores WHERE materias.id = seleccion.idMateria AND profesores.id = seleccion.idProfesor AND profesores.idEspecialidad = '" . $fila2['id'] . "' AND profesores.idDepartamento = " . $fila['id'] . " AND seleccion.idEscenario = $escenario GROUP BY profesores.abreviatura ORDER BY profesores.orden");
            while ($fila3 = mysqli_fetch_assoc($resultCant))
            {
                // Mostramos la abreviatura de cada profesor y sus horas lectivas seleccionadas
                $prof = $fila3['abreviatura'];
                $cant = $fila3['total'];
                $totalParcial += $cant;
                $objExcel->setActiveSheetIndex(0)
                        ->getStyle("J$i:L$i")
                        ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                $objExcel->setActiveSheetIndex(0)
                        ->getStyle("J$i:L$i")
                        ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
                $objExcel->setActiveSheetIndex(0)
                        ->setCellValue('J'.$i, $fila['nombre'])
                        ->setCellValue('K'.$i, $prof)
                        ->setCellValue('L'.$i, $cant)
                        ->setCellValue('M'.$i, $cant - HORAS);
                $i++;
            }
            mysqli_free_result($resultCant);

            // Volvemos a calcular el total de horas lectivas de la especialidad (igual que en la función 'profesoresPorTipo'
            // Si no coincide este dato con la suma de horas lectivas de los profesores de la especialidad, lo marcamos en rojo

            $resultCant = mysqli_query($db, "SELECT SUM(seleccion.horas) AS total FROM materias, seleccion, profesores WHERE materias.id = seleccion.idMateria AND profesores.id = seleccion.idProfesor AND profesores.idEspecialidad = '" . $fila2['id'] . "' AND profesores.idDepartamento = " . $fila['id'] . " AND seleccion.idEscenario=$escenario");
            $fila3 = mysqli_fetch_assoc($resultCant);
            mysqli_free_result($resultCant);
            $cantGlobal = $fila3['total'];

            $objExcel->setActiveSheetIndex(0)
                    ->getStyle("N$filaInicio")
                    ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'BBBBBB'))));
            $objExcel->setActiveSheetIndex(0)
                    ->setCellValue('N'.$filaInicio, $fila2['id'])
                    ->setCellValue('O'.$filaInicio, $totalParcial);
            if ($cantGlobal != $totalParcial)
                $objExcel->setActiveSheetIndex(0)
                        ->getStyle('O'.$filaInicio)->getFont()->getColor()->setRGB('FF0000');

            // Alternamos colores con cada grupo de profesores
            if ($color = 'FFFFFF')
                $color = 'BBBBBB';
            else
                $color = 'FFFFFF';

        }
        mysqli_free_result($resultGrupos);
    }
    mysqli_free_result($resultDeptos);
    
    return $i;
}

function nombresProfesores($escenario, $objExcel, $db, $fila)
{
    $i = $fila + 2;
    
    $resultDeptos = mysqli_query($db, "SELECT * FROM departamentos WHERE id IN (SELECT idDepartamento FROM departamentos_escenarios WHERE idEscenario = $escenario) ORDER BY nombre");
    while ($fila = mysqli_fetch_assoc($resultDeptos))
    {    
        $resultGrupos = mysqli_query($db, "SELECT * FROM especialidades WHERE idDepartamento = " . $fila['id']);
        while ($fila2 = mysqli_fetch_assoc($resultGrupos))
        {
            $resultProfes = mysqli_query($db, "SELECT abreviatura, nombre FROM profesores WHERE idEspecialidad = '" . $fila2['id'] . "' AND idDepartamento = " . $fila['id'] . " ORDER BY orden");
            while ($fila3 = mysqli_fetch_assoc($resultProfes))
            {
                $abr = $fila3['abreviatura'];
                $nombre = $fila3['nombre'];
                $objExcel->setActiveSheetIndex(0)
                       ->setCellValue('J'.$i, $abr)
                       ->setCellValue('K'.$i, $nombre);
                $i++;
            }
            $i++;
        }
        $i += 2;
        mysqli_free_result($resultGrupos);
    }
    mysqli_free_result($resultDeptos);
}

@session_start();

define('HORAS', 18);

require_once('lib/php/phpexcel/PHPExcel.php');
include ('includes/database.php');

$escenario = $_REQUEST['idEscenario'];

$objExcel = new PHPExcel();
$objExcel->getProperties()->setTitle("Desideratas");

$objExcel->setActiveSheetIndex(0);
$objExcel->getActiveSheet()->setTitle("Desideratas");

escribirCabecera($objExcel);
escribirCursosYMaterias($escenario, $objExcel, $db);
$fila = profesoresPorTipo($escenario, $objExcel, $db);
$fila = horasPorGrupo($escenario, $objExcel, $db, $fila);
$fila = horasPorProfesor($escenario, $objExcel, $db, $fila);
nombresProfesores($escenario, $objExcel, $db, $fila);

include ('includes/database2.php');

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="desideratas.xls"');
header('Cache-Control: max-age=0');
 
$objWriter=PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
$objWriter->save('php://output');

?>
