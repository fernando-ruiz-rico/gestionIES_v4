<!doctype html>
<html lang="es"><head>
<link href="https://iessanvicente.com/css/fonts-google.css" rel="stylesheet" type="text/css">
<meta charset="utf-8">
<title>I.E.S. San Vicente</title>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1 shrink-to-fit=no">
<link href="https://iessanvicente.com/css2019/bootstrap.min.css" rel="stylesheet">
<link href="https://iessanvicente.com/css2019/sv2019.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://iessanvicente.com/css2019/font-awesome.css" media="screen">
<link rel="shortcut icon" href="https://iessanvicente.com/favicon.ico">
<meta name="google-site-verification" content="c_NbzOOzsouKsw_aJzmn7v2L8ZW7vfRgats_0D-lZis">
</head>
<body>
<header>    
    <div class="d-flex flex-column flex-md-row align-items-center p-2 px-md-4 mb-3 bg-dark border-bottom box-shadow">
        <p class="my-0 mr-md-auto"><a href="/index.php"><img class="imgfluid" src="https://iessanvicente.com/logo50invertido72.png" alt="I.E.S. San Vicente"></a></p>
    </div>
</header>

<div class="container" style="min-height: 600px;">

<?php

require_once('includes/utilidades.php');

if (!empty($_REQUEST['idDepartamento']))
{
    $departamento = consultarBaseDeDatos("SELECT * FROM departamentos WHERE id = " . $_REQUEST['idDepartamento']);
    if(count($departamento) > 0)
    {
        echo "<h1>Propuesta pedagógica del departamento de " . $departamento[0]['nombre'] . "</h1>";
    }

    // Ciclos formativos (si los hay)

    echo '<div class="row my-4"><div class="col-md-12"><div class="card">';
    echo '<div class="card-header"><h2><a class="card-link-title collapsed" data-toggle="collapse" href="#ciclos">Ciclos formativos </a></h2></div>';
    echo '<div id="ciclos" class="collapse"><div class="card-body">';

    // Obtenemos los ciclos ordenados por nombre
    $ciclos = consultarBaseDeDatos("SELECT * FROM ciclos WHERE id IN (SELECT DISTINCT cursos_ciclos.idCiclo FROM cursos_ciclos, cursos, materias WHERE cursos_ciclos.idCurso = cursos.id AND cursos.id = materias.idCurso AND materias.idDepartamento = {$_REQUEST['idDepartamento']}) ORDER BY nivel, nombre");
    echo '<div class="row">';
    foreach($ciclos as $ciclo)
    {
        echo '<div class="col-md-12 my-2"><div class="card">';
        echo '<div class="card-header"><h3 class="mt-2"><a class="card-link-title collapsed" data-toggle="collapse" href="#ciclo' . $ciclo['id'] . '">' . $ciclo['nombre'] . ' </a></h3></div>';
        echo '<div id="ciclo' . $ciclo['id'] . '" class="collapse"><div class="card-body">';

        // Obtenemos los cursos de ese ciclo
        $cursos = consultarBaseDeDatos("SELECT DISTINCT cursos.id, cursos.nombre, cursos_ciclos.orden FROM cursos, cursos_ciclos, materias WHERE cursos.id = cursos_ciclos.idCurso AND cursos.id = materias.idCurso AND materias.idDepartamento = " . $_REQUEST['idDepartamento'] . " AND cursos_ciclos.idCiclo = " . $ciclo['id'] . ' ORDER BY cursos_ciclos.orden');
        echo '<div class="row">';
        foreach($cursos as $curso)
        {
            echo '<div class="col-md-6">';
            echo '<h4>' . $curso['nombre'] . '</h4>';
            // Obtenemos las materias de ese curso
            $materias = consultarBaseDeDatos("SELECT DISTINCT materias.id, materias.nombre FROM materias WHERE materias.idDepartamento = " . $_REQUEST['idDepartamento'] . " AND materias.tiene_programacion = 1 AND materias.idCurso = " . $curso['id']);
            echo "<ul>";
            foreach($materias as $materia)
            {
                echo '<li><a href="https://iessanvicente.com/iconsultas/gestionIES_v3/pdf_programaciones.php?idMateria=' . $materia['id'] . '">' . $materia['nombre'] . '</a></li>';
            }
            echo "</ul></div>";
        }
        echo '</div></div></div></div></div>';
    }
    echo '</div></div></div></div></div></div>';

    // Proyectos Curriculares de Ciclos Formativos (si los hay)

    echo '<div class="row my-4"><div class="col-md-12"><div class="card">';
    echo '<div class="card-header"><h2><a class="card-link-title collapsed" data-toggle="collapse" href="#pccfs">Proyectos Curriculares de Ciclos Formativos </a></h2></div>';
    echo '<div id="pccfs" class="collapse"><div class="card-body">';

    echo '<div class="col-md-12 my-2">';
    // Obtenemos las materias de ese curso
    // Por si se necesita, query para sacar los cursos en los que el departamento imparte materias y no son de ciclos (basta cambiar el id de departamento que está fijo)
    // SELECT DISTINCT cursos.id, cursos.nombre FROM cursos, materias WHERE materias.idCurso = cursos.id AND materias.idDepartamento = 1 AND materias.tiene_programacion = 1 AND cursos.id NOT IN (SELECT DISTINCT cursos_ciclos.idCurso FROM cursos_ciclos) ORDER BY nombre
    // De momento sacamos las materias sin agrupar por curso, porque no se prevé que haya muchas por curso
    $filtroDpto = $_REQUEST['idDepartamento'] == 1 ? "Informática" : "Administración";
    $ciclos = consultarBaseDeDatos("SELECT ciclos.id, ciclos.nombre FROM ciclos WHERE ciclos.familia LIKE '%{$filtroDpto}%' ORDER BY ciclos.nombre");
    echo "<ul>";
    foreach($ciclos as $ciclo)
    {
        echo '<li><a href="https://iessanvicente.com/iconsultas/gestionIES_v3/pdf_pccf.php?idCiclo=' . $ciclo['id'] . '">' . $ciclo['nombre'] . '</a></li>';
    }
    echo "</ul></div>";

    echo '</div></div></div></div></div>';

    // Otras materias fuera de ciclos formativos

    echo '<div class="row my-4"><div class="col-md-12"><div class="card">';
    echo '<div class="card-header"><h2><a class="card-link-title collapsed" data-toggle="collapse" href="#otros">Otros estudios (ESO, Bachillerato) </a></h2></div>';
    echo '<div id="otros" class="collapse"><div class="card-body"><div class="row">';

    // Obtenemos las materias de ese curso
    // Por si se necesita, query para sacar los cursos en los que el departamento imparte materias y no son de ciclos (basta cambiar el id de departamento que está fijo)
    // SELECT DISTINCT cursos.id, cursos.nombre FROM cursos, materias WHERE materias.idCurso = cursos.id AND materias.idDepartamento = 1 AND materias.tiene_programacion = 1 AND cursos.id NOT IN (SELECT DISTINCT cursos_ciclos.idCurso FROM cursos_ciclos) ORDER BY nombre
    // De momento sacamos las materias sin agrupar por curso, porque no se prevé que haya muchas por curso
    $materias = consultarBaseDeDatos("SELECT materias.id, materias.nombre AS nom_materia, cursos.nombre AS nom_curso FROM materias, cursos WHERE materias.idCurso = cursos.id AND materias.tiene_programacion = 1 AND materias.idDepartamento = " . $_REQUEST['idDepartamento'] . " AND materias.idCurso NOT IN (SELECT materias.idCurso FROM materias, cursos_ciclos WHERE materias.idCurso = cursos_ciclos.idCurso) ORDER BY CASE WHEN cursos.nombre LIKE '1º ESO' THEN 1 WHEN cursos.nombre LIKE '2º ESO' THEN 2 WHEN cursos.nombre LIKE '3º ESO' THEN 3 WHEN cursos.nombre LIKE '4º ESO' THEN 4 WHEN cursos.nombre LIKE '1º Bachillerato' THEN 5 WHEN cursos.nombre LIKE '2º Bachillerato' THEN 6 ELSE 99 END, nom_materia");

    echo '<div class="col-md-4 my-2">';
    echo '<h4>Propuestas pedagógicas</h4>';
    echo "<ul>";
    foreach($materias as $materia)
    {
        if (strpos($materia['nom_materia'], ' (grupo') !== false) continue;
        echo '<li><a href="https://iessanvicente.com/iconsultas/gestionIES_v3/pdf_programaciones.php?idMateria=' . $materia['id'] . '">' . $materia['nom_curso'] . ': ' . $materia['nom_materia'] . '</a></li>';
    }
    echo "</ul></div>";

    echo '<div class="col-md-8 my-2">';
    echo '<h4>Programaciones de aula</h4>';
    echo "<ul>";
    foreach($materias as $materia)
    {
        $profesores = consultarBaseDeDatos("SELECT p.* FROM profesores p INNER JOIN seleccion s ON p.id = s.idProfesor WHERE s.idMateria = {$materia['id']} AND s.idEscenario IN (SELECT id FROM escenarios_desideratas WHERE actual = 1) GROUP BY p.id ORDER BY p.orden");
        foreach($profesores as $profesor)
        {
            $grupos = consultarBaseDeDatos("SELECT DISTINCT g.* FROM grupos g INNER JOIN seleccion s ON g.id = s.idGrupo INNER JOIN escenarios_desideratas e ON s.idEscenario = e.id WHERE e.actual = 1 AND s.idProfesor = {$profesor['id']} AND s.idMateria = {$materia['id']} ORDER BY g.nombre;");
            foreach($grupos as $grupo)
            {
                $materia['nom_materia'] = preg_replace('/\s*\(grupos?\s+[^\)]*\)/i', '', $materia['nom_materia']);
                echo "<li><a href='https://iessanvicente.com/iconsultas/gestionIES_v3/pdf_programaciones_aula.php?idMateria={$materia['id']}&idGrupo={$grupo['id']}&idProfesor={$profesor['id']}'>{$materia['nom_curso']}: {$materia['nom_materia']} ({$profesor['nombre']} - {$grupo['nombre']})</a></li>";
            }
        }
    }
    echo "</ul></div>";

    echo '</div></div></div></div></div></div>';
}

?>

</div> <!-- /container -->

<script src="https://iessanvicente.com/js2019/popper.min.js"></script>
<script src="https://iessanvicente.com/js2019/bootstrap.min.js"></script>
<script src="https://iessanvicente.com/js2019/holder.min.js"></script>
<script>
    Holder.addTheme('thumb', {
        bg: '#55595c',
        fg: '#eceeef',
        text: 'Thumbnail'
    });
</script>

</body>
</html>