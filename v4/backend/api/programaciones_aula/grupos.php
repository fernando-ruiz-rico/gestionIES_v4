<?php
// FASE 2.4 — Grupos que imparte el profesor (escenario actual).
// Opción propia de v4: la programación de aula se elige por profesor + grupo
// (el desplegable de grupos depende del profesor elegido; no de una materia).
require_once '../../config.php';
require_once '../../lib/programaciones_compartidas.php';
cabeceraJson();

pcCmp_listarGruposProfesor();
