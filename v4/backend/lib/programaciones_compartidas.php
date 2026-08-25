<?php
// Utilidades compartidas de los endpoints de "programaciones de aula"
// (api/programaciones_aula/) y de "seguimiento de programaciones"
// (api/programaciones_seguimiento/).
//
// Los dos módulos usan exactamente la misma lógica para listar grupos,
// materias y profesores (el profesor siempre ve el suyo; un superusuario
// puede elegir cualquier profesor), así que esas consultas viven aquí una
// sola vez y cada endpoint las reutiliza, en vez de duplicar el código.
//
// Convenio: cada función hace la sesión/permisos que le corresponden, la
// consulta y la respuesta; el endpoint ya ha llamado a cabeceraJson() antes.

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
