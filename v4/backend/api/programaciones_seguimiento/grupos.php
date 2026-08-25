<?php
// API: Listar grupos de un profesor para una materia (seguimiento de programaciones)
// Lógica compartida con el aula (lib/programaciones_compartidas.php)
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

pcCmp_listarGrupos();
?>
