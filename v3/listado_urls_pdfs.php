<?php
require_once('includes/utilidades.php');

$idDepartamento = (int)($_GET['idDepartamento'] ?? 1);

// ========== FUNCIONES AUXILIARES ==========
function sanitizar($str) {
    $str = preg_replace('/[^\p{L}\p{N}_\-]/u', '_', $str);
    $str = preg_replace('/_{2,}/', '_', $str);
    return trim($str, '_');
}

function limpiarCurso($curso) {
    $curso = preg_replace('/º\s*/', '', $curso);
    $curso = str_replace(' ', '', $curso);
    return $curso;
}

function limpiarGrupo($grupo) {
    // Eliminar turnos
    $grupo = preg_replace('/_{0,2}(mañanas|tardes)_?/i', '', $grupo);
    // Extraer letras/números
    preg_match_all('/[a-zA-Z0-9]+/', $grupo, $matches);
    $partes = $matches[0];
    if (empty($partes)) {
        return 'X';
    }
    return implode('', $partes);
}

function limpiarMateria($materia) {
    // Eliminar sufijos de idioma (cualquier variante)
    $materia = preg_replace('/\s*__?\s*(Valencià|Inglés|módulo optativo|optativo)\s*_?/iu', '', $materia);
    // Eliminar paréntesis vacíos
    $materia = preg_replace('/\s*\(\s*\)\s*/', '', $materia);
    return trim($materia);
}

function formatearNombreProfesor($nombreCompleto, $curso, $grupo, $materia) {
    $partes = array_values(array_filter(explode(' ', trim($nombreCompleto))));
    if (count($partes) >= 3) {
        $apellido2 = array_pop($partes);
        $apellido1 = array_pop($partes);
        $nombreProf = implode(' ', $partes);
    } elseif (count($partes) == 2) {
        $apellido2 = '';
        $apellido1 = array_pop($partes);
        $nombreProf = $partes[0];
    } else {
        return null;
    }

    $prefijo = sanitizar($apellido1);
    if ($apellido2 !== '') {
        $prefijo .= '_' . sanitizar($apellido2);
    }
    $prefijo .= '-' . sanitizar($nombreProf);

    $cursoLimpio = limpiarCurso($curso);
    $grupoLimpio = limpiarGrupo($grupo);
    $materiaLimpia = limpiarMateria($materia);

    if ($materiaLimpia === '') {
        return null;
    }

    return $prefijo . '-' . $cursoLimpio . $grupoLimpio . '-' . sanitizar($materiaLimpia) . '.pdf';
}

