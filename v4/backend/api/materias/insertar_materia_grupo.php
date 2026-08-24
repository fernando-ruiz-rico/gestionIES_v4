<?php
// Inserta/Modifica los datos de una materia para un grupo determinado.
// Fiel a v3: v3/ajax/materias/insertar_materia_grupo.php (jefe o admin).
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    sendJSONError('Datos inválidos', 400);
}

$idMateria = intval(isset($datos['idMateria']) ? $datos['idMateria'] : 0);
$idGrupo = intval(isset($datos['idGrupo']) ? $datos['idGrupo'] : 0);

if ($idMateria <= 0 || $idGrupo <= 0) {
    sendJSONError('Parámetros inválidos', 400);
}

$cantidad = intval(isset($datos['cantidad']) ? $datos['cantidad'] : 1);
$horas = intval(isset($datos['horas']) ? $datos['horas'] : 0);
$horasComplementarias = intval(isset($datos['horas_complementarias']) ? $datos['horas_complementarias'] : 0);
$minNumProfesores = intval(isset($datos['min_num_profesores']) ? $datos['min_num_profesores'] : 0);
$maxGruposProfesor = intval(isset($datos['max_grupos_profesor']) ? $datos['max_grupos_profesor'] : 0);

try {
    $db = Db::open();

    // Comprobamos si ya existe un registro para ese grupo y materia
    $existe = $db->fetchOne("SELECT * FROM materias_grupos WHERE idMateria = ? AND idGrupo = ?", $idMateria, $idGrupo) !== null;

    if ($existe) {
        $db->execute("UPDATE materias_grupos SET cantidad=?, horas=?, horas_complementarias=?, min_num_profesores=?, max_grupos_profesor=? WHERE idMateria=? AND idGrupo=?", $cantidad, $horas, $horasComplementarias, $minNumProfesores, $maxGruposProfesor, $idMateria, $idGrupo);
    } else {
        $db->execute("INSERT INTO materias_grupos (idMateria, idGrupo, cantidad, horas, horas_complementarias, min_num_profesores, max_grupos_profesor) VALUES (?, ?, ?, ?, ?, ?, ?)", $idMateria, $idGrupo, $cantidad, $horas, $horasComplementarias, $minNumProfesores, $maxGruposProfesor);
    }

    sendJSONSuccess(null, 'Datos del grupo guardados');
} catch (DbException $e) {
    sendJSONError('Error al guardar los datos del grupo: ' . $e->getMessage(), 500);
}
?>
