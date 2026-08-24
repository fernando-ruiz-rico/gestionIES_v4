<?php
// Inserta/Modifica los datos de una materia para un grupo determinado.
// Fiel a v3: v3/ajax/materias/insertar_materia_grupo.php (jefe o admin).
require_once '../../config.php';
cabeceraJson();

// Permiso fiel a v3: jefe de departamento o admin
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$datos = cuerpoJson();
if (!$datos) {
    sendJSONError('Datos inválidos', 400);
}

$idMateria = datosOptimoInt($datos, 'idMateria');
$idGrupo = datosOptimoInt($datos, 'idGrupo');

if ($idMateria <= 0 || $idGrupo <= 0) {
    sendJSONError('Parámetros inválidos', 400);
}

$cantidad = datosOptimoInt($datos, 'cantidad', 1);
$horas = datosOptimoInt($datos, 'horas');
$horasComplementarias = datosOptimoInt($datos, 'horas_complementarias');
$minNumProfesores = datosOptimoInt($datos, 'min_num_profesores');
$maxGruposProfesor = datosOptimoInt($datos, 'max_grupos_profesor');

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
