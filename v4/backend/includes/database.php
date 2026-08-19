<?php
/**
 * Configuración de la base de datos
 * Esta archivo debe ser configurado con las credenciales reales del servidor
 */

// Conexión a la base de datos
$db = mysqli_connect("localhost", "root", "");
mysqli_select_db($db, "gestionies");
mysqli_set_charset($db, 'utf8');

?>
