<?php
    // -------------------------------
    // Imprime un mensaje si hay resultados de aprendizaje clave
    // -------------------------------
    function imprimirMensajeRAClave($resultadosAprendizaje, $idCiclo) {
        if ($idCiclo <= 0) return '';

        if (array_filter($resultadosAprendizaje, function($r) { return !empty($r['es_clave']); })) {
            $textoRACE = $idCiclo > 0 ? 'Resultado de aprendizaje' : 'Competencia específica';
            return '<p style="margin-top:10px;"><em>* ' . $textoRACE . ' clave: se debe superar para aprobar la materia.</em></p>';
        }
        return '';
    }

    // -------------------------------
    // Obtiene los temas de una materia específica
    // -------------------------------
    function obtenerTemasDeMateria($idMateria)
    {
        $sql = "SELECT * FROM temas WHERE idMateria = " . (int)$idMateria . " ORDER BY orden";
        return consultarBaseDeDatos($sql);
    }

    // -------------------------------
    // Obtiene los datos de un tema específico de una materia específica
    // -------------------------------
    function obtenerDatosTema($idTema, $idMateria)
    {
        $sql = "SELECT * FROM temas WHERE id = " . (int)$idTema . " AND idMateria = " . (int)$idMateria;
        $tema = consultarBaseDeDatos($sql);
        return !empty($tema) && is_array($tema) && count($tema) == 1 ? $tema[0] : [];
    }    

    // -------------------------------
    // Obtiene los contenidos por defecto de los temas para un departamento específico
    // -------------------------------
    function obtenerContenidosDefectoTema($idDepartamento)
    {
        $sql = "SELECT * FROM contenidos_defecto_temas WHERE idDepartamento = " . (int)$idDepartamento;
        $resultados = consultarBaseDeDatos($sql);
        return empty($resultados) ? null : $resultados[0];
    }

    // -------------------------------
    // Obtiene las competencias asociadas a un tema específico
    // -------------------------------
    function obtenerCompetenciasDeTema($idTema)
    {
        $sql = "
            SELECT cc.codigo, cc.texto
            FROM competencias_temas cmt
            INNER JOIN competencias_ciclos cc ON cmt.idCompetencia = cc.id
            WHERE cmt.idTema = " . (int)$idTema;
        return consultarBaseDeDatos($sql);
    }

    // -------------------------------
    // Obtiene las competencias profesionales de un ciclo para una materia específica
    // -------------------------------
    function obtenerCompetenciasProfesionales($idCiclo, $idMateria = 0)
    {
        $sql = "
            SELECT DISTINCT cc.codigo, cc.texto, cc.orden
            FROM competencias_ciclos cc
            INNER JOIN competencias_materias cm ON cc.id = cm.idCompetencia
            WHERE cc.idCiclo = {$idCiclo}
                AND cc.tipo = 1
                AND cm.idMateria = {$idMateria}
            ORDER BY cc.orden";

        return consultarBaseDeDatos($sql);
    }

    // -------------------------------
    // Obtiene todas las competencias profesionales de un ciclo
    // -------------------------------
    function obtenerCompetenciasProfesionalesPccf($idCiclo)
    {
        $sql = "
            SELECT DISTINCT codigo, texto, orden
            FROM competencias_ciclos
            WHERE idCiclo = {$idCiclo} AND tipo = 1
            ORDER BY orden";

        return consultarBaseDeDatos($sql);
    }

    // -------------------------------
    // Obtiene las competencias de empleabilidad asociadas a un ciclo específico
    // -------------------------------
    function obtenerCompetenciasEmpleabilidad($idCiclo)
    {
        $sql = "
            SELECT cc.codigo, cc.texto
            FROM competencias_ciclos cc
            WHERE cc.idCiclo = {$idCiclo} and cc.tipo = '2'
            ORDER BY cc.orden";
        return consultarBaseDeDatos($sql);
    }

    // -------------------------------
    // Obtiene los datos de una materia específica
    // -------------------------------
    function obtenerDatosMateria($idMateria)
    {
        $sql = "
            SELECT 
                cursos.nombre AS curso,
                cursos.categoria,
                materias.nombre AS materia,
                materias.horas_empresa AS horas_empresa,
                materias.horas AS horas,
                departamentos.id AS id_departamento,
                departamentos.nombre AS departamento
            FROM cursos
            INNER JOIN materias ON cursos.id = materias.idCurso
            INNER JOIN departamentos ON materias.idDepartamento = departamentos.id
            WHERE materias.id = " . (int)$idMateria;

        $resultados = consultarBaseDeDatos($sql);
        return empty($resultados) ? null : $resultados[0];
    }

    // -------------------------------
    // Obtiene la lista de profesores que imparten la materia
    // -------------------------------
    function obtenerProfesoresMateria($idMateria)
    {
        $sql = "
            SELECT p.nombre
            FROM profesores p
            INNER JOIN seleccion s ON p.id = s.idProfesor
            WHERE s.idMateria = " . (int)$idMateria . "
            AND s.idEscenario IN (
                SELECT id FROM escenarios_desideratas WHERE actual = 1
            )
            GROUP BY p.id
            ORDER BY p.orden";

        $profesores = array();
        foreach (consultarBaseDeDatos($sql) as $fila) {
            $profesores[] = $fila['nombre'];
        }
        return $profesores;
    }

    // -------------------------------
    // Obtiene la lista de profesores que imparten la materia
    // -------------------------------
    function obtenerIdCicloPorMateria($idMateria)
    {
        $idMateria = (int)$idMateria;

        $sql = "
            SELECT ciclos.id
            FROM ciclos
            INNER JOIN cursos_ciclos ON ciclos.id = cursos_ciclos.idCiclo
            INNER JOIN cursos ON cursos_ciclos.idCurso = cursos.id
            INNER JOIN materias ON materias.idCurso = cursos.id
            WHERE materias.id = {$idMateria}
            LIMIT 1";

        $resultado = consultarBaseDeDatos($sql);

        if (!empty($resultado)) {
            return (int)$resultado[0]['id'];
        }

        return 0;
    }

    // -------------------------------
    // Obtiene las horas anuales de una materia específica
    // -------------------------------
    function obtenerHorasAnualesPorMateria($idMateria) {
        $sql = "SELECT horas_anuales FROM materias WHERE id = $idMateria";
        $horas = consultarBaseDeDatos($sql);
        $horas_materia = $horas ? (int)$horas[0]['horas_anuales'] : 0;
        return $horas_materia;
    }

    // -------------------------------
    // Obtiene todos los apartados de programación según la categoría del curso
    // -------------------------------
    function obtenerApartadosProgramacion($categoria)
    {
        $sql = "
            SELECT * FROM apartados_programaciones
            WHERE categoria IS NULL OR categoria = 'TODOS' OR categoria LIKE '%$categoria%'
            ORDER BY orden";

        return consultarBaseDeDatos($sql);
    }

    // -------------------------------
    // Obtiene todos los datos de un ciclo formativo específico
    // -------------------------------
    function obtenerDatosCiclo($idCiclo)
    {
        $sql = "SELECT * FROM ciclos where id = $idCiclo";

        $resultado = consultarBaseDeDatos($sql);

        if (!empty($resultado)) {
            return $resultado[0];
        }

        return [];
    }

    // -------------------------------
    // Obtiene el idDepartamento asociado a un ciclo formativo
    // -------------------------------
    function obtenerIdDepartamentoDeCiclo($idCiclo)
    {
        $sql = "
            SELECT m.idDepartamento
            FROM materias m
            JOIN cursos c ON m.idCurso = c.id
            JOIN cursos_ciclos cc ON c.id = cc.idCurso
            WHERE cc.idCiclo = {$idCiclo}
              AND m.idDepartamento IS NOT NULL
            LIMIT 1";

        $resultado = consultarBaseDeDatos($sql);

        if (!empty($resultado)) {
            return (int)$resultado[0]['idDepartamento'];
        }

        return 0;
    }
    
    // -------------------------------
    // Obtiene el contenido de un apartado (personalizado o por defecto)
    // -------------------------------
    function obtenerContenidoApartado($idApartado, $idMateria, $idDepartamento = 0)
    {
        // Primero: contenido personalizado
        $sql = "SELECT texto FROM contenidos_programaciones 
                WHERE idApartado = " . (int)$idApartado . " 
                AND idMateria = " . (int)$idMateria;
        $contenido = consultarBaseDeDatos($sql);

        if (!empty($contenido) && trim($contenido[0]['texto']) !== '') {
            return $contenido[0]['texto'];
        }

        // Segundo: contenido por defecto del departamento
        $sql = "SELECT texto FROM contenidos_defecto_programaciones 
                WHERE idDepartamento = " . (int)$idDepartamento . " 
                AND idApartado = " . (int)$idApartado;
        $defecto = consultarBaseDeDatos($sql);

        if (!empty($defecto) && trim($defecto[0]['texto']) !== '') {
            return $defecto[0]['texto'];
        }

        return '';
    }

    // -------------------------------
    // Obtiene el contenido de un apartado (personalizado o por defecto)
    // -------------------------------
    function obtenerContenidoApartadoPccf($idApartado, $idCiclo, $idDepartamento = 0)
    {
        // Primero: contenido personalizado
        $sql = "SELECT texto FROM contenidos_pccf WHERE idApartado = $idApartado AND idCiclo = $idCiclo";
        $contenido = consultarBaseDeDatos($sql);

        if (!empty($contenido) && trim($contenido[0]['texto']) !== '') {
            return $contenido[0]['texto'];
        }

        // Segundo: contenido por defecto
        $sql = "SELECT texto FROM contenidos_defecto_pccf WHERE idApartado = $idApartado and idDepartamento = $idDepartamento";
        $defecto = consultarBaseDeDatos($sql);

        if (!empty($defecto) && trim($defecto[0]['texto']) !== '') {
            return $defecto[0]['texto'];
        }

        return '';
    }
?>