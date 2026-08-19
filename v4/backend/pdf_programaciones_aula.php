<?php
// Genera un PDF con el contenido completo de una programación de aula

require_once('lib/php/tcpdf/examples/tcpdf_include.php');
require_once('lib/php/tcpdf/tcpdf.php');
require_once('includes/constantes.php');
require_once('includes/utilidades.php');
require_once('includes/consultas_bd.php');
require_once('includes/generar_apartado_resumen_ce.php');

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
// Obtiene los datos necesarios del tema para mostrarlos en su sección de la programación de aula
// -------------------------------
function generarContenidoTema($tema, $id_departamento, $id_ciclo)
{
    // Información inicial extraída de la propia tabla del tema
    $estiloEncabezado = "text-align:center; background-color:#f2f2f2; font-weight:bold;";
    $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">
        <tr><th colspan=\"2\" style=\"$estiloEncabezado\">Descripción</th></tr>
        <tr><td colspan=\"2\" align=\"justify\">{$tema['descripcion']}</td></tr>
        <tr><th style=\"$estiloEncabezado\">Trimestre</th><th style=\"$estiloEncabezado\">Sesiones</th></tr>
        <tr><td align=\"center\">{$tema['trimestre']}</td><td align=\"center\">{$tema['horas']}</td></tr>
        </table><br>";

    // Justificación / Contextualización
    $titulo = $id_ciclo > 0 ? 'Justificación' : 'Contextualización';
    $html .= "<h3>{$titulo}</h3><br>{$tema['justificacion']}<br>";

    // RA y CE
    $ra_tema = consultarBaseDeDatos("SELECT DISTINCT criterios_temas.idRA,resultados_aprendizaje.orden FROM resultados_aprendizaje, criterios_temas WHERE resultados_aprendizaje.id = criterios_temas.idRA AND criterios_temas.idTema = {$tema['id']} ORDER BY resultados_aprendizaje.orden");
    $titulo = $id_ciclo > 0 ? 'Resultados de aprendizaje y criterios de evaluación' : 'Competencias específicas y criterios de evaluación';
    $html .= "<h3>{$titulo}</h3><br>";

    if (count($ra_tema) > 0)
    {
        $prefijo = $id_ciclo > 0 ? 'RA' : 'CE';
        $html .= "<ul>";
        foreach($ra_tema as $ra)
        {
            $datos_ra = consultarBaseDeDatos("SELECT * FROM resultados_aprendizaje WHERE id = {$ra['idRA']}")[0];
            $ce_tema = consultarBaseDeDatos("SELECT criterios_evaluacion.* FROM criterios_evaluacion, criterios_temas WHERE criterios_evaluacion.idRA = criterios_temas.idRA AND criterios_evaluacion.codigo = criterios_temas.codigo AND criterios_evaluacion.idRA = {$ra['idRA']} AND criterios_temas.idTema = {$tema['id']} ORDER BY codigo");
            $html .= "<li>{$prefijo}{$datos_ra['orden']}. {$datos_ra['texto']}";
            $html .=  "<ul>";
            foreach($ce_tema as $ce)
            {
                $html .= "<li>{$datos_ra['orden']}.{$ce['codigo']}) {$ce['texto']}</li>";
            }
            $html .= "</ul></li><br>";
        }
        $html .= "</ul>";
    } else {
        $html .= "<p>No se han asignado RA/CE a esta unidad</p>";
    }

    // Competencias
    $competencias_tema = consultarBaseDeDatos("SELECT competencias_ciclos.* FROM competencias_ciclos, competencias_temas WHERE competencias_ciclos.id = competencias_temas.idCompetencia AND competencias_temas.idTema = {$tema['id']} ORDER BY orden");
    $titulo = $id_ciclo > 0 ? 'Competencias profesionales y para la empleabilidad' : 'Competencias clave';
    $html .= "<h3>{$titulo}</h3><br>";
    if (count($competencias_tema) > 0)
    {
        $html .= "<ul>";
        foreach($competencias_tema as $competencia)
        {
            $html .= "<li>{$competencia['codigo']}) {$competencia['texto']}</li>";
        }
        $html .= "</ul><br>";
    } else {
        $html .= "<p>No se han asignado competencias a esta unidad.</p>";
    }

    // Contenidos / Saberes básicos
    $titulo = $id_ciclo > 0 ? 'Contenidos' : 'Saberes básicos';
    $html .= "<h3>{$titulo}</h3><br>{$tema['contenidos']}<br>";

    // Secuenciación / Actividades
    $titulo = $id_ciclo > 0 ? 'Secuenciación' : 'Actividades';
    $html .= "<h3>{$titulo}</h3>{$tema['secuenciacion']}";

    // Evaluación
    // $html .= "<h3>Evaluación</h3><br>{$tema['evaluacion']}<br>";

    // Espacios, Recursos y adaptaciones
    $recursos = $tema['recursos'];
    $adaptaciones = $tema['adaptaciones'];
    $espacios = $tema['contexto'];
    $datos_defecto = consultarBaseDeDatos("SELECT * FROM contenidos_defecto_temas WHERE idDepartamento = $id_departamento")[0];
    if($tema['recursos_defecto'])
    {
        $recursos = $datos_defecto['recursos'];
    }
    if($tema['adaptaciones_defecto'])
    {
        $adaptaciones = $datos_defecto['adaptaciones'];
    }
    if($tema['contexto_defecto'])
    {
        $espacios = $datos_defecto['contexto'];
    }

    $html .= "<h3>Organización de los espacios de aprendizaje</h3>$espacios";
    $html .= "<h3>Recursos y materiales</h3>$recursos";
    $html .= "<h3>Medidas de atención para la respuesta educativa para la inclusión</h3>$adaptaciones";

    return $html;
}

