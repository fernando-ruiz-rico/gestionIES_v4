<?php
$c = mysqli_connect('127.0.0.1', 'root', '', 'gestionies');
mysqli_set_charset($c, 'utf8');
if (!$c) { echo "CONNECT_FAILED: " . mysqli_connect_error() . "\n"; exit; }

echo "=== Estructura contenidos_defcto_programaciones (hermana) ===\n";
$r = mysqli_query($c, "SHOW COLUMNS FROM contenidos_defcto_programaciones");
if ($r) { while ($f = mysqli_fetch_assoc($r)) { echo $f['Field'] . " " . $f['Type'] . "\n"; } }

echo "\n=== ¿Existe contenidos_defcto_temas? ===\n";
$r2 = mysqli_query($c, "SHOW TABLES LIKE 'contenidos_defcto_temas'");
echo "rows: " . mysqli_num_rows($r2) . "\n";

echo "\n=== Estructura contenidos_defcto_pccf (otra hermana) ===\n";
$r3 = mysqli_query($c, "SHOW COLUMNS FROM contenidos_defcto_pccf");
if ($r3) { while ($f = mysqli_fetch_assoc($r3)) { echo $f['Field'] . "\n"; } }

mysqli_close($c);
