<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'gestionies');
if (!$conn) { echo "CONNECT: " . mysqli_connect_error() . "\n"; exit; }
mysqli_set_charset($conn, 'utf8');
$res = mysqli_query($conn, "SHOW TABLES");
$tables = [];
while ($r = mysqli_fetch_assoc($res)) { $tables[] = array_values($r)[0]; }
sort($tables);
echo "Total tables: " . count($tables) . "\n";
echo "contenidos_defcto_temas en DB viva: " . (in_array('contenidos_defcto_temas',$tables)?"EXISTE":"NO EXISTE") . "\n";
echo "departamentos en DB viva: " . (in_array('departamentos',$tables)?"SI":"NO") . "\n";
echo "=== DESCRIBE tabla (si existe) ===\n";
$r = @mysqli_query($conn, "SHOW COLUMNS FROM contenidos_defcto_temas");
if ($r) { while($row=mysqli_fetch_assoc($r)) echo $row['Field']."\n"; } else { echo "No se pudo DESCRIBIR: ".mysqli_error($conn)."\n"; }
mysqli_close($conn);
