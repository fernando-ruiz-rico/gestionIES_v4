<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3 (insertar_materia.php): jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

// --- Campos del formulario (nombres como en v3/modales/materias.php) ---
$nombre = trim(isset($datos['nombre']) ? $datos['nombre'] : '');
$idCurso = intval(isset($datos['idCurso']) ? $datos['idCurso'] : 0);
$tipo = trim(isset($datos['tipo']) ? $datos['tipo'] : 'OTRA');
if ($tipo !== 'TUTORIA' && $tipo !== 'INGLES') $tipo = 'OTRA';

// Enteros de referencia (valor por defecto si no llega)
$cantidad = intval(isset($datos['cantidad']) ? $datos['cantidad'] : 1);
$horas = intval(isset($datos['horas']) ? $datos['horas'] : 0);
$horasComplementarias = intval(isset($datos['horas_complementarias']) ? $datos['horas_complementarias'] : 0);
$minNumProfesores = intval(isset($datos['min_num_profesores']) ? $datos['min_num_profesores'] : 0);
$maxGruposProfesor = intval(isset($datos['max_grupos_profesor']) ? $datos['max_grupos_profesor'] : 0);

// Casillas de verificación (vienen como true/false o 1/0; con default de v3)
$computables = (isset($datos['computables_horas_grupo']) && $datos['computables_horas_grupo']) ? 1 : 0;
$asignadaDirectiva = (isset($datos['asignada_directiva']) && $datos['asignada_directiva']) ? 1 : 0;
$tieneProgramacion = (isset($datos['tiene_programacion']) && $datos['tiene_programacion']) ? 1 : 0;
$divisible = (isset($datos['divisible']) && $datos['divisible']) ? 1 : 0;

// Campos nulos: 0 o cadena vacía => NULL (igual que v3)
$idDepartamento = intval(isset($datos['idDepartamento']) ? $datos['idDepartamento'] : 0);
$idDepartamento = ($idDepartamento == 0) ? null : $idDepartamento;
$idEspecialidad = trim(isset($datos['idEspecialidad']) ? $datos['idEspecialidad'] : '');
$idEspecialidad = ($idEspecialidad === '') ? null : $idEspecialidad;
$codigoOficial = trim(isset($datos['codigoOficial']) ? $datos['codigoOficial'] : '');
$codigoOficial = ($codigoOficial === '') ? null : $codigoOficial;
$nombreOficial = trim(isset($datos['nombreOficial']) ? $datos['nombreOficial'] : '');
$nombreOficial = ($nombreOficial === '') ? null : $nombreOficial;
$creditosECTS = intval(isset($datos['creditosECTS']) ? $datos['creditosECTS'] : 0);
$creditosECTS = ($creditosECTS == 0) ? null : $creditosECTS;
$horasAnuales = intval(isset($datos['horasAnuales']) ? $datos['horasAnuales'] : 0);
$horasAnuales = ($horasAnuales == 0) ? null : $horasAnuales;

$id = isset($datos['id']) ? intval($datos['id']) : 0;

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre obligatorio']);
    exit;
}

try {
    $db = Db::open();
    if ($id > 0) {
        // UPDATE (fiel a v3: no se toca idCurso al editar)
        $db->execute("UPDATE materias SET nombre=?, cantidad=?, horas=?, horas_complementarias=?, idDepartamento=?, idEspecialidad=?, computables_horas_grupo=?, asignada_directiva=?, min_num_profesores=?, max_grupos_profesor=?, tiene_programacion=?, divisible=?, tipo=?, codigo_oficial=?, nombre_oficial=?, creditos_ects=?, horas_anuales=? WHERE id=?",
            $nombre, $cantidad, $horas, $horasComplementarias, $idDepartamento, $idEspecialidad, $computables, $asignadaDirectiva, $minNumProfesores, $maxGruposProfesor, $tieneProgramacion, $divisible, $tipo, $codigoOficial, $nombreOficial, $creditosECTS, $horasAnuales, $id);
        $nuevoId = $id;
    } else {
        // CREATE: la columna "grupo" es NOT NULL sin default; se guarda vacía
        $db->execute("INSERT INTO materias (nombre, idCurso, cantidad, horas, horas_complementarias, idDepartamento, idEspecialidad, computables_horas_grupo, asignada_directiva, min_num_profesores, max_grupos_profesor, tiene_programacion, divisible, tipo, codigo_oficial, nombre_oficial, creditos_ects, horas_anuales, grupo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '')",
            $nombre, $idCurso, $cantidad, $horas, $horasComplementarias, $idDepartamento, $idEspecialidad, $computables, $asignadaDirectiva, $minNumProfesores, $maxGruposProfesor, $tieneProgramacion, $divisible, $tipo, $codigoOficial, $nombreOficial, $creditosECTS, $horasAnuales);
        $nuevoId = $db->insertId();

        // Fiel a v3: al crear, se puebla la configuración por defecto (materias_grupos)
        // para todos los grupos del curso, con los datos de referencia de la materia.
        if ($idCurso > 0) {
            $grupos = $db->fetchAll("SELECT id FROM grupos WHERE idCurso = ?", $idCurso);
            foreach ($grupos as $filaG) {
                $db->execute("INSERT INTO materias_grupos (idMateria, idGrupo, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    $nuevoId, intval($filaG['id']), $cantidad, $horas, $horasComplementarias, $minNumProfesores, $maxGruposProfesor);
            }
        }
    }
} catch (DbException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Materia guardada correctamente',
    'id' => (int)$nuevoId
]);
?>
