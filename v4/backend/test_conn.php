<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'gestionies');
if (!$conn) {
    echo "CONNECT FAILED: " . mysqli_connect_error() . "\n";
    exit;
}
echo "Connected. Server info: " . mysqli_get_host_info($conn) . "\n";
$res = mysqli_query($conn, "SHOW TABLES LIKE 'contenidos_defcto_temas';");
echo "Tables: " . ($res ? "FOUND\n" : "NOT FOUND (" . mysqli_error($conn) . ")");
$res2 = mysqli_query($conn, "SELECT contexto, recursos, metodologia, acciones FROM contenidos_defcto_temas WHERE idDepartamento = 1;");
echo "Query: " . ($res2 ? "OK" : "FAILED (" . mysqli_error($conn) . ")");
mysqli_close($conn);
