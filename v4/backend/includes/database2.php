<?php
/**
 * Cierre de la conexión a la base de datos
 */

if (isset($db)) {
    mysqli_close($db);
}

?>
