-- «Propuesta Pedagógica»: flag que indica si la propuesta de una materia está terminada
ALTER TABLE `materias`
  ADD `terminada_programacion` TINYINT(1) NOT NULL DEFAULT 0;

-- «Programaciones de aula»: copia por profesor y grupo de la propuesta pedagógica
CREATE TABLE IF NOT EXISTS `contenidos_programaciones_aula` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idMateria` int NOT NULL,
  `idApartado` int NOT NULL,
  `idGrupo` int NOT NULL,
  `idProfesor` int NOT NULL,
  `texto` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- «Programaciones de aula»: copia INDEPENDIENTE de las unidades (temas) de la
-- propuesta pedagógica, propia de cada profesor y grupo. Especular con `temas`
-- (mismas columnas) añadiendo idGrupo e idProfesor para distinguir copias.
CREATE TABLE IF NOT EXISTS `temas_aula` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idMateria` int NOT NULL,
  `idGrupo` int NOT NULL,
  `idProfesor` int NOT NULL,
  `orden` int NOT NULL DEFAULT 0,
  `titulo` varchar(200) NOT NULL,
  `horas` int NOT NULL DEFAULT 0,
  `trimestre` int NOT NULL DEFAULT 0,
  `peso_evaluacion` int NOT NULL DEFAULT 0,
  `descripcion` text NOT NULL,
  `justificacion` text NOT NULL,
  `contexto` text NOT NULL,
  `contenidos` text NOT NULL,
  `secuenciacion` text NOT NULL,
  `recursos` text NOT NULL,
  `evaluacion` text NOT NULL,
  `metodologia` text NOT NULL,
  `adaptaciones` text NOT NULL,
  `contexto_defecto` TINYINT(1) NOT NULL DEFAULT 1,
  `recursos_defecto` TINYINT(1) NOT NULL DEFAULT 1,
  `metodologia_defecto` TINYINT(1) NOT NULL DEFAULT 1,
  `adaptaciones_defecto` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- «Programaciones de aula»: copia independiente de los resultados de
-- aprendizaje (RA) de la propuesta, propia de cada profesor y grupo.
CREATE TABLE IF NOT EXISTS `resultados_aprendizaje_aula` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idMateria` int NOT NULL,
  `idGrupo` int NOT NULL,
  `idProfesor` int NOT NULL,
  `orden` int NOT NULL DEFAULT 0,
  `texto` text NOT NULL,
  `porcentaje_empresa` int NOT NULL DEFAULT 0,
  `porcentaje_evaluacion` int NOT NULL DEFAULT 0,
  `es_clave` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- «Programaciones de aula»: copia independiente de los criterios de evaluación
-- (CE) que vinculan RA (resultados_aprendizaje_aula.id) con temas (temas_aula.id)
-- para cada profesor y grupo.
CREATE TABLE IF NOT EXISTS `criterios_temas_aula` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idMateria` int NOT NULL,
  `idRA` int NOT NULL,
  `codigo` varchar(2) NOT NULL,
  `idTema` int NOT NULL,
  `idGrupo` int NOT NULL,
  `idProfesor` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- «Programaciones de aula»: copia INDEPENDIENTE de los textos de los criterios
-- de evaluación (CE) de la propuesta (especular con `criterios_evaluacion`),
-- propia de cada profesor y grupo. idRA referencia resultados_aprendizaje_aula.id.
CREATE TABLE IF NOT EXISTS `criterios_evaluacion_aula` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idMateria` int NOT NULL,
  `idRA` int NOT NULL,
  `codigo` varchar(2) NOT NULL,
  `texto` varchar(200) NOT NULL,
  `idGrupo` int NOT NULL,
  `idProfesor` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- «Programaciones de aula»: copia INDEPENDIENTE de las competencias de cada
-- unidad (especular con `competencias_temas`), propia de cada profesor y grupo.
-- idCompetencia referencia el catálogo compartido `competencias_ciclos`; idTema
-- referencia temas_aula.id.
CREATE TABLE IF NOT EXISTS `competencias_temas_aula` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idMateria` int NOT NULL,
  `idCompetencia` int NOT NULL,
  `idTema` int NOT NULL,
  `idGrupo` int NOT NULL,
  `idProfesor` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
