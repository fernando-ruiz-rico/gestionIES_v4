<?php

@session_start();

require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');

class MiPDF extends TCPDF
{
    // Cabecera de las páginas
    public function Header() 
    {
        $this->setY(15);
        $this->SetFont('helvetica', 'I', 12);
        $this->Cell(0, 10, "I.E.S. San Vicente", 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }

    // Pie de las páginas
    public function Footer() 
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 10, 'Pág '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }    
}

if (!empty($_REQUEST['curso']) && !empty($_REQUEST['evaluacion']))
{
    include('includes/database.php');

    // Recopilar información para la portada

    $idEvaluacion = (int)$_REQUEST['evaluacion']; // Casting a int por seguridad
    
    $result = mysqli_query($db, "SELECT nombre FROM evaluaciones WHERE id = $idEvaluacion");
    $fila = mysqli_fetch_assoc($result);
    $evaluacion = $fila['nombre'];

    $curso = mysqli_real_escape_string($db, $_REQUEST['curso']); // Escapar string
    $idDepartamento = (int)$_REQUEST['departamento']; // Casting a int por seguridad

    $categoria = $_REQUEST['categoria'] == 'FP' ? "categoria = 'FP'" : "categoria = 'ESO' OR categoria = 'BACH'";
    
    $result = mysqli_query($db, "SELECT nombre FROM departamentos WHERE id = " . $idDepartamento);
    $fila = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    $nomDepartamento = $fila['nombre'];
    
    // Preparar el documento PDF
    $pdf = new MiPDF();
    $pdf->SetAuthor('I.E.S. San Vicente');
    $pdf->SetTitle("Seguimiento programaciones {$_REQUEST['categoria']}. Departamento de $nomDepartamento. Curso $curso, $evaluacion");
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Imprimir primera página con título, etc.
    
    $pdf->AddPage();

    $pdf->Write(0, str_repeat(PHP_EOL, 15), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 30);
    $pdf->Write(0, "Seguimiento de programaciones" . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 16);
    $pdf->Write(0, "Curso $curso, $evaluacion" . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Departamento de $nomDepartamento ({$_REQUEST['categoria']})" . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    
    // Carga inicial de contenidos por defecto del departamento
    // Nota: Aunque cambiamos a seguimiento de aula, los datos generales del departamento siguen estando en esta tabla
    $result = mysqli_query($db, "SELECT * FROM seguimiento_programaciones_departamento WHERE idDepartamento = $idDepartamento AND curso = '$curso' AND evaluacion=$idEvaluacion");
    $funcionamiento_departamento="No hay datos disponibles";
    $actividades_extraescolares="No hay datos disponibles";
    $temporalizacion_defecto="No hay datos disponibles"; // Se usará si el profe deja vacía la temporalización
    $inclusion_defecto="No hay datos disponibles"; // Se usará si el profe deja vacía la inclusión  
    
    if ($fila = mysqli_fetch_assoc($result))
    {
        $funcionamiento_departamento = $fila['funcionamiento_departamento'];
        $actividades_extraescolares = $fila['actividades_extraescolares'];
        if(!empty($fila['temporalizacion_defecto'])) {
            $temporalizacion_defecto = $fila['temporalizacion_defecto'];
        }
    }
    mysqli_free_result($result);

    // ---------------------------------------------------------
    // SECCIÓN 1: TEMPORALIZACIÓN
    // ---------------------------------------------------------

    $pdf->AddPage();
    
    // Obtenemos cursos
    $categoria = $_REQUEST['categoria'] == 'FP' ? "categoria = 'FP'" : "categoria = 'ESO' OR categoria = 'BACH'";
    $resultCursos = mysqli_query($db, "SELECT id, nombre FROM cursos WHERE $categoria ORDER BY orden");

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Write(0, "1. SEGUIMIENTO DE LA PROGRAMACIÓN (con respecto a la temporalización que figura en las Propuestas Pedagógicas)" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
    
    while ($filaCurso = mysqli_fetch_assoc($resultCursos))
    {
        // Obtenemos los grupos de este curso
        $sqlGrupos = "SELECT id, nombre FROM grupos WHERE idCurso = " . $filaCurso['id'] . " ORDER BY orden";
        $resultGrupos = mysqli_query($db, $sqlGrupos);

        while ($filaGrupo = mysqli_fetch_assoc($resultGrupos))
        {
            // Buscamos datos en seguimiento_programaciones_aula para este grupo y departamento
            // Hacemos JOIN con materias para filtrar por departamento y con profesores para mostrar el nombre
            $sqlSeguimiento = "SELECT spa.temporalizacion, m.nombre as materia, p.nombre as profesor
                               FROM seguimiento_programaciones_aula spa
                               INNER JOIN materias m ON spa.idMateria = m.id
                               INNER JOIN profesores p ON spa.idProfesor = p.id
                               WHERE spa.idGrupo = " . $filaGrupo['id'] . "
                               AND m.idDepartamento = $idDepartamento
                               AND spa.curso = '$curso'
                               AND spa.evaluacion = $idEvaluacion
                               ORDER BY m.nombre, p.nombre";
            
            $resultSeguimiento = mysqli_query($db, $sqlSeguimiento);

            // Si hay resultados para este grupo, imprimimos cabeceras
            if (mysqli_num_rows($resultSeguimiento) > 0) 
            {
                $pdf->SetFont('helvetica', 'B', 13);
                $pdf->SetTextColor(0, 0, 128); // Un azul oscuro para diferenciar niveles
                $pdf->Write(0, PHP_EOL . PHP_EOL . $filaCurso['nombre'] . " " . $filaGrupo['nombre'], '', 0, 'L', true, 0, false, false, 0); 
                $pdf->SetTextColor(0, 0, 0);

                while ($filaSpa = mysqli_fetch_assoc($resultSeguimiento))
                {
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
    }
    
    mysqli_data_seek($resultCursos, 0); // Reiniciamos el puntero de cursos para la siguiente sección
    
    // ---------------------------------------------------------
    // SECCIÓN 2: RESULTADOS
    // ---------------------------------------------------------

    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Write(0, "2. VALORACIÓN DE LOS RESULTADOS ACADÉMICOS (con especial atención a los grupos de desdoble o refuerzo, si los hay, detallando cumplimiento de programación, incidencia sobre la convivencia del grupo y resultados académicos)" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
    
    while ($filaCurso = mysqli_fetch_assoc($resultCursos))
    {
        // Obtenemos los grupos de este curso
        $sqlGrupos = "SELECT id, nombre FROM grupos WHERE idCurso = " . $filaCurso['id'] . " ORDER BY orden";
        $resultGrupos = mysqli_query($db, $sqlGrupos);

        while ($filaGrupo = mysqli_fetch_assoc($resultGrupos))
        {
            // Query similar a la anterior pero obteniendo datos de resultados
            $sqlSeguimiento = "SELECT spa.resultados, spa.inclusion, spa.num_aprobados, spa.num_suspensos, spa.num_otros, 
                                      m.nombre as materia, p.nombre as profesor
                               FROM seguimiento_programaciones_aula spa
                               INNER JOIN materias m ON spa.idMateria = m.id
                               INNER JOIN profesores p ON spa.idProfesor = p.id
                               WHERE spa.idGrupo = " . $filaGrupo['id'] . "
                               AND m.idDepartamento = $idDepartamento
                               AND spa.curso = '$curso'
                               AND spa.evaluacion = $idEvaluacion
                               ORDER BY m.nombre, p.nombre";
            
            $resultSeguimiento = mysqli_query($db, $sqlSeguimiento);

            if (mysqli_num_rows($resultSeguimiento) > 0) 
            {
                $pdf->SetFont('helvetica', 'B', 13);
                $pdf->SetTextColor(0, 0, 128);
                $pdf->Write(0, PHP_EOL . PHP_EOL . $filaCurso['nombre'] . " " .  $filaGrupo['nombre'], '', 0, 'L', true, 0, false, false, 0); 
                $pdf->SetTextColor(0, 0, 0);

                while ($filaSpa = mysqli_fetch_assoc($resultSeguimiento))
                {
                    $pdf->SetFont('helvetica', 'B', 12);
                    $pdf->Write(0, PHP_EOL . PHP_EOL . $filaSpa['materia'] . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
                    
                    $pdf->SetFont('helvetica', '', 12);

                    // Datos numéricos y cálculo de porcentaje
                    $aprobados = (int)$filaSpa['num_aprobados'];
                    $suspensos = (int)$filaSpa['num_suspensos'];
                    $otros = (int)$filaSpa['num_otros'];
                    $total_alumnos = $aprobados + $suspensos + $otros;
                    
                    $porcentaje = 0;
                    if ($total_alumnos > 0) {
                        $porcentaje = round(($aprobados / $total_alumnos) * 100, 2);
                    }

                    $pdf->SetFont('helvetica', 'I', 12);
                    $stats = "Aprobados: $aprobados ($porcentaje%)  |  Suspensos: $suspensos  |  Otros: $otros";
                    $pdf->Write(0, $stats . PHP_EOL. PHP_EOL, '', 0, 'L', true, 0, false, false, 0);

                    $pdf->SetFont('helvetica', '', 12);

                    // Análisis de resultados (Texto)
                    if (!empty($filaSpa['resultados'])) {
                        $pdf->WriteHTML($filaSpa['resultados'] . PHP_EOL, true, false, true, false, '');
                    }                    
                    
                    $pdf->Ln(2); // Pequeño salto
                }
            }
            mysqli_free_result($resultSeguimiento);
        }
        mysqli_free_result($resultGrupos);
    }
    

    // ---------------------------------------------------------
    // SECCIÓN 3: INCLUSIÓN DEL ALUMNADO
    // ---------------------------------------------------------

    $pdf->AddPage();
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Write(0, "3. INCLUSIÓN DEL ALUMNADO. VALORACIÓN DE LOS RESULTADOS DE ALUMNADO A QUIEN SE LE HA APLICADO ALGÚN TIPO DE RESPUESTA EDUCATIVA" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
    
    mysqli_data_seek($resultCursos, 0); // Reiniciamos el puntero de cursos para la siguiente sección

    $inclusion_vacio = true;

    while ($filaCurso = mysqli_fetch_assoc($resultCursos))
    {
        // Obtenemos los grupos de este curso
        $sqlGrupos = "SELECT id, nombre FROM grupos WHERE idCurso = " . $filaCurso['id'] . " ORDER BY orden";
        $resultGrupos = mysqli_query($db, $sqlGrupos);

        while ($filaGrupo = mysqli_fetch_assoc($resultGrupos))
        {
            // Buscamos datos en seguimiento_programaciones_aula para este grupo y departamento
            // Hacemos JOIN con materias para filtrar por departamento y con profesores para mostrar el nombre
            $sqlSeguimiento = "SELECT spa.inclusion, m.nombre as materia, p.nombre as profesor
                               FROM seguimiento_programaciones_aula spa
                               INNER JOIN materias m ON spa.idMateria = m.id
                               INNER JOIN profesores p ON spa.idProfesor = p.id
                               WHERE spa.idGrupo = " . $filaGrupo['id'] . "
                               AND m.idDepartamento = $idDepartamento
                               AND spa.curso = '$curso'
                               AND spa.evaluacion = $idEvaluacion
                               AND spa.inclusion IS NOT NULL
                               AND spa.inclusion != ''
                               AND spa.inclusion NOT LIKE '<p><br></p>'
                               AND spa.inclusion NOT LIKE '<p>&nbsp;</p>'
                               AND spa.inclusion NOT LIKE '<br>'
                               AND spa.inclusion REGEXP '>[^<]+'                               
                               ORDER BY m.nombre, p.nombre";
            
            $resultSeguimiento = mysqli_query($db, $sqlSeguimiento);

            // Si hay resultados para este grupo, imprimimos cabeceras
            if (mysqli_num_rows($resultSeguimiento) > 0) 
            {
                $pdf->SetFont('helvetica', 'B', 13);
                $pdf->SetTextColor(0, 0, 128); // Un azul oscuro para diferenciar niveles
                $pdf->Write(0, PHP_EOL . PHP_EOL . $filaCurso['nombre'] . " " . $filaGrupo['nombre'], '', 0, 'L', true, 0, false, false, 0); 
                $pdf->SetTextColor(0, 0, 0);

                while ($filaSpa = mysqli_fetch_assoc($resultSeguimiento))
                {                   
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
    }

    if ($inclusion_vacio) {
        // Si no se ha añadido nada, ponemos el texto por defecto
        $pdf->SetFont('helvetica', '', 12);
        $pdf->WriteHTML($inclusion_defecto . PHP_EOL, true, false, true, false, '');
    } 

    mysqli_free_result($resultCursos);
    
    // ---------------------------------------------------------
    // SECCIÓN 4: CONTENIDO COMÚN (DEPARTAMENTO)
    // ---------------------------------------------------------

    $pdf->AddPage();    

    $pdf->SetFont('helvetica', 'B', 14);

    $pdf->Write(0, "4. VALORACIÓN DE LAS HORAS DE ATENCIÓN A PENDIENTES, DESDOBLES, REFUERZOS, TALLERES DE ACOMPAÑAMIENTO Y MANTENIMIENTO (INFORMÁTICA) A LO LARGO DEL TRIMESTRE" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->WriteHTML($funcionamiento_departamento . PHP_EOL, true, false, true, false, '');

    $pdf->SetFont('helvetica', 'B', 14);

    $evaluacion = intval($_REQUEST['evaluacion']) + 1;
    $pdf->Write(0, PHP_EOL . PHP_EOL . "5. ACTIVIDADES EXTRAESCOLARES PROGRAMADAS PARA LA {$evaluacion}ª EVALUACIÓN" . PHP_EOL . PHP_EOL, '', 0, 'L', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->WriteHTML($actividades_extraescolares . PHP_EOL, true, false, true, false, '');        

    $pdf->Output();
    
    include('includes/database2.php');
}
?>