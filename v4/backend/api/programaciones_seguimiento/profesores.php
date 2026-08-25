<?php
// API: Listar todos los profesores para la selección en el seguimiento de programaciones
// (equivalente a v3 includes/seleccion_profesor.php: todos los profesores, por nombre)
// Lógica compartida con el aula (lib/programaciones_compartidas.php)
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

pcCmp_listarProfesores();
?>
