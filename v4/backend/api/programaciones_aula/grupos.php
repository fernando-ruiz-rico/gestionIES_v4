<?php
// FASE 2.4 — Grupos que imparte el profesor en la materia elegida
// (escenario actual). Opción propia de v4: la programación de aula se elige
// por profesor + materia + grupo (el desplegable de grupos depende de la
// materia elegida, igual que en el seguimiento de programaciones).
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

pcCmp_listarGrupos();
