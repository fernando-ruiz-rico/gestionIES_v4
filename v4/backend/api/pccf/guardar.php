<?php
// API para guardar el contenido de un PCCF (Fase 3.1 - PCCF)
// Inserta o actualiza el contenido de un ciclo y apartado concretos en la
// tabla contenidos_pccf (modelo fiel a v3). Con texto vacío se elimina la fila.

require_once '../../config.php';
require_once '../../lib/contenidos.php';
cabeceraJson();

// Permiso fiel a v3: admin o jefe de departamento
checkPermission(array(ROLE_ADMIN, ROLE_JEFE_DEPARTAMENTO));

$data = cuerpoJson();

$idCiclo = datosOptimoInt($data, 'idCiclo');
$idApartado = datosOptimoInt($data, 'idApartado');
$texto = datosOptimo($data, 'texto');

if ($idCiclo <= 0 || $idApartado <= 0) {
    sendJSONError('Parámetros no válidos', 400);
}

try {
    $db = Db::open();
    contenidos_guardarTexto($db, 'contenidos_pccf',
        array(array('idCiclo', $idCiclo), array('idApartado', $idApartado)),
        $texto, 'Contenido eliminado correctamente', 'Datos guardados correctamente', true);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
