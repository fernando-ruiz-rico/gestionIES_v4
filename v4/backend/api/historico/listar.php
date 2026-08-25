<?php
// Listar el histórico de un departamento y escenario: para cada profesor,
// qué eligió en el escenario indicado, marcando en rojo las materias con
// conflicto (fiel a v3/cargar_historico.php)
require_once '../../config.php';
cabeceraJson();

// Fiel a v3: el módulo de Desideratas exige sesión iniciada
checkSession();

try {
    $idDepartamento = getOptimoInt('idDepartamento');
    $idEscenario = getOptimoInt('idEscenario');
    if ($idDepartamento <= 0 || $idEscenario <= 0) {
        throw new Exception('Faltan parámetros');
    }

    $db = Db::open();

    // ---- Precomputamos las materias con sobredemanda o conflicto ----
    // (v3 recorre cursos y grupos; solo le importan las materias del
    //  departamento con cantidad > 0, que son las que se consultan aquí)
    $materias = $db->fetchAll("SELECT m.id, m.divisible, mg.idGrupo, mg.cantidad, mg.horas
                                FROM materias m
                                JOIN materias_grupos mg ON mg.idMateria = m.id
                                WHERE m.idDepartamento = ? AND mg.cantidad > 0", $idDepartamento);
    $materiasConflictos = array();
    foreach ($materias as $materia) {
        // Cuántos profesores la eligieron, y cuántas horas en total
        $peticion = $db->fetchOne("SELECT COUNT(*) AS peticiones, COALESCE(SUM(horas), 0) AS sumHoras
                                    FROM seleccion
                                    WHERE idMateria = ? AND idGrupo = ? AND idEscenario = ?",
                                   $materia['id'], $materia['idGrupo'], $idEscenario);
        if ($peticion['peticiones'] > 0) {
            // v3: si no es divisible y hay más peticiones que la cantidad, conflicto;
            // si no, conflicto cuando la suma de horas supera las de la materia
            if (!$materia['divisible'] && $peticion['peticiones'] > $materia['cantidad']) {
                $materiasConflictos[] = array('idMateria' => $materia['id'], 'idGrupo' => $materia['idGrupo']);
            } else if ($peticion['sumHoras'] > $materia['horas'] * $materia['cantidad']) {
                $materiasConflictos[] = array('idMateria' => $materia['id'], 'idGrupo' => $materia['idGrupo']);
            }
        }
    }

    // ---- Profesores activos del departamento, y los que eligieron en el escenario ----
    $profesores = $db->fetchAll("SELECT id, nombre
                                  FROM profesores
                                  WHERE (idDepartamento = ? AND activo = 1)
                                     OR id IN (SELECT idProfesor FROM seleccion WHERE idEscenario = ?)
                                  ORDER BY orden", $idDepartamento, $idEscenario);

    $historico = array();
    foreach ($profesores as $profesor) {
        $filas = array();
        $total = 0;
        $contadorTutorias = 0;
        $selecciones = $db->fetchAll("SELECT s.id AS idSeleccion, m.nombre, m.id AS idMateria,
                                            m.tipo, s.horas, c.abreviatura AS abrevCurso,
                                            g.abreviatura AS abrevGrupo, g.mostrar, g.id AS idGrupo
                                      FROM seleccion s
                                      JOIN materias m ON m.id = s.idMateria
                                      JOIN cursos c ON c.id = m.idCurso
                                      JOIN grupos g ON g.id = s.idGrupo
                                      WHERE s.idProfesor = ? AND s.idEscenario = ?
                                      ORDER BY s.orden", $profesor['id'], $idEscenario);
        foreach ($selecciones as $seleccion) {
            $total += $seleccion['horas'];
            $conflicto = false;
            // v3: la segunda tutoría en adelante es conflicto
            if ($seleccion['tipo'] == 'TUTORIA') {
                $contadorTutorias++;
                if ($contadorTutorias > 1) {
                    $conflicto = true;
                }
            }
            foreach ($materiasConflictos as $mc) {
                if ($mc['idMateria'] == $seleccion['idMateria'] && $mc['idGrupo'] == $seleccion['idGrupo']) {
                    $conflicto = true;
                }
            }
            $filas[] = array(
                'idSeleccion' => $seleccion['idSeleccion'],
                'nombre' => $seleccion['nombre'],
                'tipo' => $seleccion['tipo'],
                'abrevCurso' => $seleccion['abrevCurso'],
                'abrevGrupo' => $seleccion['abrevGrupo'],
                'mostrar' => $seleccion['mostrar'],
                'horas' => $seleccion['horas'],
                'conflicto' => $conflicto
            );
        }
        $historico[] = array(
            'id' => $profesor['id'],
            'nombre' => $profesor['nombre'],
            'total' => $total,
            'filas' => $filas
        );
    }

    $db->close();
    sendJSONSuccess($historico);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendJSONError($e->getMessage());
}
