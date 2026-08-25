<?php
// Genera un fichero Excel con el resumen de una desiderata (fiel a v3/excel.php)

require_once 'config.php';
require_once 'lib/php/phpexcel/PHPExcel.php';

// Fiel a v3 (página del módulo): requiere sesión iniciada
checkSession();

// Constante compartida por varias tablas de la hoja (horas por profesor)
define('HORAS', 18);

// ============================================================================
// Clases del módulo
// ============================================================================

// Resumen de una desiderata en una hoja de cálculo.
//
// NOTA: se genera con PHPExcel, que está en EOL (ya no se mantiene); se
// conserva porque la app ya lo usa y no hay motivo para cambiar de librería
// en este punto.
//
// Encapsula el relleno de la tabla principal (cursos/materias/profesores) y
// las tablas laterales de horas, y guarda en una propiedad las horas por
// grupo (antes era una variable global $horasGrupo).
class DesiderataExcel
{
    private $objExcel;
    private $db;
    private $escenario;

    // Horas acumuladas por curso+grupo (se rellena al escribir la tabla
    // principal y se lee en horasPorGrupo)
    private $horasGrupo = array();

    public function __construct($objExcel, $db, $escenario)
    {
        $this->objExcel = $objExcel;
        $this->db = $db;
        $this->escenario = $escenario;
    }

    // Orquesta el relleno de la hoja, en el mismo orden que antes
    public function escribir()
    {
        $this->escribirCabecera();
        $this->escribirCursosYMaterias();
        $fila = $this->profesoresPorTipo();
        $fila = $this->horasPorGrupo($fila);
        $fila = $this->horasPorProfesor($fila);
        $this->nombresProfesores($fila);
    }

    // Encabezado de la hoja (título y primera fila de la tabla para filtros)
    private function escribirCabecera()
    {
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle('A3:H3')->getFont()->setBold(true);
        $this->objExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Planificación curso')
                ->setCellValue('A3', 'Módulo')
                ->setCellValue('B3', 'Clase/Tut')
                ->setCellValue('C3', 'Curso')
                ->setCellValue('D3', 'Horas')
                ->setCellValue('E3', 'Oficial')
                ->setCellValue('F3', 'Real')
                ->setCellValue('G3', 'Nombre')
                ->setCellValue('H3', 'Depto');
        $this->objExcel->setActiveSheetIndex(0)
                ->getColumnDimension('A')->setWidth(40);
    }

