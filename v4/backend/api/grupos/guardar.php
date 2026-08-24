<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: solo admin
checkPermission(array(ROLE_ADMIN));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    sendJSONError('Datos inválidos', 400);
}

$nombre = trim(isset($datos['nombre']) ? $datos['nombre'] : '');
$abreviatura = trim(isset($datos['abreviatura']) ? $datos['abreviatura'] : '');
$idCurso = intval(isset($datos['idCurso']) ? $datos['idCurso'] : 0);
$mostrar = intval(isset($datos['mostrar']) ? $datos['mostrar'] : 1);
$horas_complementarias_dual = intval(isset($datos['horas_complementarias_dual']) ? $datos['horas_complementarias_dual'] : 0);
$id = isset($datos['id']) ? intval($datos['id']) : 0;

if (empty($nombre)) {
    sendJSONError('Nombre obligatorio', 400);
}

try {
    $db = Db::open();
    if ($id > 0) {
        // Fiel a v3: al modificar solo se actualizan nombre, abreviatura, mostrar y
        // horas_complementarias_dual (ni idCurso ni orden se tocan aquí: el orden se
        // gestiona en el endpoint de reordenación y el curso no cambia al editar).
        $db->execute("UPDATE grupos SET nombre=?, abreviatura=?, mostrar=?, horas_complementarias_dual=? WHERE id=?", $nombre, $abreviatura, $mostrar, $horas_complementarias_dual, $id);
    } else {
        // Fiel a v3: al crear no se fija orden (queda NULL) y se copia la
        // configuración de las materias del curso a materias_grupos para el grupo nuevo.
        $db->execute("INSERT INTO grupos (nombre, abreviatura, idCurso, mostrar, horas_complementarias_dual) VALUES (?, ?, ?, ?, ?)", $nombre, $abreviatura, $idCurso, $mostrar, $horas_complementarias_dual);
        $id = $db->insertId();

        $materias = $db->fetchAll("SELECT id, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor FROM materias WHERE idCurso = ?", $idCurso);
        foreach ($materias as $m) {
            $db->execute("INSERT INTO materias_grupos (idMateria, idGrupo, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor) VALUES (?, ?, ?, ?, ?, ?, ?)",
                intval($m['id']), $id, intval($m['cantidad']), intval($m['horas']), intval($m['horas_complementarias']), intval($m['min_num_profesores']), intval($m['max_grupos_profesor']));
        }
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}

sendJSONSuccess(array('id' => $id), 'Guardado correctamente');
?>