// ========== 1. CICLOS FORMATIVOS ==========
$ciclos = consultarBaseDeDatos("
    SELECT * FROM ciclos 
    WHERE id IN (
        SELECT DISTINCT cursos_ciclos.idCiclo 
        FROM cursos_ciclos
        JOIN cursos ON cursos_ciclos.idCurso = cursos.id
        JOIN materias ON cursos.id = materias.idCurso
        WHERE materias.idDepartamento = $idDepartamento
    ) 
    ORDER BY nivel, nombre
");

foreach ($ciclos as $ciclo) {
    $nombreCicloDir = sanitizar($ciclo['nombre']);
    $cursos = consultarBaseDeDatos("
        SELECT DISTINCT cursos.id, cursos.nombre, cursos_ciclos.orden 
        FROM cursos
        JOIN cursos_ciclos ON cursos.id = cursos_ciclos.idCurso
        JOIN materias ON cursos.id = materias.idCurso
        WHERE materias.idDepartamento = $idDepartamento
        AND cursos_ciclos.idCiclo = {$ciclo['id']}
        ORDER BY cursos_ciclos.orden
    ");

    foreach ($cursos as $curso) {
        $materias = consultarBaseDeDatos("
            SELECT id, nombre 
            FROM materias 
            WHERE idDepartamento = $idDepartamento 
            AND tiene_programacion = 1 
            AND idCurso = {$curso['id']}
        ");

        foreach ($materias as $materia) {
            // === SOLO PROGRAMACIONES DE AULA (con profesor) ===
            $profesores = consultarBaseDeDatos("
                SELECT p.id, p.nombre 
                FROM profesores p
                INNER JOIN seleccion s ON p.id = s.idProfesor
                WHERE s.idMateria = {$materia['id']}
                AND s.idEscenario IN (SELECT id FROM escenarios_desideratas WHERE actual = 1)
                GROUP BY p.id
                ORDER BY p.orden
            ");

            foreach ($profesores as $profesor) {
                $grupos = consultarBaseDeDatos("
                    SELECT DISTINCT g.id, g.nombre 
                    FROM grupos g
                    INNER JOIN seleccion s ON g.id = s.idGrupo
                    INNER JOIN escenarios_desideratas e ON s.idEscenario = e.id
                    WHERE e.actual = 1
                    AND s.idProfesor = {$profesor['id']}
                    AND s.idMateria = {$materia['id']}
                    ORDER BY g.nombre
                ");

                foreach ($grupos as $grupo) {
                    $nombreArchivo = formatearNombreProfesor(
                        $profesor['nombre'],
                        $curso['nombre'],
                        $grupo['nombre'],
                        $materia['nombre']
                    );

                    if ($nombreArchivo !== null) {
                        echo "https://iessanvicente.com/iconsultas/gestionIES_v3/pdf_programaciones_aula.php?idMateria={$materia['id']}&idGrupo={$grupo['id']}&idProfesor={$profesor['id']}\tCiclos_formativos/$nombreCicloDir/" . limpiarCurso($curso['nombre']) . "/$nombreArchivo\n";
                    }
                }
            }
        }
    }
}

// ========== 2. PCCF ==========
$filtroDpto = $idDepartamento == 1 ? "Informática" : "Administración";
$ciclosPCCF = consultarBaseDeDatos("
    SELECT id, nombre 
    FROM ciclos 
    WHERE familia LIKE '%" . $filtroDpto . "%' 
    ORDER BY nombre
");

foreach ($ciclosPCCF as $ciclo) {
    $nombrePdf = sanitizar($ciclo['nombre']) . '.pdf';
    echo "https://iessanvicente.com/iconsultas/gestionIES_v3/pdf_pccf.php?idCiclo={$ciclo['id']}\tPCCF/$nombrePdf\n";
}

// ========== 3. OTROS ESTUDIOS (ESO, BACHILLERATO) ==========
$materiasOtros = consultarBaseDeDatos("
    SELECT m.id, m.nombre AS nom_materia, c.nombre AS nom_curso
    FROM materias m
    JOIN cursos c ON m.idCurso = c.id
    WHERE m.tiene_programacion = 1
    AND m.idDepartamento = $idDepartamento
    AND m.idCurso NOT IN (SELECT idCurso FROM cursos_ciclos)
    ORDER BY 
        CASE 
            WHEN c.nombre LIKE '1º ESO' THEN 1
            WHEN c.nombre LIKE '2º ESO' THEN 2
            WHEN c.nombre LIKE '3º ESO' THEN 3
            WHEN c.nombre LIKE '4º ESO' THEN 4
            WHEN c.nombre LIKE '1º Bachillerato' THEN 5
            WHEN c.nombre LIKE '2º Bachillerato' THEN 6
            ELSE 99 
        END,
        m.nombre
");

foreach ($materiasOtros as $materia) {
    if (strpos($materia['nom_materia'], ' (grupo') !== false) continue;

    // === SOLO PROGRAMACIONES DE AULA (con profesor) ===
    $profesores = consultarBaseDeDatos("
        SELECT p.id, p.nombre 
        FROM profesores p
        INNER JOIN seleccion s ON p.id = s.idProfesor
        WHERE s.idMateria = {$materia['id']}
        AND s.idEscenario IN (SELECT id FROM escenarios_desideratas WHERE actual = 1)
        GROUP BY p.id
        ORDER BY p.orden
    ");

    foreach ($profesores as $profesor) {
        $grupos = consultarBaseDeDatos("
            SELECT g.id, g.nombre 
            FROM grupos g
            INNER JOIN seleccion s ON g.id = s.idGrupo
            INNER JOIN escenarios_desideratas e ON s.idEscenario = e.id
            WHERE e.actual = 1
            AND s.idProfesor = {$profesor['id']}
            AND s.idMateria = {$materia['id']}
            ORDER BY g.nombre
        ");

        foreach ($grupos as $grupo) {
            $nombreArchivo = formatearNombreProfesor(
                $profesor['nombre'],
                $materia['nom_curso'],
                $grupo['nombre'],
                $materia['nom_materia']
            );

            if ($nombreArchivo !== null) {
                echo "https://iessanvicente.com/iconsultas/gestionIES_v3/pdf_programaciones_aula.php?idMateria={$materia['id']}&idGrupo={$grupo['id']}&idProfesor={$profesor['id']}\tOtros_estudios_ESO_Bachillerato/Programaciones_de_aula/$nombreArchivo\n";
            }
        }
    }
}