    // Rellena la tabla principal (izquierda) con los cursos, sus materias y
    // los profesores que las han elegido
    private function escribirCursosYMaterias()
    {
        $contadorCursosGrupos = 0;
        $escenario = $this->escenario;
        $db = $this->db;

        $resultCursos = $db->fetchAll("SELECT cursos.id AS idCurso, cursos.abreviatura AS abrCurso, grupos.id AS idGrupo, grupos.abreviatura AS abrGrupo, grupos.mostrar
                                        FROM cursos, grupos
                                        WHERE cursos.id = grupos.idCurso
                                          AND cursos.id IN (SELECT DISTINCT materias.idCurso
                                                          FROM materias, seleccion
                                                          WHERE materias.id = seleccion.idMateria AND seleccion.idEscenario = $escenario)
                                          AND grupos.id IN (SELECT DISTINCT seleccion.idGrupo
                                                          FROM seleccion
                                                          WHERE seleccion.idEscenario = $escenario)
                                        ORDER BY cursos.orden, grupos.orden");
        $color = "BBBBBB";
        $i = 4;
        foreach ($resultCursos as $fila)
        {
            $this->horasGrupo[$contadorCursosGrupos] = array('curso' => $fila['abrCurso'], 'grupo' => ($fila['mostrar'] ? $fila['abrGrupo'] : ''), 'total' => 0);

            // Hemos obtenido los cursos, y aquí las materias de cada curso
            $resultMaterias = $db->fetchAll("SELECT * FROM materias WHERE idCurso = " . $fila['idCurso'] . " AND id IN (SELECT idMateria FROM seleccion WHERE idEscenario = $escenario)");
            foreach ($resultMaterias as $fila2)
            {
                $cant = $fila2['cantidad'];
                $contCant = 0;
                $resultProfesores = $db->fetchAll("SELECT profesores.abreviatura, profesores.idEspecialidad, departamentos.nombre AS nomDepto, seleccion.horas
                                                   FROM profesores, departamentos, seleccion
                                                   WHERE profesores.id = seleccion.idProfesor
                                                     AND profesores.idDepartamento = departamentos.id
                                                     AND idMateria = " . $fila2['id'] . "
                                                     AND seleccion.idGrupo = " . $fila['idGrupo'] . "
                                                     AND seleccion.idEscenario = $escenario");
                // Primero rellenamos las filas de las materias que han sido seleccionadas
                foreach ($resultProfesores as $fila3)
                {
                    $this->horasGrupo[$contadorCursosGrupos]['total'] += $fila3['horas'];
                    $contCant++;
                    // Si la especialidad a la que pertenece el profesor no es la que tiene asignada esa materia,
                    // pintamos la especialidad del profesor en color rojo
                    if ($fila2['idEspecialidad'] != $fila3['idEspecialidad'])
                        $this->objExcel->setActiveSheetIndex(0)
                                ->getStyle("F$i")->getFont()->getColor()->setRGB('FF0000');
                    $this->objExcel->setActiveSheetIndex(0)
                            ->getStyle('A'.$i.':H'.$i)
                            ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                    $this->objExcel->setActiveSheetIndex(0)
                            ->getStyle('A'.$i.':H'.$i)
                            ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
                    $this->objExcel->setActiveSheetIndex(0)
                            ->setCellValue('A'.$i, $fila2['nombre'])
                            ->setCellValue('B'.$i, $fila2['computables_horas_grupo'] ? 'C' : 'T')
                            ->setCellValue('C'.$i, $fila['abrCurso'] . ($fila['mostrar'] ? $fila['abrGrupo'] : ''))
                            ->setCellValue('D'.$i, $fila3['horas'])
                            ->setCellValue('E'.$i, $fila2['idEspecialidad'])
                            ->setCellValue('F'.$i, $fila3['idEspecialidad'])
                            ->setCellValue('G'.$i, $fila3['abreviatura'])
                            ->setCellValue('H'.$i, $fila3['nomDepto']);
                    $i++;
                }

                // Después rellenamos las filas de materias que no ha seleccionado nadie (columnas F y G en blanco)
                // Cuando todo sea correcto, no se debería entrar en este "for"
                for ($contCant; $contCant < $cant; $contCant++)
                {
                    $this->objExcel->setActiveSheetIndex(0)
                            ->getStyle('A'.$i.':H'.$i)
                            ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                    $this->objExcel->setActiveSheetIndex(0)
                            ->getStyle('A'.$i.':H'.$i)
                            ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
                    $this->objExcel->setActiveSheetIndex(0)
                            ->setCellValue('A'.$i, $fila2['nombre'])
                            ->setCellValue('B'.$i, $fila2['computables_horas_grupo'] ? 'C' : 'T')
                            ->setCellValue('C'.$i, $fila['abrCurso'] . ($fila['mostrar'] ? $fila['abrGrupo'] : ''))
                            ->setCellValue('D'.$i, $fila2['horas'])
                            ->setCellValue('E'.$i, $fila2['idEspecialidad'])
                            ->setCellValue('F'.$i, '')
                            ->setCellValue('G'.$i, '')
                            ->setCellValue('H'.$i, '');
                    $i++;
                }
            }

            $contadorCursosGrupos++;

            // Materias que no ha seleccionado nadie (tampoco se debería entrar aquí)
            $resultMaterias = $db->fetchAll("SELECT * FROM materias WHERE idCurso = " . $fila['idCurso'] . " AND idDepartamento IN (SELECT idDepartamento FROM departamentos_escenarios WHERE idEscenario = $escenario) AND id NOT IN (SELECT idMateria FROM seleccion WHERE idEscenario = $escenario)");
            foreach ($resultMaterias as $fila2)
            {
                $this->objExcel->setActiveSheetIndex(0)
                        ->getStyle('A'.$i.':H'.$i)
                        ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                $this->objExcel->setActiveSheetIndex(0)
                        ->getStyle('A'.$i.':H'.$i)
                        ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
                $this->objExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $fila2['nombre'])
                        ->setCellValue('B'.$i, $fila2['computables_horas_grupo'] ? 'C' : 'T')
                        ->setCellValue('C'.$i, $fila['abrCurso'] . ($fila['mostrar'] ? $fila['abrGrupo'] : ''))
                        ->setCellValue('D'.$i, $fila2['horas'])
                        ->setCellValue('E'.$i, $fila2['idEspecialidad'])
                        ->setCellValue('F'.$i, '')
                        ->setCellValue('G'.$i, '')
                        ->setCellValue('H'.$i, '');
                $i++;
            }

            // Alternamos color de fondo con cada curso
            if ($color == "BBBBBB")
                $color = "FFFFFF";
            else
                $color = "BBBBBB";
        }
    }

    // Tabla derecha de horas de cada tipo de profesor
    private function profesoresPorTipo()
    {
        $escenario = $this->escenario;
        $db = $this->db;

        // Cabecera de la tabla
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle('J3')->getFont()->setBold(true);
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle('J5:M5')
                ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'BBBBBB'))));
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle('J5:M5')
                ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
        $this->objExcel->setActiveSheetIndex(0)
                ->setCellValue('J3', 'Profesores por tipo')
                ->setCellValue('J5', 'Depto.')
                ->setCellValue('K5', 'Tipo prof')
                ->setCellValue('L5', 'Horas')
                ->setCellValue('M5', 'Profesores');

        $i = 6;
        $total = 0;

        $resultDeptos = $db->fetchAll("SELECT * FROM departamentos WHERE id IN (SELECT idDepartamento FROM departamentos_escenarios WHERE idEscenario = $escenario) ORDER BY nombre");
        foreach ($resultDeptos as $fila)
        {
            $resultEsp = $db->fetchAll("SELECT * FROM especialidades WHERE idDepartamento = " . $fila['id']);
            foreach ($resultEsp as $fila2)
            {
                // Obtenemos las especialidades de los profesores del departamento, y la suma de horas que han seleccionado
                $fila3 = $db->fetchOne("SELECT SUM(seleccion.horas) AS total
                                         FROM materias, seleccion, profesores
                                         WHERE materias.id = seleccion.idMateria
                                           AND profesores.id = seleccion.idProfesor
                                           AND profesores.idEspecialidad = '" . $fila2['id'] . "'
                                           AND profesores.idDepartamento = " . $fila['id'] . "
                                           AND seleccion.idEscenario = $escenario");
                $cant = $fila3['total'];
                $total += $cant;
                $this->objExcel->setActiveSheetIndex(0)
                        ->getStyle("J$i:M$i")
                        ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));

                // Mostramos el grupo, las horas acumuladas por sus profesores, y cuántos profesores hacen falta para esas horas
                $this->objExcel->setActiveSheetIndex(0)
                        ->setCellValue('J'.$i, $fila['nombre'])
                        ->setCellValue('K'.$i, $fila2['id'])
                        ->setCellValue('L'.$i, $cant)
                        ->setCellValue('M'.$i, $cant / HORAS);
                $i++;
            }
        }

        return $i;
    }

    // Tabla derecha que muestra las horas de cada grupo (suma de las horas de
    // sus materias más las tutorías si es el caso)
    private function horasPorGrupo($fila)
    {
        $i = $fila + 3;

        // Cabecera
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle("J$i")->getFont()->setBold(true);
        $this->objExcel->setActiveSheetIndex(0)
                ->setCellValue("J$i", 'Horas por grupo');
        $i += 2;
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle("J$i:K$i")
                ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'BBBBBB'))));
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle("J$i:K$i")
                ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
        $this->objExcel->setActiveSheetIndex(0)
                ->setCellValue("J$i", 'Grupo')
                ->setCellValue("K$i", 'Horas');

        $i++;

        for ($j = 0; $j < count($this->horasGrupo); $j++)
        {
            $nomGrupo = $this->horasGrupo[$j]['curso'] . $this->horasGrupo[$j]['grupo'];
            $horas = $this->horasGrupo[$j]['total'];

            $this->objExcel->setActiveSheetIndex(0)
                    ->getStyle("J$i:K$i")
                    ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
            $this->objExcel->setActiveSheetIndex(0)
                    ->setCellValue('J'.$i, $nomGrupo)
                    ->setCellValue('K'.$i, $horas);
            $i++;
        }

        return $i;
    }

    // Tabla derecha que muestra cuántas horas lectivas ha seleccionado cada profesor
    private function horasPorProfesor($fila)
    {
        $escenario = $this->escenario;
        $db = $this->db;

        $i = $fila + 2;

        // Cabecera
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle("J$i")->getFont()->setBold(true);
        $this->objExcel->setActiveSheetIndex(0)
                ->setCellValue("J$i", 'Horas por profesor');
        $i += 2;
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle("J$i:L$i")
                ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'BBBBBB'))));
        $this->objExcel->setActiveSheetIndex(0)
                ->getStyle("J$i:L$i")
                ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
        $this->objExcel->setActiveSheetIndex(0)
                ->setCellValue('J'.$i, 'Depto')
                ->setCellValue('K'.$i, 'Prof.')
                ->setCellValue('L'.$i, 'Horas')
                ->setCellValue('M'.$i, 'Sobra');

        $color = "FFFFFF";
        $i++;

        $resultDeptos = $db->fetchAll("SELECT * FROM departamentos WHERE id IN (SELECT idDepartamento FROM departamentos_escenarios WHERE idEscenario = $escenario) ORDER BY nombre");
        foreach ($resultDeptos as $filaD)
        {
            $resultEsp = $db->fetchAll("SELECT * FROM especialidades WHERE idDepartamento = " . $filaD['id']);
            foreach ($resultEsp as $fila2)
            {
                // Dividimos en especialidades de profesores, y obtenemos la suma de horas de cada profesor de una especialidad
                $filaInicio = $i;
                $totalParcial = 0;
                $resultCant = $db->fetchAll("SELECT profesores.abreviatura, SUM(seleccion.horas) AS total
                                              FROM materias, seleccion, profesores
                                              WHERE materias.id = seleccion.idMateria
                                                AND profesores.id = seleccion.idProfesor
                                                AND profesores.idEspecialidad = '" . $fila2['id'] . "'
                                                AND profesores.idDepartamento = " . $filaD['id'] . "
                                                AND seleccion.idEscenario = $escenario
                                              GROUP BY profesores.abreviatura, profesores.orden
                                              ORDER BY profesores.orden");
                foreach ($resultCant as $fila3)
                {
                    // Mostramos la abreviatura de cada profesor y sus horas lectivas seleccionadas
                    $prof = $fila3['abreviatura'];
                    $cant = $fila3['total'];
                    $totalParcial += $cant;
                    $this->objExcel->setActiveSheetIndex(0)
                            ->getStyle("J$i:L$i")
                            ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                    $this->objExcel->setActiveSheetIndex(0)
                            ->getStyle("J$i:L$i")
                            ->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
                    $this->objExcel->setActiveSheetIndex(0)
                            ->setCellValue('J'.$i, $filaD['nombre'])
                            ->setCellValue('K'.$i, $prof)
                            ->setCellValue('L'.$i, $cant)
                            ->setCellValue('M'.$i, $cant - HORAS);
                    $i++;
                }

                // Volvemos a calcular el total de horas lectivas de la especialidad (igual que en la función
                // 'profesoresPorTipo'). Si no coincide este dato con la suma de horas de los profesores de la
                // especialidad, lo marcamos en rojo
                $fila4 = $db->fetchOne("SELECT SUM(seleccion.horas) AS total
                                         FROM materias, seleccion, profesores
                                         WHERE materias.id = seleccion.idMateria
                                           AND profesores.id = seleccion.idProfesor
                                           AND profesores.idEspecialidad = '" . $fila2['id'] . "'
                                           AND profesores.idDepartamento = " . $filaD['id'] . "
                                           AND seleccion.idEscenario = $escenario");
                $cantGlobal = $fila4['total'];

                $this->objExcel->setActiveSheetIndex(0)
                        ->getStyle("N$filaInicio")
                        ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'BBBBBB'))));
                $this->objExcel->setActiveSheetIndex(0)
                        ->setCellValue('N'.$filaInicio, $fila2['id'])
                        ->setCellValue('O'.$filaInicio, $totalParcial);
                if ($cantGlobal != $totalParcial)
                    $this->objExcel->setActiveSheetIndex(0)
                            ->getStyle('O'.$filaInicio)->getFont()->getColor()->setRGB('FF0000');

                // Alternamos colores con cada grupo de profesores
                if ($color == 'FFFFFF')
                    $color = 'BBBBBB';
                else
                    $color = 'FFFFFF';
            }
        }

        return $i;
    }

    // Tabla derecha con los nombres de todos los profesores
    private function nombresProfesores($fila)
    {
        $escenario = $this->escenario;
        $db = $this->db;

        $i = $fila + 2;

        $resultDeptos = $db->fetchAll("SELECT * FROM departamentos WHERE id IN (SELECT idDepartamento FROM departamentos_escenarios WHERE idEscenario = $escenario) ORDER BY nombre");
        foreach ($resultDeptos as $filaD)
        {
            $resultEsp = $db->fetchAll("SELECT * FROM especialidades WHERE idDepartamento = " . $filaD['id']);
            foreach ($resultEsp as $fila2)
            {
                $resultProfes = $db->fetchAll("SELECT abreviatura, nombre FROM profesores WHERE idEspecialidad = '" . $fila2['id'] . "' AND idDepartamento = " . $filaD['id'] . " ORDER BY orden");
                foreach ($resultProfes as $fila3)
                {
                    $abr = $fila3['abreviatura'];
                    $nombre = $fila3['nombre'];
                    $this->objExcel->setActiveSheetIndex(0)
                           ->setCellValue('J'.$i, $abr)
                           ->setCellValue('K'.$i, $nombre);
                    $i++;
                }
                $i++;
            }
            $i += 2;
        }
    }
}

// ============================================================================
// Generación del fichero
// ============================================================================
$escenario = getOptimoInt('idEscenario');
if ($escenario <= 0) {
    sendJSONError('Escenario inválido', 400);
}

$objExcel = new PHPExcel();
$objExcel->getProperties()->setTitle("Desideratas");
$objExcel->setActiveSheetIndex(0);
$objExcel->getActiveSheet()->setTitle("Desideratas");

$bd = Db::open();
$desiderata = new DesiderataExcel($objExcel, $bd, $escenario);
$desiderata->escribir();
$bd->close();

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="desideratas.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
$objWriter->save('php://output');
?>