// -------------------------------
// Genera la tabla inicial identificativa de materia, grupo, etc
// -------------------------------
function generarContenidoContexto($datos)
{
    $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">
                <tr><td width=\"35%\"><strong>Centro:</strong></td><td width=\"65%\">IES San Vicente</td></tr>
                <tr><td><strong>Materia:</strong></td><td>{$datos['materia']}</td></tr>
                <tr><td><strong>Curso y grupo:</strong></td><td>{$datos['curso']} {$datos['grupo']}</td></tr>
                <tr><td><strong>Horas semanales:</strong></td><td>{$datos['horas']}</td></tr>
                <tr><td><strong>Curso académico:</strong></td><td>{$datos['cursoAcademico']}</td></tr>
                <tr><td><strong>Profesor:</strong></td><td>{$datos['profesor']}</td></tr>
            </table>";

    return $html;
}

// -------------------------------
// Genera la tabla de distribución de tiempo por evaluación (trimestre)
// -------------------------------
function generarDistribucionTiempo($idMateria, $idCiclo)
{
    // Estilo común para encabezados
    $estiloEncabezado = "text-align:center; background-color:#f2f2f2; font-weight:bold;";

    // 1. Obtener todos los temas ordenados por orden
    $sqlTemas = "
        SELECT id, orden, titulo, horas, trimestre
        FROM temas
        WHERE idMateria = " . (int)$idMateria . "
        ORDER BY orden";
    $temas = consultarBaseDeDatos($sqlTemas);

    if (empty($temas)) {
        return '<p>No hay temas definidos para esta materia.</p>';
    }

    // 2. Agrupar temas por trimestre
    $gruposPorTrimestre = array();
    foreach ($temas as $t) {
        $trimestre = (int)$t['trimestre'];
        if (!isset($gruposPorTrimestre[$trimestre])) {
            $gruposPorTrimestre[$trimestre] = array();
        }
        $gruposPorTrimestre[$trimestre][] = $t;
    }

    // 3. Etiquetas de evaluación
    $etiquetasEvaluacion = array(
        1 => '1ª EVALUACIÓN',
        2 => '2ª EVALUACIÓN',
        3 => '3ª EVALUACIÓN',
    );

    // 4. Iniciar tabla
    $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\" style=\"font-size:12px;\">\n";
    $html .= "  <thead>\n";
    $html .= "    <tr nobr=\"true\">\n";
    $html .= "      <th width=\"25%\" style=\"{$estiloEncabezado}\">EVALUACIÓN</th>\n";
    $html .= "      <th width=\"60%\" style=\"{$estiloEncabezado}\">SITUACIÓN DE APRENDIZAJE</th>\n";
    $html .= "      <th width=\"15%\" style=\"{$estiloEncabezado}\">SESIONES</th>\n";
    $html .= "    </tr>\n";
    $html .= "  </thead>\n";
    $html .= "  <tbody>\n";

    $totalSesiones = 0;

    // Procesar cada trimestre
    $prefijo = $idCiclo > 0 ? 'UP' : 'SA';
    for ($i = 1; $i <= 3; $i++) {
        if (!isset($gruposPorTrimestre[$i]) || empty($gruposPorTrimestre[$i])) {
            continue;
        }

        $nombreEvaluacion = isset($etiquetasEvaluacion[$i]) ? $etiquetasEvaluacion[$i] : "EVALUACIÓN {$i}";
        $temasDelTrimestre = $gruposPorTrimestre[$i];
        $numFilas = count($temasDelTrimestre);

        // Primera fila del trimestre (con rowspan)
        $primerTema = $temasDelTrimestre[0];
        $horasPrimer = (int)$primerTema['horas'];
        $totalSesiones += $horasPrimer;

        $html .= "    <tr nobr=\"true\">\n";
        $html .= "      <td width=\"25%\" rowspan=\"{$numFilas}\" style=\"vertical-align:middle; text-align:center; font-weight:bold;\">{$nombreEvaluacion}</td>\n";
        $html .= "      <td width=\"60%\">{$prefijo}{$primerTema['orden']}. {$primerTema['titulo']}</td>\n";
        $html .= "      <td width=\"15%\" align=\"center\">{$horasPrimer}</td>\n";
        $html .= "    </tr>\n";

        // Filas restantes del trimestre
        for ($j = 1; $j < $numFilas; $j++) {
            $tema = $temasDelTrimestre[$j];
            $horas = (int)$tema['horas'];
            $totalSesiones += $horas;
            $html .= "    <tr nobr=\"true\">\n";
            $html .= "      <td width=\"60%\">{$prefijo}{$tema['orden']}. {$tema['titulo']}</td>\n";
            $html .= "      <td width=\"15%\" align=\"center\">{$horas}</td>\n";
            $html .= "    </tr>\n";
        }
    }

    // Fila EXTRAESCOLAR (valor fijo según ejemplo)
    /*$html .= "    <tr nobr=\"true\">\n";
    $html .= "      <td colspan=\"2\" style=\"font-weight:bold;\">EXTRAESCOLAR</td>\n";
    $html .= "      <td align=\"center\">2</td>\n";
    $html .= "    </tr>\n";
    $totalSesiones += 2;*/

    // Fila TOTAL
    $html .= "    <tr nobr=\"true\">\n";
    $html .= "      <td colspan=\"2\" style=\"text-align:right; font-weight:bold;\">TOTAL:</td>\n";
    $html .= "      <td align=\"center\" style=\"font-weight:bold;\">{$totalSesiones}</td>\n";
    $html .= "    </tr>\n";

    $html .= "  </tbody>\n";
    $html .= "</table>";

    return $html;
}

