<?php
// API de selección (Desideratas): cursos, grupos y materias disponibles para
// el escenario, con el total de peticiones que hay de cada una (v3/listar_cursos.php)
require_once '../../config.php';
cabeceraJson();

$usuario = checkSession();

// "super" = jefe de departamento o admin
$super = in_array($usuario['rol'], array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$idDepartamento = getOptimoInt('idDepartamento');
$idEscenario = getOptimoInt('idEscenario');
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
