<?php
/**
 * Carga los datos de un departamento específico en formato JSON
 */

include('../../includes/database.php');

if (!empty($_REQUEST['idDepartamento'])) {
    $id = intval($_REQUEST['idDepartamento']);
    $result = mysqli_query($db, "SELECT * FROM departamentos WHERE id = $id");
    
    if ($fila = mysqli_fetch_assoc($result)) {
        echo json_encode($fila);
    }
    
    mysqli_free_result($result);
}

include('../../includes/database2.php');

?>
