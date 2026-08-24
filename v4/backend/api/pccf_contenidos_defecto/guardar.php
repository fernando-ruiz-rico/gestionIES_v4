<?php
// API para guardar el contenido por defecto de un apartado del PCCF (Fase 3.3)
// Inserta o actualiza el contenido del apartado para el departamento indicado.
// Con texto vacío se elimina la fila (fiel a v3).

require_once '../../config.php';
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
    $filaApto = $db->fetchOne("SELECT tipo FROM apartados_pccf WHERE id = ?", $idApartado);
    if (!$filaApto || intval($filaApto['tipo']) != 0) {
        sendJSONError('El apartado seleccionado no es editable: se rellena automáticamente a partir de la base de datos', 400);
    }

    $texto = trim($texto);

    // Sin texto: eliminamos la fila por defecto.
    if ($texto === '') {
        $n = $db->execute("DELETE FROM contenidos_defecto_pccf WHERE idApartado = ? AND idDepartamento = ?", $idApartado, $idDepartamento);
        if ($n <= 0) {
            sendJSONError('No existe contenido que eliminar', 400);
        }
        sendJSONSuccess(null, 'Contenido por defecto eliminado correctamente');
    } else {
        // Comprobamos si ya existe contenido para ese apartado y departamento.
        $filaCheck = $db->fetchOne("SELECT id FROM contenidos_defecto_pccf WHERE idApartado = ? AND idDepartamento = ?", $idApartado, $idDepartamento);

        if ($filaCheck) {
            // Actualizamos.
            $db->execute("UPDATE contenidos_defecto_pccf SET texto = ? WHERE idApartado = ? AND idDepartamento = ?", $texto, $idApartado, $idDepartamento);
        } else {
            // Insertamos.
            $db->execute("INSERT INTO contenidos_defecto_pccf (idDepartamento, idApartado, texto) VALUES (?, ?, ?)", $idDepartamento, $idApartado, $texto);
        }

        sendJSONSuccess(null, 'Datos guardados correctamente');
    }
} catch (DbException $e) {
    sendJSONError('Error de base de datos: ' . $e->getMessage(), 500);
}
?>
