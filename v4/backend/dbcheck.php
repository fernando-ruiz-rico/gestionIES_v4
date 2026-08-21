<?php
$c = new mysqli('127.0.0.1', 'root', '', 'gestionies');
function q($c, $sql) { $r = $c->query($sql); return $r ? mysqli_fetch_array($r) : "QUERY FAIL: " . $c->error; }

// Check exact table name variants
foreach (array('contenidos_defcto_temas', 'contenidos_defcto_temas', 'contenidos_defcto_temas') as $t) {
    $res = q($c, "SHOW TABLES LIKE '$t'");
    echo "Check '$t' => " . json_encode($res) . "<br>";
}

// Get the actual table that matches 'contenidos' + 'temas'
$r = $c->query("SHOW TABLES LIKE 'contenidos%temas'");
$match = $r ? mysqli_fetch_array($r) : array('x'=>'NONE');
$tbl = $match[0];
echo "Matched table: <b>$tbl</b><hr>";

// Show columns
$r2 = $c->query("SHOW COLUMNS FROM `$tbl`");
echo "Columns:<pre>"; while($f = $r2->fetch_array()) echo $f[0] . "\n"; echo "</pre>";

$c->close();
