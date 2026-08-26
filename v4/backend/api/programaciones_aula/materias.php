<?php
// FASE 2.4 — Materias con programación del profesor (todos sus grupos,
// escenario actual). Opción propia de v4: como en la propuesta pedagógica,
// primero se elige la materia; cada materia va con su flag "terminada"
// (materias.terminada_programacion), que es lo que habilita importar la
// programación de aula a partir de la propuesta pedagógica.
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

pcCmp_listarMaterias();
