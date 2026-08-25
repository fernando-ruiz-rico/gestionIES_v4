<?php
// API para guardar el contenido por defecto de un apartado del PCCF (Fase 3.3)
// Inserta o actualiza el contenido del apartado para el departamento indicado.
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

    // Fiel a v3: sólo se editan aquí los apartados que admiten contenido por
    // defecto y son editables (tipo == 0). Los de otro tipo se rellenan
    // automáticamente a partir de la base de datos y no se guardan, así que
    // rechazamos cualquier apartado no editable aunque llegue directamente.
    $filaApta = $db->fetchOne("SELECT tipo FROM apartados_pccf WHERE id = ?", $idApartado);
    if (!$filaApta || intval($filaApta['tipo']) != 0) {
        sendJSONError('El apartado seleccionado no es editable: se rellena automáticamente a partir de la base de datos', 400);
    }

    contenidos_guardarTexto($db, 'contenidos_defecto_pccf',
        array(array('idDepartamento', $idDepartamento), array('idApartado', $idApartado)),
        $texto, 'Contenido por defecto eliminado correctamente', 'Datos guardados correctamente', true);
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
