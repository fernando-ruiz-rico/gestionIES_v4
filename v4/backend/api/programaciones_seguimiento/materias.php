<?php
// API: Listar materias con programación activa para un profesor (seguimiento de programaciones)
// Solo las del CURSO ACTUAL (e.actual = 1), fiel a v3.
// Lógica compartida con el aula (lib/programaciones_compartidas.php)
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

pcCmp_listarMaterias();
?>
