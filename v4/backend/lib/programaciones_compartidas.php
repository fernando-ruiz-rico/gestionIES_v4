<?php
// Utilidades compartidas de los endpoints de "programaciones de aula"
// (api/programaciones_aula/) y de "seguimiento de programaciones"
// (api/programaciones_seguimiento/).
//
// Los módulos usan exactamente la misma lógica para listar grupos, materias
// y profesores (el profesor siempre ve el suyo; un superusuario puede elegir
// cualquier profesor), así que esas consultas viven aquí una sola vez y cada
// endpoint las reutiliza, en vez de duplicar el código. Aquí vive también la
// lógica de apartados/categoría de la programación, compartida entre la
// opción de propuesta pedagógica (api/programaciones/) y la de programaciones
// de aula (api/programaciones_aula/).
//
// Convenio: cada función hace la sesión/permisos que le corresponden, la
// consulta y la respuesta; el endpoint ya ha llamado a cabeceraJson() antes.
// Las funciones que reciben una conexión ($db) no la abren ni la cierran:
// del endpoint depende la conexión.

// Listar los grupos de un profesor para una materia (solo los del curso
// actual: la selección apunta a un escenario "desideratas" actual).
function pcCmp_listarGrupos()
{
    $session = checkSession();
    $idMateria = getOptimoInt('idMateria');
    $rol = $session['rol'];
    $idProfesorSesion = intval($session['idUsuario']);

    if ($idMateria <= 0) {
        sendJSONError('Debe indicar una materia', 400);
    }

    // Admin/jefe pueden ver grupos de cualquier profesor; el profesor, los suyos
    if (esUsuarioSuper($rol)) {
        $idProfesor = getOptimoInt('idProfesor', $idProfesorSesion);
    } else {
        $idProfesor = $idProfesorSesion;
    }

    try {
        $db = Db::open();

        $filas = $db->fetchAll("SELECT g.id AS id, g.nombre AS nombre
                                FROM grupos g
                                WHERE g.id IN (
                                    SELECT s.idGrupo FROM seleccion s
                                    JOIN escenarios_desideratas e ON e.id = s.idEscenario
                                    WHERE s.idMateria = ? AND s.idProfesor = ? AND e.actual = 1
                                )
                                ORDER BY g.nombre", $idMateria, $idProfesor);

        $grupos = array();
        foreach ($filas as $fila) {
            $grupos[] = array(
                'id'     => intval($fila['id']),
                'nombre' => $fila['nombre']
            );
        }

        $db->close();
        sendJSONSuccess($grupos);
    } catch (DbException $e) {
        sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
    }
}

// Listar las materias con programación activa de un profesor, solo las del
// curso actual (fiel a v3: e.actual = 1).
function pcCmp_listarMaterias()
{
    $session = checkSession();

    // Admin puede elegir profesor; un profesor usa siempre el suyo
    $rol = $session['rol'];
    $idProfesorSesion = intval($session['idUsuario']);

    if (esUsuarioSuper($rol)) {
        $idProfesor = getOptimoInt('idProfesor', $idProfesorSesion);
    } else {
        $idProfesor = $idProfesorSesion;
    }

    try {
        $db = Db::open();

        $filas = $db->fetchAll("SELECT DISTINCT m.id AS id, m.nombre AS nombreMateria, c.nombre AS nomCurso, m.horas AS horas
                                FROM materias m
                                JOIN cursos c ON c.id = m.idCurso
                                JOIN seleccion s ON s.idMateria = m.id
                                JOIN escenarios_desideratas e ON e.id = s.idEscenario
                                WHERE m.tiene_programacion = 1
                                  AND s.idProfesor = ?
                                  AND e.actual = 1
                                ORDER BY m.nombre", $idProfesor);

        $materias = array();
        foreach ($filas as $fila) {
            $materias[] = array(
                'id'       => intval($fila['id']),
                'nombre'   => $fila['nombreMateria'],
                'nomCurso' => $fila['nomCurso'],
                'horas'    => intval($fila['horas'])
            );
        }

        $db->close();
        sendJSONSuccess($materias);
    } catch (DbException $e) {
        sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
    }
}

// ¿La materia pertenece a un ciclo? → 'FP'; si no, 'ESO/BACH' (criterio v3).
function pcCmp_categoriaMateria($db, $idMateria)
{
    $idMateria = (int)$idMateria;
    $fila = $db->fetchOne(
        "SELECT c.id FROM ciclos c
            JOIN cursos_ciclos cc ON cc.idCiclo = c.id
            JOIN cursos cu ON cu.id = cc.idCurso
            JOIN materias m ON m.idCurso = cu.id
           WHERE m.id = ?
           LIMIT 1", $idMateria);
    return $fila ? 'FP' : 'ESO/BACH';
}

// Listado de apartados de una materia (con numeración "1." / "1.1."), criterio v3.
function pcCmp_cargarApartados($db, $idMateria)
{
    $idMateria = (int)$idMateria;
    $categoria = pcCmp_categoriaMateria($db, $idMateria);
    $filas = $db->fetchAll(
        "SELECT id, titulo, tipo, subapartado FROM apartados_programaciones
         WHERE categoria = 'TODOS' OR categoria = ?
         ORDER BY orden", $categoria);
    $apartados = array();
    $principal = 0;
    $secundario = 0;
    foreach ($filas as $fila) {
        if (!(bool)$fila['subapartado']) {
            $principal++;
            $secundario = 0;
        } else {
            $secundario++;
        }
        $apartados[] = [
            'id'        => (int)$fila['id'],
            'tipo'      => (int)$fila['tipo'],
            'nombre'    => ($fila['subapartado']
                              ? "$principal.$secundario. "
                              : "$principal. ") . $fila['titulo']
        ];
    }
    return $apartados;
}

// Listar los grupos que imparte un profesor en el escenario actual (para el
// desplegable «grupo» de la opción de programaciones de aula). El profesor
// solo ve el suyo; un superusuario puede elegir cualquier profesor.
function pcCmp_listarGruposProfesor()
{
    $session = checkSession();
    $rol = $session['rol'];
    $idProfesorSesion = intval($session['idUsuario']);
    if (esUsuarioSuper($rol)) {
        $idProfesor = getOptimoInt('idProfesor', $idProfesorSesion);
    } else {
        $idProfesor = $idProfesorSesion;
    }

    try {
        $db = Db::open();
        $filas = $db->fetchAll("SELECT DISTINCT g.id AS id, g.nombre AS nombre
                                  FROM grupos g
                                  JOIN seleccion s ON s.idGrupo = g.id
                                  JOIN escenarios_desideratas e ON e.id = s.idEscenario
                                  WHERE s.idProfesor = ?
                                    AND e.actual = 1
                                  ORDER BY g.nombre", $idProfesor);

        $grupos = array();
        foreach ($filas as $fila) {
            $grupos[] = array('id' => intval($fila['id']), 'nombre' => $fila['nombre']);
        }

        $db->close();
        sendJSONSuccess($grupos);
    } catch (DbException $e) {
        sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
    }
}

// Listar las materias del profesor en un grupo (con programación, escenario
// actual), para el desplegable «materia» de la opción de programaciones de
// aula. Cada materia va con su flag "terminada" (si la propuesta pedagógica
// está terminada), porque es lo que habilita importar la programación de aula.
function pcCmp_listarMateriasGrupo()
{
    $session = checkSession();
    $rol = $session['rol'];
    $idProfesorSesion = intval($session['idUsuario']);
    if (esUsuarioSuper($rol)) {
        $idProfesor = getOptimoInt('idProfesor', $idProfesorSesion);
    } else {
        $idProfesor = $idProfesorSesion;
    }
    $idGrupo = getOptimoInt('idGrupo');
    if ($idGrupo <= 0) {
        sendJSONError('Debe indicar un grupo', 400);
    }

    try {
        $db = Db::open();
        $filas = $db->fetchAll("SELECT DISTINCT m.id AS id, m.nombre AS nombreMateria,
                                           m.terminada_programacion AS terminada
                                  FROM materias m
                                  JOIN seleccion s ON s.idMateria = m.id
                                  JOIN escenarios_desideratas e ON e.id = s.idEscenario
                                  WHERE m.tiene_programacion = 1
                                    AND s.idProfesor = ?
                                    AND s.idGrupo = ?
                                    AND e.actual = 1
                                  ORDER BY m.nombre", $idProfesor, $idGrupo);

        $materias = array();
        foreach ($filas as $fila) {
            $materias[] = array(
                'id'        => intval($fila['id']),
                'nombre'    => $fila['nombreMateria'],
                'terminada' => (bool)$fila['terminada']
            );
        }

        $db->close();
        sendJSONSuccess($materias);
    } catch (DbException $e) {
        sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
    }
}

// Listar todos los profesores (id y nombre), para el desplegable de selección.
// Solo un administrador o jefe de departamento puede llegar aquí.
function pcCmp_listarProfesores()
{
    checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

    try {
        $db = Db::open();

        // Solo id y nombre (los campos que usa el desplegable); evita devolver
        // clave, e-mail, teléfono...
        $filas = $db->fetchAll("SELECT id, nombre FROM profesores ORDER BY nombre");

        $profesores = array();
        foreach ($filas as $fila) {
            $profesores[] = array('id' => intval($fila['id']), 'nombre' => $fila['nombre']);
        }

        $db->close();
        sendJSONSuccess($profesores);
    } catch (DbException $e) {
        sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
    }
}
