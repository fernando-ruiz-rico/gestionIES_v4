<?php
// API de selección para el módulo de Desideratas.
// Fiel a v3 (las páginas ajax/seleccion/*.php reordenadas como "acciones"):
//  - listar_escenarios: escenarios elegibles para el desplegable (v3/cargar_escenarios.php y
//                        v3/cargar_escenarios_profesor.php, según el rol)
//  - listar_especialidades: especialidades del departamento (v3 las fijaba a 1..4 en el JS)
//  - listar_profesores: profesores del departamento con sus horas (v3/listar_profesores.php)
//  - listar_cursos: cursos, grupos y materias disponibles (v3/listar_cursos.php)
//  - listar_seleccion: materias elegidas por el profesor (v3/listar_seleccion.php)
//  - listar_profesores_materia: profesores que eligieron una materia (v3/cargar_listado_profesores_materia.php)
//  - insertar_seleccion: elegir una materia (v3/insertar_seleccion.php)
//  - borrar_seleccion: quitar una selección (v3/borrar_seleccion.php)
//  - borrar_toda_seleccion: vaciar la selección del profesor (v3/borrar_toda_seleccion.php)
//  - borrar_todas_selecciones: vaciar el escenario (v3/borrar_todas_selecciones.php)
//  - ordenar_seleccion: reordenar prioridades (v3/ordenar_seleccion.php)
require_once '../config.php';
cabeceraJson();

// Fiel a v3: el módulo de Desideratas exige sesión iniciada
$usuario = checkSession();

