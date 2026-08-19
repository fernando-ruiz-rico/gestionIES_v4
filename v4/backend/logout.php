<?php
/**
 * Página de logout - cierra la sesión
 */

@session_start();
session_destroy();
header("Location: login.php");
exit;

?>
