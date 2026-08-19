<?php
    // Obtiene un valor de $_REQUEST como cadena; por defecto: ''
    function getReqStr($key, $default = '') {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

    // Obtiene un valor de $_REQUEST como entero; por defecto: 0
    function getReqInt($key, $default = 0) {
        return isset($_REQUEST[$key]) ? (int)$_REQUEST[$key] : $default;
    }

    // -------------------------------
    // Ejecuta una consulta SQL y devuelve un array de filas asociativas
    // -------------------------------
    function consultarBaseDeDatos($sql)
    {
        global $db;
        if (empty($db)) {
            include('database.php');
        }
        $resultado = mysqli_query($db, $sql);
        if (!$resultado) {
            error_log("Error SQL: " . mysqli_error($db) . " | Query: " . $sql);
            return array();
        }

        $filas = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $filas[] = $fila;
        }
        mysqli_free_result($resultado);
        return $filas;
    }

    // -------------------------------
    // Ejecuta una actualización SQL
    // -------------------------------
    function actualizarBaseDeDatos($sql)
    {
        global $db;
        if (empty($db)) {
            include('database.php');
        }

        $resultado = mysqli_query($db, $sql);
        
        if (!$resultado) {
            error_log("Error SQL en actualización: " . mysqli_error($db) . " | Query: " . $sql);
            return false;
        }

        // Opcional: devolver el número de filas afectadas
        return mysqli_affected_rows($db);
    }

    // -------------------------------
    // Determina el curso académico actual (ej. 2023/2024)
    // -------------------------------
    function obtenerCursoAcademico()
    {
        $mes = (int)date('n');
        $anio = (int)date('Y');
        if ($mes >= 9) {
            return array($anio, $anio + 1);
        } else {
            return array($anio - 1, $anio);
        }
    }

    // -------------------------------
    // Determina si un texto está vacío o contiene solo espacios o etiquetas HTML vacías
    // -------------------------------
    function estaVacio($datos) {
        return !isset($datos) || 
               empty($datos) || 
               (is_string($datos) && trim(str_replace("\xc2\xa0", ' ', html_entity_decode(strip_tags($datos)))) == '');
    }

    // -------------------------------
    // Determina el curso académico anterior (ej. 2022/2023)
    // -------------------------------
    function cursoAnterior()
    {
        $fecha = explode("/", date("n/Y"));
        if (intval($fecha[0]) >= 9)
        {
            $anyo1Curso = intval($fecha[1]) - 1;
            $anyo2Curso = $anyo1Curso + 1;
        } else {
            $anyo2Curso = intval($fecha[1]) - 1;
            $anyo1Curso = $anyo2Curso - 1;
        }
        return "$anyo1Curso/$anyo2Curso";
    }

    // -------------------------------
    // Determina el curso académico actual (ej. 2022/2023)
    // -------------------------------
    function cursoActual()
    {
        $fecha = explode("/", date("n/Y"));
        if (intval($fecha[0]) >= 9)
        {
            $anyo1Curso = intval($fecha[1]);
            $anyo2Curso = $anyo1Curso + 1;
        } else {
            $anyo2Curso = intval($fecha[1]);
            $anyo1Curso = $anyo2Curso - 1;
        }
        return $anyo1Curso . "/" . $anyo2Curso;
    }

    // -------------------------------
    // Obtiene el acrónimo de un texto, excluyendo ciertas palabras comunes
    // -------------------------------
    function obtenerAcronimo($texto) {
        if ($texto == 'Programación') return 'PRO';
        if (strpos($texto, 'Inglés') !== false) return 'ING';

        // Definimos la lista (pueden estar en mayúsculas o minúsculas aquí, 
        // pero las procesaremos para que coincidan)
        $excluirOriginal = ['de', 'del', 'la', 'las', 'el', 'los', 'en', 'y', 'a', 'al', 'e', 'para', 'GM', '(GM)', 'GS', '(GS)', 'aplicada'];
        
        // Pasamos toda la lista de exclusión a minúsculas
        $excluir = array_map('mb_strtolower', $excluirOriginal);

        // 1. Convertir a minúsculas y dividir
        $palabras = preg_split('/[\s-]+/', mb_strtolower($texto));
        $acronimo = "";

        foreach ($palabras as $palabra) {
            // 2. Ahora la comparación sí funcionará (minúscula vs minúscula)
            if (!in_array($palabra, $excluir) && !empty($palabra)) {
                $acronimo .= mb_substr($palabra, 0, 1);
            }
        }
        return mb_strtoupper($acronimo);
    }
?>