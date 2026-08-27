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
