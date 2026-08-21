-- FASE 2.7: Crear tabla contenidos_defcto_temas (fiel a v3)
-- Columnas: idDepartamento (PK), contexto, recursos, metodologia, acciones
DROP TABLE IF EXISTS `contenidos_defcto_temas`;
CREATE TABLE `contenidos_defcto_temas` (
  `idDepartamento` int(11) NOT NULL,
  `contexto` text NOT NULL,
  `recursos` text NOT NULL,
  `metodologia` text NOT NULL,
  `adaptaciones` text NOT NULL,
  PRIMARY KEY (`idDepartamento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Semilla por departamento (datos de ejemplo; v3 solo tenía el dept 1)
INSERT INTO `contenidos_defcto_temas` (`idDepartamento`, `contexto`, `recursos`, `metodologia`, `adaptaciones`) VALUES
(1, '<p>En el aula habitual con los equipos de clase</p>', '<ul>\r\n<li>Materiales</li>\n<li>Tecnológicos</li>\n<li>Organizativos</li>\n</ul>', '<ul>\n<li>Aprendizaje basado en proyectos</li>\n<li>Metodología activa</li>\n</ul>', '<ul>\n<li>Acceso: Puestos adaptados</li>\n<li>Metodológica: dividir tareas en pasos</li>\n<li>Currículo: objetivos personalizados</li>\n</ul>');