// "super" = jefe de departamento o admin (v3 lo usa en casi todas las páginas)
$super = in_array($usuario['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = array();
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = cuerpoJson();
}

$action = datosOptimo($datos, 'action', datosOptimo($_REQUEST, 'action'));
$idDepartamento = getOptimoInt('idDepartamento');
$idEscenario = getOptimoInt('idEscenario');

switch ($action) {

    // Escenarios elegibles para el desplegable del módulo.
    // Si es super ve los del departamento; si no, solo los activos en este momento
    case 'listar_escenarios': {
        if ($idDepartamento <= 0) {
            sendJSONError('Departamento inválido', 400);
        }
        try {
            $db = Db::open();
            if ($super) {
                $filas = $db->fetchAll("SELECT id, nombre
                                        FROM escenarios_desideratas
                                        WHERE id IN (SELECT idEscenario
                                                     FROM departamentos_escenarios
                                                     WHERE idDepartamento = ?)
                                        ORDER BY nombre", $idDepartamento);
            } else {
                // v3/cargar_escenarios_profesor.php: solo los elegibles (activo_desideratas = 1)
                $filas = $db->fetchAll("SELECT id, nombre
                                        FROM escenarios_desideratas
                                        WHERE id IN (SELECT idEscenario
                                                     FROM departamentos_escenarios
                                                     WHERE idDepartamento = ?)
                                        AND activo_desideratas = 1
                                        ORDER BY nombre", $idDepartamento);
            }
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess($filas);
        break;
    }

    // Especialidades del departamento (para el filtro del panel de profesores)
    case 'listar_especialidades': {
        if ($idDepartamento <= 0) {
            sendJSONError('Departamento inválido', 400);
        }
        try {
            $db = Db::open();
            $filas = $db->fetchAll("SELECT id, descripcion
                                     FROM especialidades
                                     WHERE idDepartamento = ?
                                     ORDER BY id", $idDepartamento);
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess($filas);
        break;
    }

    // Panel de profesores del departamento, con el total de horas que ya eligieron
    case 'listar_profesores': {
        if ($idDepartamento <= 0 || $idEscenario <= 0) {
            sendJSONError('Faltan parámetros', 400);
        }
        $idEspecialidad = datosOptimo($_REQUEST, 'idEspecialidad', 'Todos');
        try {
            $db = Db::open();
            $extra = ($idEspecialidad == 'Todos') ? '' : ' AND p.idEspecialidad = ?';
            if ($idEspecialidad == 'Todos') {
                $filas = $db->fetchAll("SELECT p.id, p.nombre, p.idEspecialidad,
                                            (SELECT COALESCE(SUM(horas), 0)
                                             FROM seleccion s
                                             WHERE s.idEscenario = ? AND s.idProfesor = p.id) AS horas
                                     FROM profesores p
                                     WHERE p.idDepartamento = ? AND p.activo = 1
                                     ORDER BY p.orden", $idEscenario, $idDepartamento);
            } else {
                $filas = $db->fetchAll("SELECT p.id, p.nombre, p.idEspecialidad,
                                            (SELECT COALESCE(SUM(horas), 0)
                                             FROM seleccion s
                                             WHERE s.idEscenario = ? AND s.idProfesor = p.id) AS horas
                                     FROM profesores p
                                     WHERE p.idDepartamento = ? AND p.activo = 1" . $extra . "
                                     ORDER BY p.orden", $idEscenario, $idDepartamento, $idEspecialidad);
            }
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess($filas);
        break;
    }

    // Cursos, grupos y materias disponibles para el escenario, con el total de
    // peticiones que hay de cada una (v3/listar_cursos.php)
    case 'listar_cursos': {
        if ($idDepartamento <= 0 || $idEscenario <= 0) {
            sendJSONError('Faltan parámetros', 400);
        }
        try {
            $db = Db::open();
            // v3: el jefe/admin ve todas las materias; el profesor, las que
            // no le haya asignado la directiva (cargos, etc.)
            $extra = $super ? '' : ' AND asignada_directiva = 0';
            $fila = $db->fetchOne("SELECT modo_rueda FROM escenarios_desideratas WHERE id = ?", $idEscenario);
            $modoRueda = $fila ? $fila['modo_rueda'] : 0;
            $cursos = $db->fetchAll("SELECT c.id, c.nombre
                                      FROM cursos c
                                      WHERE c.id IN (SELECT idCurso
                                                    FROM materias
                                                    WHERE idDepartamento = ?" . $extra . ")
                                      ORDER BY c.orden, c.nombre", $idDepartamento);
            $filas = array();
            foreach ($cursos as $curso) {
                $grupos = $db->fetchAll("SELECT id, nombre, mostrar
                                         FROM grupos
                                         WHERE idCurso = ?
                                         ORDER BY orden, nombre", $curso['id']);
                foreach ($grupos as $grupo) {
                    $materias = $db->fetchAll("SELECT m.id, m.nombre, m.divisible, m.idEspecialidad,
                                                    mg.horas, mg.cantidad, mg.min_num_profesores, mg.max_grupos_profesor,
                                                    (SELECT COUNT(*)
                                                     FROM seleccion s
                                                     WHERE s.idMateria = m.id AND s.idGrupo = ? AND s.idEscenario = ?) AS elegidas
                                             FROM materias m
                                             JOIN materias_grupos mg ON mg.idMateria = m.id AND mg.idGrupo = ?
                                             WHERE m.idDepartamento = ?" . $extra . " AND mg.cantidad > 0
                                             ORDER BY m.nombre", $grupo['id'], $idEscenario, $grupo['id'], $idDepartamento);
                    foreach ($materias as $materia) {
                        $materia['idCurso'] = $curso['id'];
                        $materia['nombreCurso'] = $curso['nombre'];
                        $materia['idGrupo'] = $grupo['id'];
                        $materia['nombreGrupo'] = $grupo['mostrar'] ? $grupo['nombre'] : '';
                        $filas[] = $materia;
                    }
                }
            }
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess(array('modoRueda' => $modoRueda, 'filas' => $filas));
        break;
    }

    // Materias que ya eligió el profesor, con su orden actual
    case 'listar_seleccion': {
        $idProfesor = getOptimoInt('idProfesor');
        if ($idProfesor <= 0 || $idEscenario <= 0) {
            sendJSONError('Faltan parámetros', 400);
        }
        try {
            $db = Db::open();
            $filas = $db->fetchAll("SELECT s.id, s.horas, s.orden, m.nombre, m.asignada_directiva,
                                        c.abreviatura AS abrevCurso, g.abreviatura AS abrevGrupo, g.mostrar
                                     FROM seleccion s
                                     JOIN materias m ON m.id = s.idMateria
                                     JOIN cursos c ON c.id = m.idCurso
                                     JOIN grupos g ON g.id = s.idGrupo
                                     WHERE s.idProfesor = ? AND s.idEscenario = ?
                                     ORDER BY s.orden", $idProfesor, $idEscenario);
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess($filas);
        break;
    }

    // Nombres de los profesores que ya eligieron una materia (badge "X/Y" al pulsarlo)
    case 'listar_profesores_materia': {
        $idMateria = getOptimoInt('idMateria');
        $idGrupo = getOptimoInt('idGrupo');
        if ($idMateria <= 0 || $idGrupo <= 0 || $idEscenario <= 0) {
            sendJSONError('Faltan parámetros', 400);
        }
        try {
            $db = Db::open();
            $filas = $db->fetchAll("SELECT p.nombre
                                    FROM seleccion s
                                    JOIN profesores p ON p.id = s.idProfesor
                                    WHERE s.idMateria = ? AND s.idGrupo = ? AND s.idEscenario = ?
                                    ORDER BY s.orden, p.orden", $idMateria, $idGrupo, $idEscenario);
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        $nombres = array();
        foreach ($filas as $fila) {
            $nombres[] = $fila['nombre'];
        }
        sendJSONSuccess($nombres);
        break;
    }

    // Elegir una materia para el profesor actual (v3/insertar_seleccion.php)
    case 'insertar_seleccion': {
        if ($datos === null) {
            sendJSONError('Faltan datos', 400);
        }
        $idProfesor = datosOptimoInt($datos, 'idProfesor');
        $idMateria = datosOptimoInt($datos, 'idMateria');
        $idGrupo = datosOptimoInt($datos, 'idGrupo');
        $horas = datosOptimoInt($datos, 'horas');
        if ($idProfesor <= 0 || $idMateria <= 0 || $idGrupo <= 0 || $idEscenario <= 0 || $horas <= 0) {
            sendJSONError('Faltan parámetros', 400);
        }
        try {
            $db = Db::open();
            // v3: si la materia la asigna la directiva se le da un orden inferior (100),
            // de modo que queda por detrás de las que elige el profesor
            $asignada = $db->fetchOne("SELECT asignada_directiva FROM materias WHERE id = ?", $idMateria);
            if ($asignada) {
                $total = $db->fetchOne("SELECT COUNT(*) AS total FROM seleccion
                                          WHERE idProfesor = ? AND idEscenario = ?", $idProfesor, $idEscenario);
                $orden = $asignada['asignada_directiva'] ? 100 : $total['total'] + 1;
            } else {
                $orden = $db->fetchOne("SELECT COALESCE(MAX(orden), 0) + 1 AS nuevo
                                          FROM seleccion
                                          WHERE idProfesor = ? AND idEscenario = ?", $idProfesor, $idEscenario)['nuevo'];
            }
            $db->execute("INSERT INTO seleccion (idProfesor, idMateria, idGrupo, horas, orden, idEscenario)
                          VALUES (?, ?, ?, ?, ?, ?)", $idProfesor, $idMateria, $idGrupo, $horas, $orden, $idEscenario);
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess(null, 'Seleccionada');
        break;
    }

    // Quitar una selección concreta (v3/borrar_seleccion.php)
    case 'borrar_seleccion': {
        if ($datos === null) {
            sendJSONError('Faltan datos', 400);
        }
        $id = datosOptimoInt($datos, 'id');
        if ($id <= 0) {
            sendJSONError('ID inválido', 400);
        }
        try {
            $db = Db::open();
            $afectadas = $db->execute("DELETE FROM seleccion WHERE id = ?", $id);
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        if ($afectadas === 0) {
            sendJSONError('No encontrado', 404);
        }
        sendJSONSuccess(null, 'Eliminada');
        break;
    }

    // Vaciar la selección del profesor. v3: si no hay permisos, solo se quitan
    // las materias que no haya asignado la directiva (v3/borrar_toda_seleccion.php)
    case 'borrar_toda_seleccion': {
        if ($datos === null) {
            sendJSONError('Faltan datos', 400);
        }
        $idProfesor = datosOptimoInt($datos, 'idProfesor');
        $idEscenario = datosOptimoInt($datos, 'idEscenario', $idEscenario);
        if ($idProfesor <= 0 || $idEscenario <= 0) {
            sendJSONError('Faltan parámetros', 400);
        }
        try {
            $db = Db::open();
            if ($super) {
                $db->execute("DELETE FROM seleccion WHERE idProfesor = ? AND idEscenario = ?", $idProfesor, $idEscenario);
            } else {
                $db->execute("DELETE FROM seleccion
                              WHERE idProfesor = ? AND idEscenario = ?
                                AND idMateria NOT IN (SELECT id FROM materias WHERE asignada_directiva = 1)", $idProfesor, $idEscenario);
            }
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess(null, 'Selección vaciada');
        break;
    }

    // Vaciar todas las selecciones del escenario. Solo jefe de departamento o admin
    case 'borrar_todas_selecciones': {
        if (!$super) {
            sendJSONError('Permisos insuficientes', 403);
        }
        if ($idEscenario <= 0) {
            sendJSONError('Escenario inválido', 400);
        }
        try {
            $db = Db::open();
            $db->execute("DELETE FROM seleccion WHERE idEscenario = ?", $idEscenario);
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess(null, 'Escenario vaciado');
        break;
    }

    // Reordenar las prioridades de selección del profesor. v3: si el escenario
    // está en modo rueda y no hay permisos, la operación no se hace
    case 'ordenar_seleccion': {
        if ($datos === null) {
            sendJSONError('Faltan datos', 400);
        }
        $ids = isset($datos['ids']) && is_array($datos['ids']) ? $datos['ids'] : array();
        if (count($ids) === 0 || $idEscenario <= 0) {
            sendJSONError('Faltan parámetros', 400);
        }
        try {
            $db = Db::open();
            $fila = $db->fetchOne("SELECT modo_rueda FROM escenarios_desideratas WHERE id = ?", $idEscenario);
            if ($fila && $fila['modo_rueda'] && !$super) {
                sendJSONError('El escenario está en modo rueda; no se pueden reordenar las selecciones', 400);
            }
            // "ids" lleva los ids de la selección en el orden nuevo; a cada uno se le asigna su posición
            foreach ($ids as $posicion => $id) {
                $db->execute("UPDATE seleccion SET orden = ? WHERE id = ?", $posicion + 1, intval($id));
            }
            $db->close();
        } catch (DbException $e) {
            sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
        }
        sendJSONSuccess(null, 'Reordenado');
        break;
    }
};
?>
