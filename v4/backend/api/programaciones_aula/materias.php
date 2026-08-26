<?php
// FASE 2.4 — Materias del profesor en un grupo (con programación, escenario
// actual). Opción propia de v4: cada materia va con su flag "terminada"
// (materias.terminada_programacion), que es lo que habilita importar la
// programación de aula a partir de la propuesta pedagógica.
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

pcCmp_listarMateriasGrupo();
