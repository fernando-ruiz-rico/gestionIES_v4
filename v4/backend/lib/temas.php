<?php
// FASE 2.6 — Utilidades compartidas de los endpoints de temas / unidades
// de programación (api/temas/). Cada acción es un fichero, pero comparten
// estos helpers (copias de v3), así que viven aquí y no se duplican en
// cada endpoint.
//
// Convenio: la conexión $db la abre y cierra cada endpoint; aquí solo se
// consulta.

// id del ciclo formativo al que pertenece la materia (0 si no es de FP).
// Copia de v3 obtenerIdCicloPorMateria().
function temas_id_ciclo_por_materia($db, $idMateria)
{
    $fila = $db->fetchOne("SELECT ciclos.id
                FROM ciclos
                INNER JOIN cursos_ciclos ON ciclos.id = cursos_ciclos.idCiclo
                INNER JOIN cursos ON cursos_ciclos.idCurso = cursos.id
                INNER JOIN materias ON materias.idCurso = cursos.id
                WHERE materias.id = ? LIMIT 1", $idMateria);
    return $fila ? intval($fila['id']) : 0;
}

// Horas anuales de una materia (v3 obtenerHorasAnualesPorMateria)
function temas_horas_anuales($db, $idMateria)
{
    $fila = $db->fetchOne("SELECT horas_anuales FROM materias WHERE id = ?", $idMateria);
    return $fila ? intval($fila['horas_anuales']) : 0;
}

// Checkbox de competencias de una materia (v3 generarCheckboxesCompetenciasMateria)
function temas_competencias_materia($db, $idMateria, $idCiclo)
{
    $competencias = array();
    $yaAgregados = array();

    // Tipo 1: asignadas a la materia
    foreach ($db->fetchAll("SELECT cc.id, cc.codigo, cc.texto, cc.tipo, cc.orden
                FROM competencias_ciclos cc
                INNER JOIN competencias_materias cm ON cc.id = cm.idCompetencia
                WHERE cm.idMateria = ? AND cc.tipo = 1
                ORDER BY cc.orden", $idMateria) as $fila) {
        $competencias[] = $fila;
        $yaAgregados[intval($fila['id'])] = true;
    }

    // Tipo 2: del ciclo (siempre)
    if ($idCiclo > 0) {
        foreach ($db->fetchAll("SELECT id, codigo, texto, tipo, orden
                    FROM competencias_ciclos
                    WHERE idCiclo = ? AND tipo = 2
                    ORDER BY orden", $idCiclo) as $fila) {
            $id = intval($fila['id']);
            if (!isset($yaAgregados[$id])) {
                $competencias[] = $fila;
                $yaAgregados[$id] = true;
            }
        }
    }

    // Ordenar por (tipo, orden)
    usort($competencias, function ($a, $b) {
        if (intval($a['tipo']) === intval($b['tipo'])) {
            return intval($a['orden']) - intval($b['orden']);
        }
        return intval($a['tipo']) - intval($b['tipo']);
    });

    return $competencias;
}
