<?php
// API endpoint para guardar contenido por defecto de un apartado
// Inserta o actualiza la fila de la tabla contenidos_defecto_programaciones.
// Con texto vacío se elimina la fila (fiel a v3).

require_once '../../config.php';
require_once '../../lib/contenidos.php';
cabeceraJson();

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$data = cuerpoJson();

$idApartado = datosOptimoInt($data, 'idApartado');
$idDepartamento = datosOptimoInt($data, 'idDepartamento');
$texto = datosOptimo($data, 'texto');

if ($idApartado <= 0 || $idDepartamento <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();
    // Este endpoint no avisa si no había nada que eliminar ($avisaSinFila = false)
    contenidos_guardarTexto($db, 'contenimientos_defecto_programaciones',
        array(array('idApartado', $idApartado), array('idDepartamento', $idDepartamento)),
        $texto, 'Contenido eliminado correctamente', 'Contenido guardado correctamente', false);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