// -------------------------------
// Genera el PDF completo
// -------------------------------
function generarPDFProgramacionAula($idMateria, $idGrupo, $idProfesor)
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
    $pdf->Write(0, "Programación de aula" . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Curso: {$cursoAcademico}" . str_repeat(PHP_EOL, 3), '', 0, 'C', true, 0, false, false, 0);
    $pdf->Write(0, "Departamento de " . $departamento . str_repeat(PHP_EOL, 2), '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Write(0, $profesor, '', 0, 'C', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 12);

    // Mostrar datos identificativos
    $contadorIndice++;
    $pdf->AddPage();
    $pdf->Bookmark($contadorIndice . '. Datos identificativos', 0, 0, '', '');
    $pdf->WriteHTML('<h1>' . $contadorIndice . '. Datos identificativos' . '</h1><br>', true, false, true, false, '');
    $pdf->WriteHTML(generarContenidoContexto($datosIdentificativos), true, false, true, false, '');
    
    // Desarrollo de unidades
    $contadorIndice++;
    $titulo = $idCiclo > 0 ? 'Unidades de programación' : 'Situaciones de aprendizaje';
    $pdf->Bookmark($contadorIndice . ". $titulo", 0, 0, '', '');
    $pdf->WriteHTML("<h1>{$contadorIndice}. {$titulo}</h1><br>", true, false, true, false, '');
    $contenidoInicial = consultarBaseDeDatos("SELECT texto FROM programaciones_aula_temas WHERE idGrupo=$idGrupo AND idProfesor=$idProfesor AND idTema=0");
    if (estaVacio($contenidoInicial) || estaVacio($contenidoInicial[0]['texto'])) {
        $contenidoInicial = $idCiclo > 0 ? "<p>Esta programación de aula se basa en la programación didáctica del departamento para el módulo profesional correspondiente.</p><p>El desarrollo completo de cada tema, incluidas las actividades y tareas que permiten alcanzar los Resultados de Aprendizaje y cumplir los Criterios de Evaluación, está disponible en un curso dentro de la plataforma Aules.</p>" : "<p>Esta programación de aula está basada en la propuesta didáctica del departamento para la asignatura.</p><p>El desarrollo completo de cada Situación de Aprendizaje, incluidas las actividades que se realizan y que, por tanto, permiten obtener las competencias, están subidas en un curso dentro de la plataforma Aules.</p>";
    }
    else {
        $contenidoInicial = $contenidoInicial[0]['texto'];
    }
    $pdf->WriteHTML($contenidoInicial, true, false, true, false, '');

    $prefijo = $idCiclo > 0 ? 'Tema ' : 'SA';
    $temas = consultarBaseDeDatos("SELECT * FROM temas WHERE idMateria=$idMateria ORDER BY orden");
    foreach($temas as $tema)
    {
        $id = $tema['id'];
        $titulo = "{$prefijo}{$tema['orden']}. {$tema['titulo']}";
        $pdf->AddPage();
        $pdf->Bookmark("    $titulo", 0, 0, '', '');
        $pdf->WriteHTML("<h2>{$titulo}</h2><br>", true, false, true, false, '');
        // Tabla resumen inicial del tema
        $datosTema = generarContenidoTema($tema, $idDepartamento, $idCiclo);
        $pdf->WriteHTML($datosTema, true, false, true, false, '');
        // Contenido asociado (si lo hay)
        $programacion_tema = consultarBaseDeDatos("SELECT * FROM programaciones_aula_temas WHERE idTema=$id AND idGrupo=$idGrupo AND idProfesor=$idProfesor");
        if(count($programacion_tema) > 0)
        {
            $pdf->WriteHTML("<br><p><strong>Distribución temporal</strong></p>", true, false, true, false, '');
            $pdf->WriteHTML($programacion_tema[0]['texto'], true, false, true, false, '');
        }
    }

    // Tabla resumen de criterios de evaluación por tema
    $contadorIndice++;
    $sa_up = $idCiclo > 0 ? 'Unidades de Programación' : 'Situaciones de Aprendizaje';
    $titulo = "$contadorIndice. Tablas resumen $sa_up - Criterios de Evaluación";
    $pdf->AddPage();
    $pdf->Bookmark($titulo, 0, 0, '', '');
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $apartado = generarTablasResumenCriteriosEvaluacion($idMateria, $idCiclo);
    $pdf->WriteHTML($apartado, true, false, true, false, '');

    // Evaluación
    $contadorIndice++;
    $titulo = "$contadorIndice. Evaluación";
    $pdf->AddPage();
    $pdf->Bookmark($titulo, 0, 0, '', '');
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $apartado = obtenerContenidoApartado(55, $idMateria, $idDepartamento);
    $pdf->WriteHTML($apartado, true, false, true, false, '');

    // Metodología
    $contadorIndice++;
    $titulo = "$contadorIndice. Metodología y agrupamientos";
    $pdf->AddPage();
    $pdf->Bookmark($titulo, 0, 0, '', '');
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $apartado = obtenerContenidoApartado(7, $idMateria, $idDepartamento);
    $pdf->WriteHTML($apartado, true, false, true, false, '');

    // Recursos y espacios
    $contadorIndice++;
    $titulo = "$contadorIndice. Recursos y espacios";
    $pdf->AddPage();
    $pdf->Bookmark($titulo, 0, 0, '', '');
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $apartado = obtenerContenidoApartado(17, $idMateria, $idDepartamento);
    $pdf->WriteHTML($apartado, true, false, true, false, '');

    // Distribución de tiempo por evaluación
    $contadorIndice++;
    $titulo = "$contadorIndice. Distribución del tiempo";
    $pdf->AddPage();
    $pdf->Bookmark($titulo, 0, 0, '', '');
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $apartado = generarDistribucionTiempo($idMateria, $idCiclo);
    $pdf->WriteHTML($apartado, true, false, true, false, '');

    // Actividades complementarias y extraescolares
    $contadorIndice++;
    $titulo = "$contadorIndice. Actividades complementarias y extraescolares";
    $pdf->AddPage();
    $pdf->Bookmark($titulo, 0, 0, '', '');
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $apartado = obtenerContenidoApartado(19, $idMateria, $idDepartamento);
    $pdf->WriteHTML($apartado, true, false, true, false, '');

    // Medidas de atención para la respuesta educativa para la inclusión
    $contadorIndice++;
    $titulo = "$contadorIndice. Medidas de atención para la respuesta educativa para la inclusión";
    $pdf->AddPage();
    $pdf->Bookmark($titulo, 0, 0, '', '');
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $apartado = obtenerContenidoApartado(15, $idMateria, $idDepartamento);
    $pdf->WriteHTML($apartado, true, false, true, false, '');

    // Referencias
    $contadorIndice++;
    $titulo = "$contadorIndice. Referencias";
    $pdf->AddPage();
    $pdf->Bookmark($titulo, 0, 0, '', '');
    $pdf->WriteHTML("<h1>$titulo</h1><br>", true, false, true, false, '');
    $apartado = obtenerContenidoApartado(57, $idMateria, $idDepartamento);
    $pdf->WriteHTML($apartado, true, false, true, false, '');

    // Índice
    $pdf->addTOCPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->MultiCell(0, 0, 'Índice de contenidos', 0, 'C', 0, 1, '', '', true, 0);
    $pdf->Ln();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->addTOC(2);
    $pdf->endTOCPage();

    // Salida
    $pdf->Output();
}

// -------------------------------
// Punto de entrada principal
// -------------------------------
if (!empty($_REQUEST['idMateria'])) {
    require_once('includes/database.php');
    generarPDFProgramacionAula((int)$_REQUEST['idMateria'], (int)$_REQUEST['idGrupo'], (int)$_REQUEST['idProfesor']);
    require_once('includes/database2.php');
}
