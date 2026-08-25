<?php
// API: Listar grupos de un profesor para una materia (programaciones de aula)
// Lógica compartida con el seguimiento (lib/programaciones_compartidas.php)
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

pcCmp_listarGrupos();
?>
