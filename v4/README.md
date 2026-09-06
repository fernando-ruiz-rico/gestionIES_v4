# GestionIES v4

Aplicación fullstack para la gestión interna de centros educativos (IESSV). Reimplementación de `v3` con **backend PHP 5 + frontend Vue 3** (sin build step), comunicándose vía JSON.

## Tabla de contenido

- [Estructura del proyecto](#estructura-del-proyecto)
- [Tecnologías](#tecnologías)
- [Requisitos del servidor](#requisitos-del-servidor)
- [Instalación](#instalación)
- [Base de datos — cambios de esquema](#base-de-datos-cambios-de-esquema)
- [Estado actual del proyecto](#estado-actual-del-proyecto)
- [Hoja de ruta](#hoja-de-ruta)
- [Historial de cambios](#historial-de-cambios)
- [Registro de Decisiones Técnicas](#registro-de-decisiones-técnicas)
- [Metodología de desarrollo](#metodología-de-desarrollo)
- [Diferencias con v3](#diferencias-con-v3)
- [Notas importantes](#notas-importantes)

---

## Estructura del proyecto

```
v4/
├── backend/           # API PHP 5 (mysqli_*, PHP 5)
│   ├── config.php     # Configuración y funciones comunes (BD, sesión, roles, menús)
│   ├── create_table.sql
│   └── api/           # Endpoints de la API (namespace por módulo)
│       ├── auth.php               # Autenticación (login, logout, check)
│       ├── app.php                # Datos de la aplicación (menús, activaciones)
│       ├── departamentos/          # Fase 1
│       ├── profesores/           # Fase 1
│       ├── especialidades/       # Fase 1
│       ├── ciclos/               # Fase 1
│       ├── cursos/               # Fase 1
│       ├── grupos/               # Fase 1
│       ├── materias/             # Fase 1
│       ├── escenarios/           # Fase 1
│       ├── programaciones/        # Fase 2.1 (fiel a v3)
│       ├── programaciones_apartados/        # Fase 2.2
│       ├── programaciones_contenidos_defecto/# Fase 2.3
│       ├── programaciones_aula/               # Fase 2.4
│       ├── programaciones_seguimiento/        # Fase 2.5
│       ├── temas.php               # Fase 2.6
│       ├── temas_contenidos_defecto.php         # Fase 2.7
│       ├── pccf/                  # Fase 3.1
│       ├── pccf_apartados/        # Fase 3.2
│       ├── pccf_contenidos_defecto/# Fase 3.3
│       ├── resultados_aprendizaje.php  # Fase 4.1 (RA + criterios de evaluación)
│       ├── competencias_ciclos.php     # Fase 4.2
│       ├── cualificaciones_uc.php      # Fase 4.3 (cualificaciones + UC + asociaciones)
│       ├── seleccion.php               # Fase 5.1 (materias, profesores, insertar/borrar)
│       ├── actas.php                   # Fase 6.1
│       ├── historico.php               # Fase 7.1
│       ├── estadisticas.php            # Fase 7.2
│       ├── configuracion.php           # Fase 7.3
│   ├── excel.php                     # Desideratas (XLS de la selección, PHPExcel)
│   ├── pdf/                          # Scripts que generan PDFs (TCPDF/FPDI)
│   │   ├── plantilla.pdf             # Plantilla de la desiderata (FPDI)
│   │   ├── desiderata_horario.pdf    # Plantilla de preferencias (FPDI)
│   │   ├── pdf_acta.php                  # Fase 8 (PDF del acta, TCPDF)
│   │   ├── pdf_programaciones_seguimiento.php # Fase 8 (PDFs de seguimiento de aula, TCPDF)
│   │   ├── pdf_resultados_aprendizaje.php # Fase 8 (PDFs de RA/CE empresa, TCPDF)
│   │   ├── pdf_desiderata.php            # Fase 8 (PDF de la desiderata: ficha de profesor o por especialidad, TCPDF+FPDI)
│   │   ├── pdf_preferencias.php          # Fase 8 (PDF de preferencias de un profesor / departamento, TCPDF+FPDI)
│   │   ├── pdf_programaciones.php        # Fase 2.1 (PDF completo de la programación, TCPDF)
│   │   ├── pdf_programaciones_apartado.php # Fase 2.1 (PDF de un apartado, TCPDF)
│   │   └── pdf_unidades_programacion.php # Fase 2.1 (PDF de unidades/temas, TCPDF)
│   └── lib/                          # lib/php/tcpdf (TCPDF, desde v3) + lib/php/fpdi (FPDI, desde v3) + lib/php/phpexcel (PHPExcel, desde v3) + lib/programaciones_pdf.php
│
└── frontend/          # Aplicación Vue 3 (desde CDN, sin compilación)
    ├── index.html     # Punto de entrada (acceder directamente)
    ├── css/
    │   ├── app.css            # Estilos personalizados mínimos
    │   └── estilos_tiny.css   # Estilos para editores TinyMCE
    ├── lib/js/tinymce/      # TinyMCE 7.9.1 (copiado íntegro desde v3)
    └── js/
        ├── app.js                 # Aplicación principal Vue 3 (registro de componentes)
        ├── api/
        │   ├── auth.js            # API de autenticación
        │   ├── app.js             # API de datos de la aplicación
        │   └── ...                # Un cliente por módulo
        ├── components/
        │   ├── login-view.js      # Componente de login
        │   ├── app-layout.js      # Layout principal (mapeo de rutas)
        │   ├── sidebar.js         # Menú lateral
        │   └── header-bar.js      # Barra superior
        └── views/
            ├── home-view.js       # Página de inicio
            └── ...                # Una vista por módulo
```

---

## Tecnologías

### Backend
- **PHP 5** compatible con servidores antiguos (Apache ~2010)
- **MySQL** con `mysqli_*` y sentencias preparadas (`mysqli_prepare` / `mysqli_stmt_*`)
- **JSON** para la comunicación con el frontend (`json_encode(['success' => bool, 'data' => ..., 'message' => ...])`)
- Sesiones PHP para autenticación

### Frontend
- **Vue 3** desde CDN (sin build step, sin Node.js)
- **Bootstrap 5.3.8** para layouts responsive
- **Bootstrap Icons 1.13.1** para iconos (sin imágenes PNG/SVG)
- **SweetAlert2** para mensajes y confirmaciones
- **TinyMCE 7.9.1** para editores WYSIWYG (fiel a v3 en módulos 2.3–3.3)
- CSS personalizado mínimo sobre Bootstrap

---

## Requisitos del servidor

- PHP 5.x (compatible con versiones antiguas)
- MySQL / MariaDB
- Apache con módulo `mod_rewrite` (opcional)
- **No** requiere Node.js ni procesos de compilación

---

## Instalación

1. Subir la carpeta `v4` al servidor web.

2. Configurar la base de datos en `backend/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gestionies');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. Asegurarse de que la base de datos tiene las tablas reales de v3 (p. ej. `profesores`, `cursos`, `ciclos`, `especialidades`, `departamentos`, `grupos`, `materias`, `escenarios_desideratas`, etc.).

4. Acceder directamente a la carpeta frontend:
   ```
   http://tudominio.com/v4/frontend/
   ```

---

## Base de datos — cambios de esquema

SQL a ejecutar sobre la base de datos real de v3 (la del dump `gestionies.sql`)
para las opciones propias de v4 «Propuesta Pedagógica» y «Programaciones de
aula»:

```sql
-- «Propuesta Pedagógica» (antigua «Programaciones»): flag que indica si la
-- propuesta de una materia está terminada. Es lo que habilita importar la
-- programación de aula a partir de ella (opción «Programaciones de aula»,
-- botón «Importar propuesta»).
ALTER TABLE `materias`
  ADD `terminada_programacion` TINYINT(1) NOT NULL DEFAULT 0;

-- «Programaciones de aula»: copia, por profesor y grupo, de la propuesta
-- pedagógica de una materia (contenidos de apartados). Al ser una copia
-- independiente, editarla NO modifica `contenidos_programaciones` (la
-- propuesta pedagógica).
CREATE TABLE `contenidos_programaciones_aula` (
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
-- (CE) que vinculan RA (resultados_aprendizaje_aula.id) con temas
-- (temas_aula.id) para cada profesor y grupo.
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
-- propia de cada profesor y grupo. idRA referencia
-- resultados_aprendizaje_aula.id.
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
-- unidad (especular con `competencias_temas`), propia de cada profesor y
-- grupo. idCompetencia referencia el catálogo compartido
-- `competencias_ciclos`; idTema referencia temas_aula.id.
CREATE TABLE IF NOT EXISTS `competencias_temas_aula` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idMateria` int NOT NULL,
  `idCompetencia` int NOT NULL,
  `idTema` int NOT NULL,
  `idGrupo` int NOT NULL,
  `idProfesor` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
```

- El flag `materias.terminada_programacion` (0/1) se edita desde la opción
  «Propuesta Pedagógica» (interruptor «Propuesta pedagógica terminada», una
  vez elegida la materia); el endpoint es
  `backend/api/programaciones/actualizar_terminada.php`.
- `contenidos_programaciones_aula` se puebla con el botón «Importar
  propuesta» de la opción «Programaciones de aula», que copia las filas de
  `contenidos_programaciones` de la materia elegida para el
  (profesor, grupo) elegido; si ya existía una copia para esa combinación,
  se reemplaza.
- `temas_aula`, `resultados_aprendizaje_aula`, `criterios_temas_aula`,
  `criterios_evaluacion_aula` y `competencias_temas_aula` se pueblan también
  con ese mismo botón «Importar propuesta»: además de los contenidos de
  apartados, se copian las **unidades** (temas) y **todos los demás datos** que
  las componen —sus resultados de aprendizaje (RA), los criterios de
  evaluación (CE, tanto su texto como su vínculo RA↔tema) y sus
  competencias— creando una copia **completa e independiente** para el
  (profesor, grupo) elegido. Si ya existía copia, se reemplaza (mismos
  borrados + reinsertos dentro de la misma transacción; los vínculos RA↔tema
  y las competencias se re-vinculan por orden, pues los ids cambian).

---

## Estado actual del proyecto

> Tabla basada en los archivos realmente presentes en `backend/` y `frontend/`.

| Módulo | Backend | Frontend | Estado |
|--------|:-----:|:-----:|:------:|
| Autenticación (login / logout / check) | ✅ | ✅ | Completado |
| Layout / UI (sidebar, header, app-layout) | ✅ | ✅ | Completado |
| **Fase 1 – Módulos básicos** | | | |
| Departamentos | ✅ | ✅ | Completado |
| Profesores | ✅ | ✅ | Completado |
| Especialidades | ✅ | ✅ | Completado |
| Ciclos Formativos | ✅ | ✅ | Completado |
| Cursos | ✅ | ✅ | Completado |
| Grupos | ✅ | ✅ | Completado |
| Materias | ✅ | ✅ | Completado |
| Escenarios | ✅ | ✅ | Completado |
| **Fase 2 – Programaciones Didácticas** | | | |
| 2.1 Propuesta Pedagógica (fiel a v3) | ✅ | ✅ | Completado |
| 2.2 Apartados de programación | ✅ | ✅ | Completado |
| 2.3 Contenidos por defecto | ✅ | ✅ | Completado |
| 2.4 Programaciones de aula (propia de v4) | ✅ | ✅ | Completado |
| 2.5 Seguimiento de programaciones | ✅ | ✅ | Completado |
| 2.6 Temas (unidades de programación) | ✅ | ✅ | Completado |
| 2.7 Cont. defecto de temas | ✅ | ✅ | Completado |
| **Fase 3 – PCCF** | | | |
| 3.1 PCCF | ✅ | ✅ | Completado |
| 3.2 Apartados PCCF | ✅ | ✅ | Completado |
| 3.3 Cont. defecto PCCF | ✅ | ✅ | Completado |
| **Fase 4 – Resultados y Competencias** | | | |
| 4.1 Resultados de Aprendizaje | ✅ | ✅ | Completado |
| 4.2 Competencias por Ciclo | ✅ | ✅ | Completado |
| 4.3 Cualificaciones y UC | ✅ | ✅ | Completado |
| **Fase 5 – Selección** | | | |
| 5.1 Selección de Destinos | ✅ | ✅ | Completado |
| **Fase 6 – Actas** | | | |
| 6.1 Actas de Evaluación | ✅ | ✅ | Completado |
| **Fase 7 – Utilidades y Reportes** | | | |
| 7.1 Histórico | ✅ | ✅ | Completado |
| 7.2 Estadísticas | ✅ | ✅ | Completado |
| 7.3 Configuración | ✅ | ✅ | Completado |
| 7.4 Exportación a Excel | ✅ | ✅ | Completado |
| 7.5 Ayuda | ❌ | ❌ | Pendiente (página estática de ayuda) |
| **Fase 8 – PDFs** | ✅ | N/A | Completado (TCPDF: actas, selección, RA/CE empresa y seguimiento de aula) |
| **Fase 9 – Características Avanzadas** | | | |
| Edición de temas con accordion RA/CE | ✅ | ✅ | Completado (Fase 2.6) |
| Modales reutilizables globales | — | — | Retirados en v4.4.1 (ninguna vista los usaba; los modales de cada módulo son inline en sus vistas) |
| Resto de características avanzadas | ⬜ | ⬜ | Pendiente |

✅ = Implementado | ❌ = Pendiente

---

## Hoja de ruta

Hoja de ruta para completar en v4 la misma funcionalidad que v3. El **estado** de cada fase refleja lo realmente implementado (ver [Estado actual del proyecto](#estado-actual-del-proyecto)).

### Fase 1: Módulos básicos de mantenimiento (Completado)

Módulos de base del sistema:

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| Departamentos | `backend/api/departamentos/` | `frontend/js/views/departamentos-view.js` | `departamentos.php`, `ajax/departamentos/` |
| Profesores | `backend/api/profesores/` | `frontend/js/views/profesores-view.js` | `profesores.php`, `ajax/profesores/` |
| Especialidades | `backend/api/especialidades/` | `frontend/js/views/especialidades-view.js` | `especialidades.php`, `ajax/especialidades/` |
| Ciclos Formativos | `backend/api/ciclos/` | `frontend/js/views/ciclos-view.js` | `ciclos.php`, `ajax/ciclos/` |
| Cursos | `backend/api/cursos/` | `frontend/js/views/cursos-view.js` | `cursos.php`, `ajax/cursos/` |
| Grupos | `backend/api/grupos/` | `frontend/js/views/grupos-view.js` | `grupos.php`, `ajax/grupos/` |
| Materias | `backend/api/materias/` | `frontend/js/views/materias-view.js` | `materias.php`, `ajax/materias/` |
| Escenarios | `backend/api/escenarios/` | `frontend/js/views/escenarios-view.js` | `escenarios.php`, `ajax/escenarios/` |

### Fase 2: Programaciones Didácticas (Completado)

> **Modelo fiel a v3**: en v3 **no existe** la tabla `programaciones`. La programación vive en `apartados_programaciones` + `contenidos_programaciones` asociados a cada materia (flag `materias.tiene_programacion`); el curso se resuelve con `materias.idCurso → cursos`. (Única excepción: la 2.4 «Programaciones de aula», opción propia de v4 sin equivalente en v3.)

| Módulo | Backend | Frontend | Referencia v3 | Estado |
|--------|---------|----------|---------------|:------:|
| 2.1 Propuesta Pedagógica | `backend/api/programaciones/` | `programaciones-view.js` + `api/programaciones.js` | `programaciones.php`, `ajax/programaciones/`, `modales/importar_programacion.php` | Completado (+ flag «terminada» propio de v4) |
| 2.2 Apartados | `backend/api/programaciones_apartados/` | `programaciones-apartados-view.js` | `programaciones_apartados.php`, `ajax/programaciones_apartados/` | Completado |
| 2.3 Cont. defecto | `backend/api/programaciones_contenidos_defecto/` | `programaciones-contenidos-defecto-view.js` | `programaciones_contenidos_defecto.php`, `ajax/programaciones_contenidos_defecto/` | Completado |
| 2.4 Programaciones de aula | `backend/api/programaciones_aula/` | `programaciones-aula-view.js` + `api/programaciones-aula.js` | — (opción propia de v4, sin equivalente en v3) | Completado |
| 2.5 Seguimiento | `backend/api/programaciones_seguimiento/` | `programaciones-seguimiento-view.js` | `programaciones_seguimiento.php`, `ajax/programaciones_seguimiento/` | Completado |
| 2.6 Temas | `backend/api/temas.php` | `temas-view.js` + `api/temas.js` | `temas.php`, `editar_tema.php`, `ajax/temas/` | Completado |
| 2.7 Cont. defecto de temas | `backend/api/temas_contenidos_defecto.php` | `temas-contenidos-defecto-view.js` | `temas_contenidos_defecto.php`, `ajax/temas_contenidos_defecto/` | Completado |

### Fase 3: PCCF (Completado)

> **Modelo fiel a v3**: contenido almacenado en `contenidos_pccf` / `contenidos_pccf_apartados` (una fila por ciclo + apartado).

| Módulo | Backend | Frontend | Referencia v3 | Estado |
|--------|---------|----------|---------------|:------:|
| 3.1 PCCF | `backend/api/pccf/{listar, listar_ciclos, guardar, generar}.php` | `pccf-view.js` + `api/pccf.js` | `pccf.php`, `ajax/pccf/` | Completado |
| 3.2 Apartados PCCF | `backend/api/pccf_apartados/{listar, obtener, guardar, borrar, ordenar}.php` | `pccf-apartados-view.js` | `pccf_apartados.php`, `ajax/pccf_apartados/` | Completado |
| 3.3 Cont. defecto PCCF | `backend/api/pccf_contenidos_defecto/{cargar, guardar}.php` | `pccf-contenidos-defecto-view.js` | `pccf_contenidos_defecto.php`, `ajax/pccf_contenidos_defecto/` | Completado |

### Fase 4: Resultados de Aprendizaje y Competencias (Completado)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 4.1 Resultados de Aprendizaje | `backend/api/resultados_aprendizaje.php` | `resultados_aprendizaje-view.js` + `api/resultados_aprendizaje.js` | `resultados_aprendizaje.php`, `ajax/resultados_aprendizaje/` |
| 4.2 Competencias por Ciclo | `backend/api/competencias_ciclos.php` | `competencias_ciclos-view.js` + `api/competencias_ciclos.js` | `competencias_ciclos.php`, `ajax/competencias_ciclos/` |
| 4.3 Cualificaciones y UC | `backend/api/cualificaciones_uc.php` | `cualificaciones_uc-view.js` + `api/cualificaciones_uc.js` | `cualificaciones_uc.php`, `ajax/cualificaciones_uc/` |

### Fase 5: Selección y Asignaciones (Completado)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 5.1 Selección de Destinos | `backend/api/seleccion.php` | `seleccion-view.js` + `api/seleccion.js` | `seleccion.php`, `ajax/seleccion/` |

### Fase 6: Actas y Evaluación (Completado)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 6.1 Actas de Evaluación | `backend/api/actas/` (`listar`/`obtener`/`guardar`/`nueva`) | `actas-view.js` + `api/actas.js` | `actas.php`, `ajax/actas/` |

### Fase 7: Utilidades y Reportes (Completado)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 7.1 Histórico | `backend/api/historico.php` | `historico-view.js` + `api/historico.js` | `historico.php` |
| 7.2 Estadísticas | `backend/api/estadisticas.php` | `estadisticas-view.js` + `api/historico.js` (EstadisticasAPI) | `estadisticas.php` |
| 7.3 Configuración | `backend/api/configuracion.php` | `configuracion-view.js` + `api/configuracion.js` | `configuracion.php` |
| 7.4 Exportación a Excel | `backend/excel.php` | botón «Excel» de `seleccion-view.js` | `excel.php` |
| 7.5 Ayuda | — | — | Pendiente (página estática de ayuda) |

### Fase 8: Generación de PDFs (Completado con matices)

Endpoints que generan PDF con TCPDF (compatible PHP 5, copiado en `backend/lib/php/tcpdf/`):

- `backend/pdf/pdf_acta.php` — PDF del acta de departamento (`?idActa=X`), fiel a `v3/pdf_acta.php`. ✅ Verificado en vivo (≈27 KB, el de v3 hace 27 KB).
- `backend/pdf/pdf_desiderata.php` — **Ficha de la desiderata** de un profesor (`?idProfesor=X&idEscenario=Y`, solo el propio, o `?selEsp=<esp|Todos>&idDepartamento=X&idEscenario=Y` para jefe/admin: todos los profesores de la especialidad), fiel a `v3/pdf_desiderata.php` (TCPDF+FPDI sobre la plantilla `backend/pdf/plantilla.pdf`). ✅ Verificado en vivo (200 `application/pdf`, cabecera `%PDF`).
- `backend/pdf/pdf_preferencias.php` — **Preferencias** de un profesor (`?idProfesor=X`, solo el propio) o de todo un departamento/especialidad (`?selEsp=<esp|Todos>&idDepartamento=X` para jefe/admin), fiel a `v3/pdf_preferencias.php` (TCPDF+FPDI sobre la plantilla `backend/pdf/desiderata_horario.pdf`); sin permiso: «Acceso no permitido». ✅ Verificado en vivo.
- `backend/api/pccf/generar.php` — PDF del PCCF (`modo=completo` / `modo=apartado`). ⚠️ Con MySQL 8 (`ONLY_FULL_GROUP_BY`) el modo `completo` genera siempre un PDF de una página con el error SQL 3087 (ver bug **B-3**); en `modo=apartado` funcionan los tipos 0/7/12 y falla el tipo 4.
- `backend/pdf/pdf_programaciones.php` — **PDF completo** de la programación de una materia (`?idMateria=X`), Fase 2.1, fiel a `v3/pdf_programaciones.php` (portada que **siempre dice «Propuesta pedagógica»** —antes, con `idCiclo`, ponía «Programación didáctica»— + índice con TOC + apartados con `Bookmark`, FE en página propia, temas en su página). ✅ Verificado en vivo.
- `backend/pdf/pdf_programaciones_apartado.php` — **PDF de un apartado** (`?idMateria=X&idApartado=Y`), el apartado pedido + sus subapartados hasta el siguiente principal, fiel a v3. ✅ Verificado en vivo.
- `backend/pdf/pdf_unidades_programacion.php` — **PDF de unidades/temas** (`?idMateria=X`, una página por tema), fiel a `v3/pdf_unidades_programacion.php`. El «PDF de Apartado» de la vista enruta aquí si el apartado es de temas (`tipo = 13`), igual que v3. ✅ Verificado en vivo.
- `backend/pdf/pdf_programaciones_seguimiento.php` — **PDFs de seguimiento de aula** (`?departamento=X&curso=Y&evaluacion=Z&categoria=FP|ESO/BACH`), Fase 2.5, fiel a `v3/pdf_programaciones_seguimiento.php`: portada (curso + evaluación + departamento) y las 5 secciones (1. temporalización, 2. resultados académicos con % de aprobados, 3. inclusión del alumnado —con «No hay datos disponibles» si no hay inclusiones—, 4. valoración de las horas de atención a pendientes [datos comunes del departamento], 5. actividades extraescolares programadas para la evaluación siguiente). Los dos botones de la vista de seguimiento (`Ciclos Formativos` → `categoria=FP`; `ESO/BACH` → el resto) abren este endpoint con el curso actual, la evaluación elegida y el **departamento del usuario** (jefe/profesor: el suyo; **admin real: el que elige en el desplegable de la vista**, equivalente al `seleccion_departamento` de la cabecera de v3 — sin departamento el botón queda desactivado). ✅ Verificado en vivo.
- `backend/pdf/pdf_programaciones_aula.php` — **PDF completo de la copia de aula** (`?idMateria=X&idGrupo=G&idProfesor=P`): espejo de `pdf_programaciones.php` sobre las tablas de aula (portada que **siempre dice «Programación de aula»** e **incluye el grupo** de la copia —nombre de `grupos.nombre`— junto al curso y al profesor de la copia + apartados editables/predefinidos + índice). Lo abre el botón «PDF Completo» de «Programaciones de aula».
- `backend/pdf/pdf_programaciones_apartado_aula.php` — **PDF de un apartado de la copia de aula** (`?idMateria=X&idApartado=Y&idGrupo=G&idProfesor=P`): espejo de `pdf_programaciones_apartado.php` sobre las tablas de aula. Lo abre el botón «PDF de Apartado» (cuando el apartado no es de temas).
- `backend/pdf/pdf_unidades_programacion_aula.php` — **PDF de unidades de la copia de aula** (`?idMateria=X&idGrupo=G&idProfesor=P`, una página por tema): espejo de `pdf_unidades_programacion.php` sobre `temas_aula`/`resultados_aprendizaje_aula`/`criterios_*_aula`/`competencias_temas_aula`. Lo abren el botón «PDF de Unidades» y el «PDF de Apartado» cuando el apartado es de temas (`tipo = 13`).

> Los PDFs se abren desde sus vistas: los de «Propuesta Pedagógica» son los 3 de más arriba; los de «Resultados de aprendizaje», el de la empresa; los de «Seguimiento», el de antes de esta línea. **La opción «Programaciones de aula» (Fase 2.4, propia de v4) abre sus propios PDFs** (los 3 de la copia de aula, `*_aula.php` de arriba) con el (grupo, profesor) de la copia —mismos botones que «Propuesta Pedagógica»: «PDF de Unidades», «PDF de Apartado» y «PDF Completo» (ver «Historial de cambios»).

### Fase 9: Características Avanzadas (Parcial)

- ✅ Edición de temas con accordion RA/CE (entregado en la Fase 2.6)
- ➖ Modales reutilizables: `ModalConfirmacion`/`ModalMensaje` (v4.4.0) se retiraron en v4.4.1 por no usarse en ninguna vista; los modales específicos de cada módulo se definen inline en sus vistas (los genéricos se resuelven con SweetAlert2).
- ✅ Sistema de activaciones (ON/OFF de `programaciones` y `desideratas`): lo cubre la Fase 7.3 en `configuracion.php` (el frontend envía `evaluacionRA`/`seleccion`; se mapea a las filas `programaciones`/`desideratas`, mismo modelo que v3)
- ⬜ Copia de seguridad y restauración
- ⬜ Importación/exportación de datos (parcial: la exportación a CSV en `excel-view.js` cubre la exportación; la importación queda pendiente)

---

## Historial de cambios

> Registro cronológico (más reciente primero) de las entregas por versión.

### «Unidades» — botón «Guardar» en la barra del título del formulario y aviso de cambios sin guardar al cerrar la edición
- 🎯 **Pedido**: en el formulario de edición de tema/unidad (tanto «Propuesta Pedagógica» como «Programaciones de aula»), el botón de **Guardar** debe aparecer en la barra del título del cuadro, a la derecha del botón de **Cerrar edición**; y al pulsar «Cerrar edición» con cambios sin guardar, debe avisar de la situación y preguntar si se quiere guardar.
- 🔧 **Frontend** (`temas-view.js` + `temas-aula-view.js`, espejo): el botón «Guardar» (icono de guardado, desactivado mientras guarda, texto «Guardando…») se **mueve** —no se duplica— desde el pie del formulario a la `card-header`, a la derecha de «Cerrar edición».
- 🔧 **Detección de cambios sin guardar**: al abrir una unidad (`editarTema`) se toma una **fotografía de referencia** del estado **después** de `inicializarEditores()` (TinyMCE puede normalizar el HTML al arrancar). `_fotografiaEstado()` replica la lógica exacta del guardado (`leerEditores`) **sin mutar estado**: contenido real de cada editor (o del textarea si no hay instancia) con la misma regla de los campos por defecto (`mostradorDe`/`temaOriginalDe` + flags `*Defecto`) más las selecciones `selCE`/`selCom`, serializado a JSON. `hayCambiosSinGuardar()` compara esa fotografía con `estadoOriginal`.
- 🔧 **Cerrar edición con aviso**: `cerrarEdicion()` pasa a ser asíncrona: sin cambios, cierra directamente; con cambios, muestra un diálogo `Swal` («Cambios sin guardar») con dos opciones: **«Guardar y cerrar»** (ejecuta `guardar()`, que ahora **devuelve un booleano de éxito**, y solo cierra si la guarda fue correcta —si falla, el formulario queda abierto para reintentar) o **«Cerrar sin guardar»** (cierra descartando los cambios). X / Esc descarta el diálogo sin decisión y la edición **queda abierta** (no se pierde nada). El cierre efectivo se factoriza en `_hacerCerrarEdicion()`.
- 🔧 **Borrado de la unidad editada** (`borrarTema`): si la unidad eliminada era la que estaba en edición, cierra directamente por `_hacerCerrarEdicion()` —sin aviso, porque esa fila ya no existe—. `cambiarMateria` también reinicia `estadoOriginal`.
- 📄 **Despliegue**: `index.html` — `temas-view.js?v=4`, `temas-aula-view.js?v=5`; `sw.js` — `NIVEL → v4-pwa-6` (re-caché PWA al subir NIVEL).
- ✅ **Verificado**: `node --check` limpio en las dos vistas; simulación en Node del flujo fotografía/cambios/cierre sobre el archivo real (abrir sin cambios → cierra sin diálogo; cambio en un editor, en un campo simple, en un CE o en una competencia → diálogo; «Guardar y cerrar» cierra si la guarda OK y **queda abierto** si la guarda falla; «Cerrar sin guardar» cierra; X/Esc mantiene abierto; borrar la unidad editada cierra sin aviso).
- ⚠️ Sin cambios de backend ni de esquema (solo frontend).

### «Unidades» — porcentajes de evaluación de los RA: ahora se calculan a partir del peso de cada unidad (antes: solo por nº de criterios de evaluación)
- 🎯 **Cálculo por peso de unidad** (lo pedido: «los porcentajes que se aplican a cada RA para la evaluación se deben calcular a partir del % que se ha puesto en la unidad como peso en la evaluación anual… al final debe quedar en cada RA el porcentaje total final de la nota de cada RA de toda la asignatura teniendo en cuenta en cuantos temas influye y cuantos criterios de evaluación»). El % de evaluación de cada RA (`porcentaje_evaluacion`) ya no se calcula solo en proporción al nº total de criterios de evaluación (CE) de cada RA sobre toda la asignatura (v3 `calcularPorcentajesRA`). Ahora: el **peso de cada unidad** (`peso_evaluacion`) se reparte entre los **RA que intervienen en ella**, **en proporción a los CE de cada RA en esa unidad** (`criterios_temas` / `criterios_temas_aula`); el **% final de cada RA es la suma** de su parte en cada unidad en la que interviene. Un RA que influye en más unidades, y con más CE en cada una, se lleva más de la nota de la asignatura.
  - Fórmula: `%_RA_en_tema = peso_tema × (CEs_del_RA_en_tema / CEs_totales_del_tema)`; `%_RA_final = Σ_temas %_RA_en_tema` (redondeo mayor a entero). Un RA presente en 2 temas — p. ej. 8 CE en uno (peso 25 %) y 2 CE en otro (peso 25 %) — se lleva `25 × 8/CEs_totales_tema1 + 25 × 2/CEs_totales_tema2` según los criterios de evaluación de cada tema.
- 🔧 **Backend** (`recalcular_porcentajes.php`, `temas` + `temas_aula`, espejo): se rehace con la fórmula por peso de unidad. Lee el peso de cada unidad (`temas` / `temas_aula`), los CE por (RA, unidad) (`criterios_temas` / `criterios_temas_aula`, agrupados por `idRA`+`idTema`), y para cada RA suma `peso × (CEs_RA / CEs_totales)` en cada unidad; redondea al entero y actualiza `porcentaje_evaluacion`. PHP 5 (arrays anidados + `isset`, sin `??`), sentencias preparadas por `Db`.
- 🔒 **El % es solo lectura** (lo pedido: «el cálculo manda»): `actualizar_ra.php` (`temas` + `temas_aula`) ya no actualiza el % — solo el flag `es_clave` («RA/CE clave»). En `temas-view.js` / `temas-aula-view.js` la etiqueta de % del RA pasa de botón editable («Pulsa para cambiar») a **etiqueta de solo lectura** (`<span>` con badge); se añade un botón de «RA/CE clave» (estrella) que abre el modal, que ahora solo cambia `es_clave` (el % se muestra como información, no editable). El **único** que fija el % es el botón «**Calcular y actualizar porcentajes**» (guarda la unidad y recalcula, como antes).
- 📄 **Despliegue**: `index.html` — `temas-view.js?v=3`, `temas-aula-view.js?v=4`, `api/temas.js?v=2`, `api/temas-aula.js?v=2`; `sw.js` — `NIVEL → v4-pwa-4` (re-caché PWA al subir NIVEL).
- ✅ **Verificado** (Laragon, `testadmin`): materia 5 (8 unidades, pesos 3/18/21/6/22/21/6/3) → RA `12/34/15/18/10/10/0` (suma 99, por redondeo); copia de aula (materia 54, grupo 7, profesor 7; 4 unidades 10/30/30/30) → RA `24/17/19/22/19` (suma 101); ambos **coinciden con el cálculo a mano**; `actualizar_ra` solo toca `es_clave` (el % queda intacto, verificado en BD); el acordeón `accordion_ra_ce` devuelve los % calculados y la `Suma` (99/101). `php -l` / `node --check` limpios.
- ⚠️ **Nota**: la suma de los % de los RA puede desviarse unos puntos de 100 (redondeo mayor por RA); el indicador «Suma» de la vista sigue en rojo cuando no es exactamente 100 (informativo, no un error).
- 🐞 **Corrección de «Guardar» (solo cambia CE → «Los datos generales del tema no se guardaron correctamente»)**: `guardar.php` (`temas` + `temas_aula`) usaba `errorTema = (afectadas == 0)` sobre el `UPDATE temas SET …`; si solo cambian los criterios de evaluación (los datos generales quedan iguales) el `UPDATE` no modifica filas (`afectadas == 0`) y se señalaba error aunque el guardado fue correcto. Ahora `errorTema` = «el tema no existe» (`temas`: `fetchOne SELECT 1 WHERE id`; `temas_aula`: reutiliza la fila ya validada al inicio, que devuelve 404 si no existe). Verificado: guardar sin cambios ni con un cambio real de CE → `errorTema:false` y la CE persiste.

### Menú lateral «Salir» — cerraba la sesión en vez de volver al inicio (corregido)
- 🐞 **Causa**: el ítem «Salir» del menú lateral (id 11, `link => "logout"`, en `getMenus` de `config.php`) se navegaba como un link cualquiera: `sidebar.js` `navigate` emitía `navigate` con `"logout.php"`, que `app-layout.js` mapeaba a `vistaMap['logout.php']` → no existe → caía a `home-view` (volvía al inicio) en vez de cerrar sesión.
- 🔧 **Fix** (frontend, espejo en el flujo de eventos): `sidebar.js` `navigate` detecta `link === 'logout'` y emite el evento `logout` (no `navigate`); `app-layout.js` añade `@logout="handleLogout"` al `<sidebar>` (el mismo handler que ya cerraba sesión desde el botón rojo de la barra superior). Con esto el «Salir» del menú lateral cierra la sesión igual que el botón rojo de arriba a la derecha (con su confirmación «¿Cerrar sesión?»).
- 📄 **Despliegue**: `index.html` — `sidebar.js?v=1`, `app-layout.js?v=4`; `sw.js` — `NIVEL → v4-pwa-5` (re-caché PWA).
- ⚠️ Sin cambios de esquema de BD ni de backend (los `menus` de `config.php` no cambian; el ítem «Salir» ya estaba ahí, solo no lo navegaba como cierre de sesión).

### Portada de los «PDF Completo» — la de «Propuesta Pedagógica» decía «Programación didáctica» y la de aula no ponía el grupo (corregido)
- 🔧 **Portada del «PDF Completo» de «Propuesta Pedagógica»** (`backend/pdf/pdf_programaciones.php`): el título de la portada era `$idCiclo ? 'Programación didáctica' : 'Propuesta pedagógica'` — para las materias de **ciclo** (`idCiclo > 0`) ponía «Programación didáctica». Ahora **siempre** pone «**Propuesta pedagógica**» (que es como se llama la opción que genera este PDF).
- 🔧 **Portada del «PDF Completo» de «Programaciones de aula»** (`backend/pdf/pdf_programaciones_aula.php`): el título era el mismo condicional de arriba —para ciclo, «Programación didáctica»—. Ahora siempre pone «**Programación de aula**» y la portada **añade el grupo** de la copia: una línea «Grupo: …» con `grupos.nombre` (nombre del grupo, la misma etiqueta del desplegable), junto al curso académico y al profesor de la copia. El nombre se lee de la tabla `grupos` con una consulta preparada por `Db`.
- ✅ **Verificado**: `php -l` limpio en los dos ficheros; el resto de la portada (materia, curso, año académico, departamento, profesores del ciclo) no cambia.
- ⚠️ Sin cambios de esquema de BD ni de frontend (los dos «PDF Completo» siguen abriendo los mismos endpoints con los mismos parámetros).

### «Unidades» — los apartados salían vacíos en el editor (corregido: el editor muestra lo que se imprime)
- 🐞 **Causa raíz**: en el editor de unidades (tanto «Propuesta Pedagógica» como «Programaciones de aula») los 4 apartados con checkbox «Dejar valores por defecto» (Contexto, Recursos, Metodología, Adaptaciones) mostraban **solo el contenido propio** de la unidad (`temas`/`temas_aula`), que en la mayoría de unidades está **vacío**: su texto efectivo lo aporta el **catálogo compartido** del departamento (`contenidos_defecto_temas`), que rige cuando el flag `*_defecto = 1` y no hay propio — la misma regla que ya cumplía el **PDF** (`pgGenerarContenidoTemas`). La UI no conocía el catálogo (el endpoint `temas_contenidos_defecto/cargar.php` de la Fase 2.7 es solo admin/jefe — al profesor le da 403), de ahí el «la mayoría salen vacíos» pese a que el PDF salía bien. (v3 tenía el mismo comportamiento: campo crudo + checkbox, sin prellenado.)
- 🔧 **Backend** (`temas/obtener.php` + `temas_aula/obtener.php`, espejo): el JSON de «obtener tema» añade `contenidosDefecto` — los 4 campos compartidos del departamento de la materia (`LEFT JOIN contenidos_defecto_temas ON cd.idDepartamento = m.idDepartamento`). PHP 5 (ternario, sin `??`), sentencias preparadas por `Db`; cambio aditivo (las sesiones existentes siguen funcionando). Se resuelve en `obtener` — y no en `cargar.php` (2.7) — porque el editor lo usa un **profesor**.
- 🔧 **Frontend** (`temas-view.js` + `temas-aula-view.js`, espejo): al abrir una unidad, cada apartado defecto muestra **lo que se imprime**: flag 1 + hay compartido → el contenido del catálogo; en su caso, el propio (misma regla que el PDF, `contenidoMostrado`). Se añade una nota informativa azul cuando el apartado muestra contenido compartido. Al **guardar**: (a) sin cambios → la unidad **conserva** su contenido propio y su flag (no se materializa copia del compartido); (b) con cambios → la unidad queda con **contenido propio** y el checkbox se **desmarca** (en el handler de `change` se desmarca en vivo); (c) desmarcar el checkbox sin escribir → **fiel a v3** (prevalece el propio: si está vacío, el apartado queda omitido en el PDF).
- 📄 **Despliegue**: `index.html` — `temas-view.js?v=2`, `temas-aula-view.js?v=3`; `sw.js` — `NIVEL → v4-pwa-3` (re-caché PWA al subir NIVEL).
- ✅ **Verificado** (Laragon): `php -l` / `node --check` limpios; en vivo `temas_aula/obtener.php?idTema=17` devuelve `contenidosDefecto` (613 chars de recursos, depto 1); unidades 17–27: los 9 apartados salen **rellenos** en el editor; la unidad 16 (sin datos ni flags) sigue mostrando solo sus propios (legítimo: no tiene nada); simulación del modelo (21 aserciones display/guardar) OK; los **PDFs no cambian** (ya imprimían el compartido).
- ⚠️ Solo `obtener` + vistas; sin cambios de esquema de BD.

### «Importar propuesta» — «Error de conexión con el servidor» (corregido)
- 🐞 **Causa raíz**: en `programaciones_aula/importar.php`, los `INSERT … SELECT` de los **resultados de aprendizaje** y de las **unidades** usaban **4 huecos `?`** en la lista `SELECT` (`SELECT ?, ?, ?, ?, …`) con una lista de **3 parámetros** (`$idMateria, $idGrupo, $idProfesor`) → `ArgumentCountError` en PHP 8 (500 en el endpoint), que la SPA traducía a «Error de conexión con el servidor».
- 🔧 **Fix**: en ambos `INSERT … SELECT`, la `idMateria` de la copia se deriva de la fila origen (`SELECT idMateria, ?, ?, … FROM … WHERE idMateria = ?`) — los parámetros vuelven a ser 3 y encajan.
- ✅ **Verificado en vivo**: «Importar propuesta» OK de nuevo — copia completa e **idéntica al origen** (12 unidades, 12 RA, criterios y competencias revinculados por `orden`, incluidos los 4 flags `*_defecto`; byte-exacto). `php -l` limpio.
- ⚠️ Sin cambios de esquema; la copia ya era completa (los 18 campos de `temas`, flags incluidos).

### «Propuesta Pedagógica» + «Programaciones de aula» — opciones propias de v4 (renombrado, flag «terminada» e importar de la propuesta a la programación de aula)
- **Menú**: el ítem «Programaciones» pasa a llamarse **«Propuesta Pedagógica»** (misma ruta `programaciones.php`, misma vista; su botón «Importar» sigue siendo el de v3: modal materia origen→destino, solo admin).
- **Flag «terminada»** (`materias.terminada_programacion`, `TINYINT(1)`): en «Propuesta Pedagógica», al elegir una materia aparece el interruptor «Propuesta pedagógica terminada» (endpoint `programaciones/actualizar_terminada.php`); `cargar_materias` devuelve el flag de cada materia.
- **«Programaciones de aula» (2.4, propia de v4)**: **sustituye** a la antigua 2.4 (texto de introducción por tema + botones PDF pendientes). Flujo: **materia** (igual que en la propuesta pedagógica: las que imparte el profesor en el curso actual con `tiene_programacion = 1`, escenario actual) → **grupo** (de los que imparte esa materia; y **profesor**, en caso de jefe/admin) → **apartado** (mismo catálogo que la propuesta). El botón **«Importar propuesta»**, solo activo con la propuesta marcada como **terminada**, hace una copia, para el (profesor, grupo) elegidos, de `contenidos_programaciones` de la materia en la nueva tabla `contenidos_programaciones_aula` (idMateria + idApartado + idGrupo + idProfesor) y **la muestra en el editor** (TinyMCE, misma configuración que la propuesta); a partir de ahí se modifica igual que la propuesta pedagógica y **la propuesta no se toca** (copia independiente; si ya existía copia para esa combinación, se reemplaza). Si la propuesta no está terminada, el botón queda desactivado y el backend lo impone (400); el backend además verifica que el profesor imparta la materia en el grupo (escenario actual) y, para un profesor, fuerza el propio id (no vale el `idProfesor` del body).
- **SQL** (ver «Base de datos — cambios de esquema»): `ALTER TABLE materias ADD terminada_programacion` + 6 tablas de la copia de aula (`contenidos_programaciones_aula`, `temas_aula`, `resultados_aprendizaje_aula`, `criterios_temas_aula`, `criterios_evaluacion_aula` y `competencias_temas_aula`).
- **Barra de botones + unidades + PDF (igual que «Propuesta Pedagógica»)**: la vista «Programaciones de aula» muestra la misma barra que «Propuesta Pedagógica» —«Cont. defecto Unidades» (admin; catálogo compartido `temas_contenidos_defecto.php`), **«Unidades»** (navega a `temas_aula.php`, la gestión de unidades de la copia, espejo de `temas-view` sobre `temas_aula`), **«PDF de Unidades»**, **«PDF de Apartado»** (enruta al PDF de unidades si el apartado es de temas, `tipo = 13`) y **«PDF Completo»**—; los tres PDFs abren los endpoints `*_aula.php` con el (grupo, profesor) de la copia. **No** se añade el botón «Importar» origen→destino de la propuesta (aquí el importar es solo «Importar propuesta»).
- **Retirados**: `programaciones_aula/contenido.php` y `programaciones_aula/temas.php` (texto de introducción por tema, sin uso ya) y los dos botones PDF «Pendiente (Fase 8)» de la antigua vista.
- **Compartido**: la lógica de apartados/categoría (`pcCmp_cargarApartados`/`pcCmp_categoriaMateria`) pasa a `lib/programaciones_compartidas.php`, ahora compartida por `programaciones/cargar_apartados.php` y `programaciones_aula/apartados.php`; las funciones de listado `pcCmp_listarMaterias`/`pcCmp_listarGrupos`/`pcCmp_listarProfesores` viven en la misma lib y las reutilizan los dos módulos (el de aula, con el flag `terminada` en `pcCmp_listarMaterias`, y el de seguimiento).
- **Verificado en vivo** (Laragon, `testadmin`/`testprofe`): menú renombrado; flag 0/1; compuerta del importar (400 sin «terminada»); import real (16/16 apartados copiados para un profesor+grupo); lectura/guardado con `sin_cambios`; profesor forzado a sí mismo; `php -l` y `node --check` limpios.

### Perfil — «la opción no funciona adecuadamente» (corregido, fiel a v3): propio perfil + preferencias de horario
- 🐞 **Causa raíz** (tres cosas a la vez): (1) el menú «Perfil» del sidebar emite `perfil.php` y en la `vistaMap` de `app-layout.js` **no había entrada**, de modo que **silenciosamente caía en la home** (sin vista ni error); (2) el acceso rápido «Perfil» de la home abría `configuracion.php` (una vista **solo de admin**); (3) el formulario de profesor compartido (modal del módulo «Profesores») cargaba las preferencias horarias **desde un stub** (siempre inicializaba vacías), con **horas duras** (8:00, 9:00, …) en vez de las reales de la tabla `horas` y un código de celda de **7 caracteres** (`L_8_00`): guardar **borraba/corrompía** las filas reales de `preferencias_horario` (el backend parsea el código de 6 caracteres de v3, p. ej. `L07_55`).
- 🔧 **Backend — nuevo endpoint `backend/api/profesores/preferencias.php`** (GET, fiel a v3): `?idProfesor=X` o el propio (de sesión); permisos **admin o el propio** (403 el resto); devuelve las horas de la tabla `horas` separadas en `horasManana`/`horasTarde` (M/T, por hora) y las casillas guardadas como cadenas de v3: `rojas`/`amarillas` de códigos de 6 caracteres (día + hora, `:` → `_`, p. ej. `L07_55`) — el formato exacto que parsea `insertarPreferencias` de `guardar.php` (`substr($pref, 0, 6)`). PHP 5 (sin `??`/`...`), `cabeceraJson()` + `checkSession()`.
- 🧩 **Frontend — nueva vista `js/views/perfil-view.js`** (estilo v4, fiel a `cargarPerfil` de v3): el formulario precarga los datos del usuario de la sesión (`ProfesoresAPI.obtener(usuario.idUsuario)`) con la **clave siempre vacía** (v3: si no se rellena, `guardar.php` conserva la actual), la **abreviatura en solo-lectura** (v3 `editarAbreviatura = false`: solo el admin la cambia), el desplegable de especialidades **solo las de su departamento** (etiqueta `descripcion`) y la rejilla de preferencias con **horas reales** (no duras) y las casillas guardadas ya marcadas; al clic se alterna **sin color → roja (máx. 3) → amarilla → sin color**, igual que v3, y «Guardar» envía a `guardar.php` con `id = usuario.idUsuario` + `idDepartamento` y las cadenas `prefRojas`/`prefAmarillas`. Si por alguna causa llega un admin (no le sale el menú), ve un aviso claro en vez del formulario.
- 🧩 **Correcciones en `js/views/profesores-view.js`** (el mismo formulario; v3 comparte un solo modal para ambos usos, así que la corrección también cubre el modal del admin): `cargarPreferenciasHorarias(id)` **carga de verdad** las preferencias guardadas (`ProfesoresAPI.preferencias(id)`) en vez del stub que siempre las inicializaba vacías (guardar desde el modal del admin **borraba las preferencias reales** del profesor); las **horas de la rejilla salen de la tabla `horas`** (se cargan en `mounted`, así salen también en «Nuevo Profesor»); el **código de celda de 6 caracteres de v3** en `obtenerClaseCelda`/`togglePreferencia` (`dia + hora.replace(':','_')`, p. ej. `L07_55`) — el código de 7 caracteres anterior (`L_8_00`) corrompía `preferencias_horario` al guardar; y la etiqueta de las opciones de especialidad `{{ esp.descripcion }}` (era `{{ esp.nombre }}`, un campo que no existe en la tabla → opción en blanco).
- 🔗 **Cableado**: `app-layout.js` `vistaMap` + `'perfil.php': 'perfil-view'`; `app.js` registra `perfil-view`; el acceso rápido «Perfil» de `home-view.js` → `irA('perfil.php')` (era `configuracion.php`); `ProfesoresAPI.preferencias(idProfesor)` en `api/profesores.js` (sin id → el propio; para el admin, horas y vacías).
- 📄 **`index.html`**: nuevo `js/views/perfil-view.js?v=1` y subidas de versión: `home-view.js?v=1`, `profesores-view.js?v=1`, `app-layout.js?v=2`, `js/app.js?v=2`, `api/profesores.js?v=2`. **`sw.js`**: `NIVEL → v4-pwa-2` (archivos nuevos: re-caché de la PWA).
- ✅ **Verificado en navegador real** (Chromium headless contra Laragon, app real, usuarios de prueba documentados): **`testprofe` (profesor)** — sin menú «Profesores», «Perfil» con la vista; precarga (nombre/abreviatura/login, **clave vacía**, **abreviatura solo-lectura**); rejilla con **horas reales** (07:55…20:05); clics: **3 rojas (máx.)** + amarillas (la 4ª roja la rechaza); «Guardar» → **DB exacta** (`L 07:55 R`, `M 08:50 R`, `X 09:45 R`, `L 10:40 A`, `M 11:00 A`) y **round-trip** (recargar y las casillas persisten); **`testadmin` (jefe)** — «Perfil» con sus datos y guardar sin cambios **no toca** `preferencias_horario`; **`admin`** (`config`, credenciales temporales restauradas después) — sin «Perfil» en menú y en «Profesores» el modal de edición **carga las preferencias guardadas reales** del profesor (las del paso anterior), ciclos roja→amarilla correctos y «Guardar» → **DB exacta**. Sin `pageerror`/5xx (el único 401 es el `checkAuth` pre-login, esperado). `node --check` limpios en todos los JS tocados, `php -l` limpio; PHP 5 (sin `??`/`...`/`fn`/`?->`/`match` en el código nuevo).
- 🧹 **Limpieza**: filas de prueba de `preferencias_horario` (217/218) eliminadas; `profesores.clave` 217/218 y `config.admin` **tal y como estaban** (las claves documentadas de la tabla «Usuarios de prueba» y `config.admin` devuelto a su valor tras el intercambio temporal del test del admin en navegador).
### Actas — el editor no dejaba introducir texto, el jefe debía elegir departamento y «Asistentes» salía vacío (corregido, fiel a v3)
- 🐞 **Causa raíz**: `actas-view.js` **no tenía editor**: mostraba el acta con `v-html="actaContenido"` (solo lectura), sin ningún `textarea`/TinyMCE, de modo que «no me deja introducir texto». Además «Nueva acta» **creaba la fila al vuelo** con un `Asistentes` **vacío** (`<ol></ol>`), y el desplegable de **departamento** estaba activo para **todos** los roles (el jefe debía elegirlo a mano, en vez de entrar fijo al suyo).
- 🔧 **Backend — nuevo endpoint `backend/api/actas/nueva.php`** (fiel a `v3/ajax/actas/nueva_acta_departamento.php`): devuelve el **texto inicial** de un acta nueva — el apartado **`Asistentes` relleno con el listado completo de profesores del departamento, ordenado alfabéticamente por nombre**, más la apertura de **`Orden del día`** (`<p>Por completar</p>`). El departamento: el **admin** lo elige (parámetro de la petición) y el **jefe** siempre usa **el suyo** (de sesión). PHP 5 (sin `??`, `mbstring`), `cabeceraJson()` + `checkPermission(admin/jefe)`.
- 🧩 **Frontend — `actas-view.js` reescrita** (estilo v4, fiel a v3):
  - **Editor TinyMCE** (`textarea#editorActa` + `TinyMCEUtils.iniciar`) con la **misma configuración de v3** (`autolink lists advlist code fullscreen wordcount`, toolbar, `content_css css/estilos_tiny.css`, `height 300`): ahora **sí se puede introducir texto** del acta.
  - Flujo **«Nueva acta» fiel a v3**: no crea la fila; pide el texto inicial a `actas/nueva.php` y lo prepara en el editor (**todos los profesores del departamento en «Asistentes»**), y es **«Guardar cambios»** quien hace el `INSERT` (sin `idActa`) o el `UPDATE` (con `idActa`). Tras guardar, la acta aparece en el desplegable (se recarga la lista, fiel a v3).
  - **El jefe entra fijo a su departamento** (patrón v4 de `resultados_aprendizaje`/`seleccion`): el `<select>` de departamento va `:disabled="!esAdmin"` y, al montar, el jefe/profesor recibe `idDepartamento = usuario.idDepartamento` (no se le pregunta). El **admin** elige.
  - Botón **«Generar PDF»** (fiel a `v3`): `window.open('../backend/pdf/pdf_acta.php?idActa=X')` en pestaña nueva; deshabilitado sin acta elegida.
  - Al elegir una acta del desplegable, su **fecha** y **texto** se recargan en el formulario/editor (fiel a `v3.cambiarActa`); sin acta, se vacía.
- 🔗 **`frontend/js/api/actas.js`**: nuevo método `nueva(idDepartamento)` → `GET nueva.php?idDepartamento=` (convención de lectura `getOk` → `data.data`). `index.html`: bump `?v=` de `actas-view.js` y `api/actas.js`.
- ✅ **Verificado en navegador real** (Chromium headless contra Laragon, `testadmin` jefe depto 1): departamento **fijo y deshabilitado** (valor `1`); desplegable de actas poblado; «Nueva acta» abre el editor con **`Asistentes` y los 49 profesores del dpto** + `Orden del día`/`Por completar`; **se introduce texto** y `getContent()` lo trae; fecha se rellena; **«Guardar cambios»** → aviso «Acta guardada», la fila queda en BD y aparece en el desplegable; **round-trip** (re-seleccionar la acta trae el contenido + fecha); **«Generar PDF»** abre `pdf_acta.php?idActa=`; **sin errores de consola**. `node --check`/`php -l` limpios.
- 🧹 **Limpieza**: se borraron las filas de prueba creadas en la verificación (id `121`, `123`, `124`, `125`, por el marcador `ZZ Prueba edicion E2E`), **sin tocar** el resto de actas (entre ellas la `id 122` de fecha 2026-09-02, una prueba manual del propio usuario).

### Escenarios — el listado salía vacío y no había botones ni opciones («no funciona igual que en v3», corregido, fiel a v3)
- 🐞 **Causa raíz**: en `frontend/js/api/escenarios.js`, el método `listar()` **no recibía ni enviaba el `idDepartamento`**, pero la vista (`escenarios-view.js`) lo llama como `EscenariosAPI.listar(this.idDepartamento)`. La vista lo pasaba y el cliente lo **ignoraba**: la petición iba a `listar.php` **sin** parámetro, y `backend/api/escenarios/listar.php` lo exige (`getOptimoInt('idDepartamento')` → 400 «Departamento inválido»). La vista capturaba el error y dejaba `escenarios = []` → la tabla salía **«Sin escenarios»**, sin ningún escenario ni sus **6 botones** por fila (eliminar, editar, en vigor, activo para desideratas, duplicar, modo rueda): «faltan opciones y botones». Solo parecía completo porque la verificación previa era **de cabeceras/JSON** (`listar.php?idDepartamento=1` a mano), no de UI.
- 🔧 **Corrección** (1 línea, `frontend/js/api/escenarios.js`): `listar(idDepartamento)` ahora construye la URL con `?idDepartamento=${idDepartamento}` (mismo patrón v4 de `materias.js`/`profesores.js`), de modo que `EscenariosAPI.listar(this.idDepartamento)` de la vista envía el departamento y `listar.php` ya no hace 400.
- ✅ **Verificado en navegador real** (Chromium headless contra Laragon, `testadmin` jefe, depto 1): al entrar en **Desideratas → Escenarios** ya no sale «Sin escenarios», sino la **lista real** de escenarios, cada uno con sus **6 acciones** — **Eliminar** (papelera, con confirmación), **Editar** (lápiz, modal nombre+departamentos precargado), **En vigor** (✓), **Activo para desideratas** (candado), **Duplicar** (→ «nombre bis») y **Modo rueda** (brújula) — más el botón **«Nuevo escenario»** (el admin elige el departamento arriba, igual que en v3). **Flujo CRUD completo en la UI**: alta (aparece), editar/cambiar nombre, alternar `modo_rueda`, duplicar («bis» con los mismos departamentos) y borrado de las filas de prueba (vuelve al nº de filas original, sin restos en BD). `node --check` limpio; **sin cambios de backend** (los 6 endpoints `escenarios/{listar,obtener,guardar,eliminar,alternar,duplicar}.php` ya estaban bien) ni de esquema.

### Refactor del backend (monolitos → endpoints por acción) y limpieza del frontend — fiel a v3, listo para editar a mano
- 🔧 **Backend — los 12 monolitos `api/*.php` (más el `programaciones/index.php`) divididos en 71 endpoints por acción** (149 en total en `api/`), cada uno un fichero fino `api/{módulo}/{acción}.php` con `require` de `config.php`, `cabeceraJson()` y `try { Db::open() … } catch (DbException) catch (Exception)`; comentarios en español y sin `call_user_func_array`.
- 📚 **Lógica en compartidos** (`lib/`): `programaciones_compartidas.php` (grupos/materias/profesores aula↔seguimiento), `contenidos.php` (guardar texto PCCF/cont. defecto), `pdf_compartidas.php` (clase `MiPDFBase` con cabecera/pie), `horarios_compartidas.php` (`xDia` + `MiPDF` de FPDI), `resultados_aprendizaje.php` y `temas.php`.
- 🔒 **Permisos unificados** en `checkPermission()` (p. ej. `profesores/*`, `departamentos/*`) y **entrada unificada a JSON** (`cuerpoJson()`/`datosOptimo(Int)` en vez de `$_POST`), con `Db::close()` siempre.
- 🐞 **2 arreglos de bug** (aprobados): el typo `datasOptimoInt` → `datosOptimoInt` en `ciclos/borrar_asociacion_curso.php` (función inexistente: el endpoint fallaba), y el **desacople JSON/`$_POST`** de `departamentos/guardar` y `departamentos/eliminar` (leían `$_POST` pero el frontend envía JSON: `guardar` leía `id` en vez de `idDepartamento` y siempre hacía INSERT). Arreglo extra: se **restaura** el `guardar` de `temas_contenidos_defecto` (borrado por un refactor anterior).
- 🧩 **Frontend**: `Avisos` en vez de `Swal.fire` directo, `*Ok` de `http.js` en vez del envoltorio, constantes API con mayúscula y `departamentos.js` fusionado en su vista (7 avisos quedan **crudos** a propósito — info/toast/ayuda que `Avisos` no cubre). Sin cambiar comportamiento.
- 📄 **Excel/PDF sin cambio de comportamiento**: `excel.php` usa la clase `DesiderataExcel` (mismo XLS); los 6 `MiPDF*` heredan de `MiPDFBase` y `xDia`/`MiPDF`(FPDI) se comparten en `horarios_compartidas.php` (`yHora` se queda en cada fichero: dos implementaciones distintas). `api/pccf/generar.php` sigue monolito y `PHPExcel` se conserva (EOL).

### Módulo Desideratas completo (Escenarios, Selección, Histórico, Estadísticas, Excel, PDFs) — «no funciona» resuelto, fiel a v3
- 🐞 **El módulo «Desideratas» no funcionaba** (Escenarios, Selección, Histórico y Estadísticas sin backend/vidas y los PDFs/Excel sin portar). Entrega completa del módulo con el patrón v4 (una API JSON por módulo con `action`, `Db`, `cabeceraJson`, roles `ROLE_ADMIN`/`ROLE_JEFE_DEPARTAMENTO`/`ROLE_PROFESOR`), fiel a las páginas de v3.
- 🔧 **Backend**:
  - `backend/api/seleccion.php` (API única): `listar_escenarios` (al profesor solo los de `activo_desideratas`), `listar_especialidades`, `listar_profesores` (con horas de referencia), `listar_cursos` (al no-super solo `asignada_directiva = 0`, fiel a v3), `listar_seleccion`, `listar_profesores_materia`, `insertar_seleccion` (**orden fiel a v3**: `100` si la materia es asignada por el equipo directivo, `total+1` si no), `borrar_seleccion`, `borrar_toda_seleccion` (no-super sin las materias asignadas), `borrar_todas_selecciones` (solo super) y `ordenar_seleccion` (**modo rueda**: el no-super no puede reordenar → 400). PHP 5.4, sin `array_column`.
  - `backend/api/historico.php` (`listar`): horas y conflictos por profesor del escenario (mismos criterios de `v3/historico.php`: conflictos por peticiones sobradas, divisibilidad, mínimos de profesores, tutorías múltiples…).
  - `backend/api/estadisticas.php` (`listar`): horas por especialidad (impartidas vs nº profesores × 18), materias **sin escoger**, **conflictos** (demasiadas/pocas peticiones, no divisible, mín. profesores, máx. grupos/profesor, más de una tutoría) con `tuyo` para el profesor logueado (el admin nunca lo pone, fiel a v3) y `tienesConflictos`.
  - `backend/excel.php`: XLS fiel a `v3/excel.php` (resumen de horas por especialidad/profesor del escenario; `application/vnd.ms-excel`, plantilla `desideratas.xls`), con `require` de PHPExcel y `GROUP BY …, profesores.orden` (MySQL 8 `ONLY_FULL_GROUP_BY`).
  - `backend/pdf_desiderata.php` y `backend/pdf_preferencias.php`: **puertos fieles** de `v3/pdf_desiderata.php` (ficha del profesor `?idProfesor=X&idEscenario=Y` solo el propio; `?selEsp=<esp|Todos>&idDepartamento=X&idEscenario=Y` para jefe/admin) y de `v3/pdf_preferencias.php` (preference propio `?idProfesor=X`; departamento/especialidad para jefe/admin; «Acceso no permitido» sin permiso), con `@session_start();` (patrón de `pdf_programaciones_seguimiento.php`) y **FPDI** sobre las plantillas `backend/pdf/{plantilla.pdf,desiderata_horario.pdf}`.
  - **Librerías copiadas de v3** a `backend/lib/php/{fpdi,phpexcel}`. ⚠️ El servidor actual sirve **PHP 8.3**: en esa runtime los endpoints propios de v3 (Excel/PDF) fallan (`each()` en FPDI; offsets `$var{N}` de PHPExcel, error de *parse*); la **copia de v4** se parcheó con mínimo para funcionar (offsets `$var[N]`, `foreach` en vez de `each`); **v3 queda intacto**.
- 🧩 **Frontend** (Vue 3 + bootstrap.Modal + SweetAlert2, arrastre HTML5 nativo):
  - `seleccion-view.js`: 3 paneles de v3 (especialidad/escenario, cursos por especialidad con horas y aviso `<17`/`>22`, selección con **arrastrar para reordenar**, papelera —oculta a no-super en las asignadas— y badge «asignada»), modal de horas (con confirmación de especialidad distinta), modales de profesores de la materia, botones **Estadísticas / Histórico / PDF ficha / PDF todos / Preferencias / Excel** y **Ayuda** (texto de v3). El jefe entra fijo a su departamento; el **admin** elige departamento (patrón v4 de `actas`/`historico`); el profesor, si la activación `desideratas` está OFF, ve el aviso y sin edición.
  - `historico-view.js` y `estadisticas-view.js`: fieles a v3 (tarjetas por profesor con filas en **rojo** si hay conflicto y total de horas; paneles «Horas por especialidades» y «Conflictos» con el texto `tuyo` en **negrita** y el banner de conflictos propios solo para no-admin).
  - **Eliminados**: `api/excel.php`, `excel-view.js` (la Excel ya es el botón de la vista de Selección, como en v3) y `pdf_seleccion.php` (la reimplantación funcional anterior, sustituida por los puertos fieles). `index.html`: `?v=2` en los scripts del módulo.
- ✅ **Verificado en vivo** (HTTP/curl con sesión; **sin navegador** en el entorno, así que la verificación es de cabeceras/JSON/bytes, no de UI): login de los 3 roles; matriz de selección por rol (**jefe 212** cursos con las 10 asignadas vs **profesor 202**); `insertar` con orden v3 (asignada → `orden=100` en BD); `ordenar` por rol y bloqueo de modo-rueda; borrados con guardas super; **Escenarios**: duplicado del 28 («Curso 2025-2026 bis», **219 selecciones** copiadas), alternar `modo_rueda`/`actual`, guardar, `borrar_todas`, eliminar (sin restos); **Histórico**: 49 profesores, 11 conflictos reales; **Estadísticas**: INF **425/486** h, SAI **310/360**, 6 conflictos y `tuyo` comprobado con una selección temporal (y limpiada); **Excel**: 45 KB, CFB `d0cf11e0`, `application/vnd.ms-excel` (sin sesión → 401); **PDFs**: ficha propio (64 KB), ficha INF del depto (100 KB), preferencias propio (146 KB)/departamento (194 KB) y «Acceso no permitido» sin permiso. **BD restaurada**: escenario 28 con sus 219 selecciones originales, filas de prueba eliminadas, sin huérfanos y `config.admin`/`profesores.id=218` devueltos a sus valores originales y verificados. `php -l`/`node --check` limpios.

### PDFs de seguimiento de programaciones (Fase 8 — los botones «Pendiente» ya generan PDF, fieles a v3)
- 📄 **Nuevo endpoint** `backend/pdf_programaciones_seguimiento.php` (`?departamento=X&curso=Y&evaluacion=Z&categoria=FP|ESO/BACH`): replica fiel de `v3/pdf_programaciones_seguimiento.php` — portada (I.E.S. San Vicente, título, curso/evaluación, departamento + categoría) y las 5 secciones de v3: 1. seguimiento de la programación (temporalización por grupo y materia), 2. valoración de resultados académicos (aprobados/suspensos/otros + % de aprobados + HTML de `resultados`), 3. inclusión del alumnado (solo inclusiones con contenido, con el filtro de «HTML vacío» de v3; si no hay ninguna, «No hay datos disponibles»), 4. valoración de las horas de atención a pendientes/desdobles… (datos comunes del departamento) y 5. actividades extraescolares programadas **para la evaluación siguiente** (los datos comunes siguen en `seguimiento_programaciones_departamento`, igual que v3). Cabecera/pie «I.E.S. San Vicente» + «Pág x/y» como en v3.
- 🔗 **Frontend** (`programaciones-seguimiento-view.js`): los dos botones —que antes avisaban con SweetAlert2 «Pendiente (Fase 8)»— abren ahora `../backend/pdf_programaciones_seguimiento.php` con el **curso actual** (mismo criterio que `cargar`/`guardar`), la evaluación elegida y la categoría (`Ciclos Formativos` → `FP`, `ESO/BACH` → `ESO/BACH`), en una pestaña nueva. El **departamento** lo pone el propio usuario: jefe/profesor el suyo (de sesión); el **admin real** —que en v4 no tiene departamento, a diferencia de v3, donde lo guardaba la cabecera— elige el departamento en un **selector desplegable de la vista** (mismo patrón v4 de `actas`/`excel`/`historico`); sin departamento elegido los botones quedan desactivados y `generarPDF` avisa con SweetAlert2, de modo que nunca se genera un PDF con departamento 0 vacío.
- ✅ **Verificado en vivo**: PDFs válidos de 5 páginas para `categoria=FP` (con las 3 filas reales del curso actual —grupos 1º DAW y 2º DAM—: temporalizaciones, resultados con % de aprobados y secciones comunes) y `ESO/BACH` (secciones vacías con el texto por defecto, correcto porque no hay datos ESO/BACH); también con el departamento por sesión (sin el parámetro), el error «Falta el curso o la evaluación» sin curso/evaluación y la portada con departamento 0 sin sesión (mismo comportamiento que v3). `php -l` / `node --check` limpios.
- 🔧 **Corrección del PDF vacío con el admin real**: con el usuario `admin` (tabla `config`) el PDF salía **sin datos**, porque el front enviaba `departamento=` vacío —en v4 ese usuario no tiene `idDepartamento` (ni en sesión) y el endpoint caía en departamento 0, cuyo `m.idDepartamento = 0` no casa con ninguna materia. En v3 ese caso no existía: la cabecera dejaba `$_SESSION['departamentoUsuario']` fijado con el desplegable `seleccion_departamento` de la propia página. Corrección: la vista carga `departamentos/listar.php` y muestra el selector solo para el admin sin departamento propio; `generarPDF` usa `dptoParaPDF` (el propio o el elegido) y, si aún no hay ninguno, avisa en vez de abrir un PDF vacío; los botones, además de `idEvaluacion`, se desactivan hasta que hay departamento. Jefe/profesor siguen sin desplegable (el suyo, como en v3).
- 📌 El PDF de «Programación de aula» (Fase 2.4) **sigue siendo stub**: no se tocó en esta corrección (no formaba parte del encargo).

### Seguimiento de programaciones — las materias salían todas las del profesor, sin tener en cuenta el curso (corregido, fiel a v3)
- 🐞 **Causa raíz**: en `backend/api/programaciones_seguimiento/materias.php`, el desplegable de materias listaba **todas** las materias del profesor **de todos los cursos**: la consulta hacía `m.id IN (SELECT idMateria FROM seleccion WHERE idProfesor = ?)` **sin** el filtro de escenario. En v3 (`includes/cargar_materias_programaciones.php`, rama del profesor) el criterio es `… AND escenarios_desideratas.actual = TRUE`: solo las materias impartidas en los escenarios del **curso actual** — la misma consulta que ya usaban la opción de Programaciones, «Unidades», Resultados de aprendizaje **y el propio `grupos.php` de seguimiento** (que traía `e.actual = 1`; de ahí el «no se enlazan bien»: los grupos ya estaban filtrados y las materias, no).
- 🔧 **Corrección** (`programaciones_seguimiento/materias.php`): la consulta ahora hace `JOIN seleccion s ON s.idMateria = m.id JOIN escenarios_desideratas e ON e.id = s.idEscenario` y filtra `m.tiene_programacion = 1 AND s.idProfesor = ? AND e.actual = 1` — idéntico al resto de listados de materias de v4 (`programaciones/cargar_materias`, `temas/listar_materias`, `resultados_aprendizaje`). PHP 5 / sentencias preparadas; **sin cambios de esquema** ni de frontend (la respuesta sigue siendo `id, nombre, nomCurso, horas`).
- 🔧 **Mismo bug, mismo criterio**: `backend/api/programaciones_aula/materias.php` (Programación de aula, Fase 2.4) era **copia idéntica** de la consulta anterior, y v3 la filtra igual (`programaciones_aula.php` usa el mismo criterio `escenarios_desideratas.actual = TRUE`), así que se corrige con el mismo `JOIN` + `e.actual = 1`.
- ✅ **Verificado en vivo** (Laragon, BD `gestionies`, `testadmin` jefe): `seguimiento/materias.php?idProfesor=12` pasa de **7 materias** (todos los cursos) a las **4 del curso actual** (Acceso a Datos, Digitalización, Lenguajes de Marcas, Proyecto intermodular de DAW) y `?idProfesor=1` de **8 a 2** (Introducción a la nube pública (módulo optativo), Programación de Servicios y Procesos (Inglés)); la cascada completa sigue funcionando (profe 12 → materia 53 → grupo `semi` → `cargar`/`guardar` intactos); `testprofe` (profesor, sin asignaturas) sigue devolviendo lista vacía. `php -l` limpio en los dos archivos.

### Apartados PCCF — listado con título, numeración v3, borrar y reordenado («no está / no salen todos», fiel a v3)
- 🐞 **«La opción de apartados del PCCF no está» / no salen todos / sin título / sin borrar — causa raíz**: en `pccf-apartados-view.js`, la numeración `rule()` hacía `if (!a.subapartado)`, pero la API devuelve `subapartado` como **cadena** (`"0"`/`"1"`). En JS `"0"` es *truthy* (a diferencia de PHP, donde `!"0"` es true), así que `!a.subapartado` era **siempre false** → `cont` nunca se incrementaba y **todas** las filas salían como `0.1.`, `0.2.`, …, `0.20.` (parecía que «no están» los apartados). Además la fila **solo mostraba el número** (sin **título**), no tenía botón de **borrar** (el clic en la fila entera editaba) y el **reordenado por arrastre** estaba incompleto (había `@dragstart` pero ni `@drop` ni llamada a `ordenar`), a pesar de que el aviso lo anunciaba.
- 🧩 **Listado fiel a v3** (`pccf-apartados-view.js`): cada fila muestra la **numeración correcta y el título** («`1. Identificación del ciclo formativo`», «`3.1. Análisis del centro y del alumnado`», «`3.2. Ponderación de competencias profesionales`», «`10.1. Identificación de RRAA…`») con el sufijo «(opcional)» en los no requeridos (igual que `cargar_apartados.php` de v3), y a la derecha **dos botones** — **papelera** (borrar, con confirmación SweetAlert2 «¿Borrar este apartado? Se eliminarán todos los contenidos relativos a dicho apartado.») y **lápiz** (editar, abre el modal precargado). La numeración `rule()` compara **numéricamente** (`Number(a.subapartado) === 0`) para replicar el `cont++`/`cont2++` de v3.
- 🧩 **Reordenado por arrastre** (HTML5 nativo, patrón de `programaciones-apartados-view`/`competencias_ciclos-view`): `draggable` + `@dragstart`/`@dragover.prevent`/`@drop` que mueve la fila en el array y envía `PCCFApartadosAPI.ordenar('ap1,ap2,…')` (el endpoint `ordenar.php` ya interpretaba el prefijo `ap`); al soltar reordena en BD y recarga.
- 🧩 **Alta/baja**: «Nuevo Apartado» y el modal de edición piden **título, subapartado, requerido, contenido por defecto y tipo** (igual que v3); usa los endpoints `guardar`/`eliminar`/`ordenar` ya existentes (con `checkPermission` admin/jefe, fiel a v3).
- ⚠️ **Solo frontend**: `js/views/pccf-apartados-view.js` reescrito (numeración, título, borrar, arrastre) + `?v=1` en `index.html`. **Sin cambios de backend** ni de esquema (los 5 endpoints `pccf_apartados/{listar,obtener,guardar,borrar,ordenar}.php` ya estaban bien). La opción sigue siendo **solo admin** en el menú (fiel a v3).
- 🐞 **Dato: la BD en vivo le faltaba el apartado id=21** «Ponderación de competencias profesionales» (presente en el `gestionies.sql` de v3, `subapartado=1`): **restaurado** para que «salgan todos», y los `orden` devueltos al valor canónico (cada subapartado bajo su sección: `3.1.`+`3.2.` bajo «Adecuación…», `10.1.` bajo «Criterios PFI»). El `tipo` real de la BD se **conserva** (el dump es obsoleto en `tipo`: en vivo son 1/4/5/7/101 para ids 1/4/5/7/11, ver la línea de «Contenidos por defecto»). La tabla queda con **21 filas** (id 1‑21), sin duplicados ni restos de prueba.
- ✅ **Verificado en navegador real** (Chromium headless contra Laragon, app real, **admin** real con `config.admin` temporal, **restaurado al terminar**): al entrar salen los **21 apartados** con numeración v3 correcta y título («1. …» … «3.1. Análisis…», «3.2. Ponderación…» … «10.1. RRAA…» … «18. Criterios…»), 21 papeleras + 21 lápices; modal de edición **precargado**; **borrado** con confirmación; **alta** de una fila → 22 y **baja** → 21 (rollback limpio, sin restos); **drag&drop** de dos filas → `orden` 1↔2 en BD y **restaurado** al terminar; sin errores de consola ni de página (el único 401 es el `checkAuth` pre-login, esperado). `node --check` limpio.

### PCCF «Contenidos por defecto» — no se editan los apartados automáticos (tipo != 0), fiel a v3
- 🐞 **El desplegable de apartados ofrecía TODOS los apartados** (también los de `tipo != 0`, que se rellenan automáticamente a partir de la base de datos): el **admin** (y el jefe) podía seleccionar y guardar un contenido por defecto de un apartado que **no es editable**. Fiel a `v3/pccf_contenidos_defecto.php`, la lista solo debe ofrecer los apartados que **admitan** contenido por defecto **y** sean **editables** (`tipo == 0`), recorriendo todos para mantener la numeración original.
- 🧩 **Frontend** `js/views/pccf-contenidos-defecto-view.js`: el computed `apartadosFiltrados` ahora recorre **todos** los apartados (para mantener la numeración original `1.`/`1.1.`) pero solo añade a la lista los de `contenido_defecto && tipo == 0` — mismo criterio y numeración que v3 y que su hermana `programaciones-contenidos-defecto-view.js`. Con los datos reales: **ocultos** ids 1 (tipo 1), 4 (tipo 4), 5 (tipo 5), 7 (tipo 7) y 11 (tipo 101); **visibles** 2., 3., 3.1., 7., 8., 9., 10., 11., 12., 13., 14., 15., 16., 17., 18. (15).
- 🔒 **Backend** `backend/api/pccf_contenidos_defecto/guardar.php`: nuevo **guard** — lee el `tipo` del apartado y, si `tipo != 0` (o no existe), rechaza con 400 «El apartado seleccionado no es editable: se rellena automáticamente a partir de la base de datos», de modo que ni un POST directo pueda guardar un apartado no editable. PHP 5 / `mysqli_*` preparadas; **sin cambios de esquema**.
- ⚠️ `index.html` `?v=1` en `pccf-contenidos-defecto-view.js` (bust de caché). La opción es solo **admin**/**jefe** (como en v3); el `profesor` no la ve en el menú, así que su flujo de PCCF no se toca. **Nota**: la opción principal «PCCF» (`pccf-view.js`) sigue listando todos los apartados (v3 la filtra a `tipo == 0` en `cargar_apartados_pccf.php`); se deja tal cual por no ser la opción pedida.
- ✅ **Verificado en vivo** (Laragon, `testadmin` jefe): login OK; `guardar` con `idApartado=1` (tipo 1) → **400** rechazado por el guard y **sin cambio en BD**; `guardar` con `idApartado=2` (tipo 0) → **aceptado** (round-trip guardado y **restaurado byte a byte** al terminar); la lógica de `apartadosFiltrados` ejecutada en node contra el `listar` real → **15 visibles / 5 ocultos** con numeración original idéntica a v3. `php -l` / `node --check` limpios.

### Unidades — el botón «Unidades» enlaza a la materia elegida + materias por rol en la opción
- 🧩 **Botón «Unidades» (Programaciones)**: ahora navega a la opción de Temas/Unidades **con la materia seleccionada precargada** (igual que v3, `temas.php?idMateria=X`): `programaciones-view` emite `navigate` con la materia, `app-layout` la conserva y la pasa como prop `params` a las vistas, y `temas-view` precarga la materia y sus unidades al montar. El botón **solo está activo si hay materia seleccionada** (deshabilitado si no, más el guard de `irAUnidades`).
- 🔒 **Materias por rol en «Unidades»** (`temas.php` `listar_materias`, fiel a `cargar_materias_programaciones.php` de v3): el **profesor** solo ve las que imparte (escenario actual con `tiene_programacion`), el **jefe** las de su departamento y el **admin** todas (v4 no tiene el selector de departamento de v3). Antes salían todas para todos los roles.
- 📦 **Frontend**: `js/components/app-layout.js` (nuevo estado `params` + prop `:params` en la vista), `js/views/programaciones-view.js` (`irAUnidades` pasa la materia) y `js/views/temas-view.js` (prop `params` + precarga de la materia en `mounted()`, solo si está en la lista); `index.html` bump a `?v=1`/`?v=3`.
- ✅ **Verificado en vivo**: `temas.php` `listar_materias` → `testprofe` (profesor) ve **0 materias** (correcto, sin `seleccion` en el escenario actual, fiel a v3); `testadmin` (jefe) ve **98 del departamento**, igual que `cargar_materias` de Programaciones; con la materia 53 se precargan sus **6 unidades** (133 h anuales). `node --check` limpios en los 3 JS y `php -l` en `temas.php`.

### Programaciones — edición de apartados (fiel a v3) + PDFs completo / por apartado / de unidades (Fase 2.1, completada de verdad)
- 🧩 **Edición de apartados integrada en la vista «Programaciones»** (antes solo había listado + modal «Ver» + importar; ahora reproduce `v3/programaciones.php` + `js/programaciones.js`): desplegable de **materias** (el profesor, las suyas del escenario actual con `tiene_programacion`; el jefe, las de su departamento; el admin, todas — fiel a `cargar_materias_programaciones.php`) → desplegable de **apartados** con su numeración v3 (`1.`/`1.1.`; `categoria TODOS o FP/ESO-BACH` según si la materia tiene ciclo) → si el apartado es **editable** (`tipo = 0`) carga su texto y sale el editor **TinyMCE** (`editorProgramacion`, misma configuración que el resto de la app) con botón «Guardar»; si no, el **mensaje de v3** («el contenido se rellena automáticamente a partir de otros apartados (Unidades, RA/CE, FE…); edita en la opción correspondiente»).
- 🔧 **Backend** `backend/api/programaciones/index.php`: `cargar_materias` (por rol, fiel a v3), `cargar_apartados` (numeración + `categoria`), `cargar_contenido` (leer) y `guardar_contenido` (INSERT/UPDATE por `idMateria`+`idApartado`; `affected_rows == 0` → flag `sin_cambios` que la vista avisa con SweetAlert2 «El contenido ya estaba guardado así (no se han realizado cambios)», fiel al `'si'` de v3). `listar`/`obtener`/`importar` se conservan.
- 🧩 **Los 3 PDFs de v3** (`backend/` raíz, scripts GET autocontenidos **sin sesión**, `window.open` desde la vista — patrón v4): `pdf_programaciones.php` («PDF Completo», toda la materia: portada + índice TOC + apartados con `Bookmark`, FE en página propia y cada tema en su página), `pdf_programaciones_apartado.php` («PDF de Apartado», el apartado pedido + sus subapartados hasta el siguiente principal) y `pdf_unidades_programacion.php` («PDF de Unidades», una página por tema: tabla horas/trimestre/peso, campos y RA/CE). La lógica de contenido se factoriza en **`backend/lib/programaciones_pdf.php`** (generadores fieles a `v3/includes/generar_apartado_*.php`; PHP 5 sin `…`). El «PDF de Apartado» enruta a `pdf_unidades_programacion.php` si el apartado es de temas (`tipo = 13`), igual que v3.
- 🧩 **Botón «Unidades»**: navega a la opción existente de Temas/Unidades de programación (`temas.php`) — enlaza con las unidades como en v3; el «PDF de Unidades» también abre `pdf_unidades_programacion.php?idMateria=X`.
- 🔒 **Comportamiento por rol fiel a v3**: el editor y los 3 PDFs son visibles para todo rol (la activación `config.programaciones` está en ON); el botón **«Cont. defecto Unidades»** (→ `temas_contenidos_defecto.php`) y **«Importar»** solo los ve el rol `admin` (el `esAdmin` de la vista es `rol === 'admin'`, igual que los `$permisos` de v3 → el jefe `testadmin` no los ve, como en v3). «Importar» conserva la transacción sobre las tablas reales v3 (borra y reinserta `contenidos_programaciones`/`competencias_temas`/`criterios_temas`/`temas` de la materia destino) y la confirmación «¡Esta acción no se puede deshacer!».
- ⚠️ **Backend PHP 5** (suelo efectivo 5.4: literales `[]` OK, sin `…`/`??`): `lib/programaciones_pdf.php` reutiliza `call_user_func_array('mysqli_stmt_bind_param', …)` para el bind y el curso académico lo resuelve del escenario actual (igual que v3). Sin cambios de esquema de BD.
- ✅ **Verificado en vivo** (Laragon, `testadmin`/`testprofe`): `cargar_materias` por rol, `cargar_apartados` con numeración FP/ESO-BACH correcta, `cargar_contenido`/`guardar_contenido` round-trip con la señal `sin_cambios` (sin cambio → `true`; con cambio → `false`); los 3 PDFs devuelven `200 application/pdf` con cabecera `%PDF` (completo 53/424, apartado 53/424, unidades 53/424); `php -l` limpio en `lib/programaciones_pdf.php` + los 3 `pdf_*.php` + `index.php` y `node --check` limpio en `programaciones-view.js` + `api/programaciones.js`.
- 📦 **Frontend**: `js/views/programaciones-view.js` reescrito (deshace el listado+modal «Ver» de la entrega inicial) y `js/api/programaciones.js` (nuevos `cargarMaterias`/`cargarApartados`/`cargarContenido`/`guardarContenido`); `?v=2` en los 2 scripts de `index.html`.

### Competencias por Ciclo — el desplegable de ciclos no se poblaba («la opción no funciona»)
- 🐞 **«La opción de competencias no funciona correctamente» — causa raíz**: en `competencias_ciclos-view.js`, `mounted()` solo instanciaba el modal y **nunca llamaba a `cargarCiclos()`** → el array `ciclos` quedaba vacío para siempre, el desplegable solo mostraba «-- Selecciona un ciclo --» (sin opciones), no se podía elegir ciclo y `cargar()` nunca se disparaba → **no aparecía ninguna competencia**. La API no fallaba (`listar_ciclos` y `listar` devuelven bien), así que el fallo era solo del cliente. Corrección: `mounted()` ahora llama a `this.cargarCiclos()` (mismo patrón que el resto de vistas, p. ej. `ciclos-view`).
- 🧩 **Fiel a v3** (`competencias_ciclos.php` + `js/competencias_ciclos.js` + `modales/competencias_ciclos.php`): el listado se numera por **código** («a. Preparar equipos…»), no por posición; el modal de alta/edición pide ahora **código, texto y tipo** (1 = Profesional, 2 = Para la empleabilidad, igual que v3 — el `tipo` lo filtran endpoints como `pccf/generar.php`); recuperado el **reordenado por arrastre** con la mecánica nativa de HTML5 de v4 (modelo de `programaciones-apartados-view`): `dragstart` / `@dragover.prevent` / `drop` que envía el string de orden con prefijo `cm` (`cm2,cm1,cm3,…`) que el backend ya interpretaba con `substr($cod, 2)`; y el texto de ayuda y la confirmación de borrado («Confirmas el borrado de la competencia 'a'?») como en v3.
- ⚠️ Solo frontend: `js/views/competencias_ciclos-view.js` (recarga de ciclos, modal con `tipo`, arrastre, numeración por código) + `?v=2` en `index.html`. **Sin cambios de backend** (el endpoint `competencias_ciclos.php` ya soportaba `listar_ciclos/listar/obtener/guardar/ordenar/eliminar` con rol admin), de BD ni de PHP 5 (el servidor sigue sirviendo PHP 8.3; el objetivo de compatibilidad es PHP 5.6).
- ✅ **Verificado en navegador real** (Chromium headless contra Laragon, app real, **admin** real con `config.admin` temporal — el menú es solo-admin, por eso no con `testadmin`/`testprofe`): menú «Programaciones → Competencias» → desplegable con los **13 ciclos** (antes: 0); al elegir el ciclo 6 aparecen sus **25 competencias** numeradas por código («a. …», «b. …»); modal de edición con **código/texto/tipo precargados**; alta de una competencia de prueba (tipo 2) → 26 en BD y fila visible; borrado → 25; **drag&drop** de las dos primeras filas → `orden` 1↔2 en BD (bodies de los POST de `ordenar` capturados y correctos: `cm2,cm1,cm3,…` y `cm1,cm2,cm3,…` tras restaurar), y orden devuelto al original al terminar; `config.admin` restaurado y verificado; sin errores de consola ni de página (el único 401 es el `checkAuth` pre-login, esperado); `node --check` limpio.

### Resultados de aprendizaje — selector de materias arreglado (fiel a v3) + PDFs de empresa (Fase 4.1)
- 🐞 **«La opción no funciona correctamente» — causa raíz**: la vista `resultados_aprendizaje-view.js` se referenciaba al cliente como `ResultadosArendizajeAPI` (23 caracteres, sin la `p`), pero el cliente `js/api/resultados_aprendizaje.js` lo declaraba como `ResultadosAprendizajeAPI` (24, **con** la `p`) → `ReferenceError` en cada llamada; el `try/catch` de `cargarMaterias()` lo tragaba en silencio y el desplegable de materias quedaba **siempre vacío** (el de departamentos sí cargaba, por eso la opción parecía a medias). Corrección: renombrado del `const` a `ResultadosArendizajeAPI`, que es el nombre que usan las 11 referencias de la vista y la convención del módulo (`ResultadosArendizajeView` en la vista y `app.js`).
- 🧩 **Comportamiento fiel a v3** (la opción, en `v3/formacion_empresa.php`): el **departamento** lo elige solo el **admin** (desplegable; al cambiar se limpian materias/RA); **jefe de departamento y profesor** lo tienen **fijo al suyo** (desplegable deshabilitado con su valor, fiel a v3). El desplegable de **materias** por rol: el **profesor** solo ve **las suyas** (`seleccion` del profesor + escenario actual + `tiene_programacion`), y el **jefe/admin** ve **todas las del departamento con programación activa** — mismo criterio de v3 (`listar_materias`: el admin pasa `idDepartamento` por parámetro, jefe/profesor lo resuelve el backend de su sesión).
- 🧩 **Los 3 botones de PDF de v3** («Ver resumen general», «RAs empresa», «CEs empresa»): `backend/pdf_resultados_aprendizaje.php` (nuevo, TCPDF del patrón v4 `class MiPDF extends TCPDF`, cabecera «I.E.S. San Vicente» y pie de página) con los 3 modos `?modo=resumen|ra|ce`, que replican las 3 páginas HTML de v3: resúmenes de empresa por ciclo (totales de horas empresa y % medio con el umbral 10-20 % y asterisco), detalle de RAs empresa (ciclos de la familia Informática, materias con `horas_empresa>0` y acrónimos fieles al `obtenerAcronimo` de v3, reimplementado para PHP 5 sin `mbstring`) y CEs empresa (CRs con % empresa y su tema vinculado). La vista los abre con `window.open` directo al endpoint (patrón v4), sin impresión por el navegador.
- 🔧 **Backend** `resultados_aprendizaje.php`: `listar_materias` reescrito por rol (admin con `idDepartamento` requerido, jefe/profesor con el departamento de sesión) y todas las acciones de escritura acotadas a departamento (`guardar`, `actualizar_horas` + `tienePermisoEdicion`, `eliminar`, criterios `guardar/actualizar/eliminar_criterio`); `actualizar_evaluacion` conserva solo la comprobación de departamento, fiel al endpoint de v3 (sin comprobación de rol). Cliente `api/resultados_aprendizaje.js`: `listar_materias(idDepartamento)` solo adjunta `&idDepartamento=` cuando hay departamento (caso admin).
- ✅ **Verificado en navegador real** (Chromium headless contra Laragon, app real): **jefe** (`testadmin`) — departamento fijo «1» deshabilitado, 98 materias del departamento y los 3 botones de PDF; **profesor** (`testprofe`) — departamento fijo, **solo las suyas** (con una asignatura temporal, borrada después: «Programación (1º DAM)», 2 opciones) y 3 botones; **admin** (credenciales reales vía intercambio temporal de `config.admin`, restaurado y verificado) — elige departamento → aparecen las 98 materias; los 3 PDFs devuelven `200 application/pdf` con cabecera `%PDF` en los 3 modos; sin errores de consola. `php -l` / `node --check` limpios.
- ⚠️ Frontend (vista + cliente), `backend/api/resultados_aprendizaje.php` y `backend/pdf_resultados_aprendizaje.php` (nuevo). Sin cambios de esquema de BD ni de endpoints. PHP 5 (sin `??`, `call_user_func_array` para `mysqli_stmt_bind_param`, `mb_substr` con `function_exists`). `?v=2` en los 2 scripts del módulo de `index.html`.

### TinyMCE 7 — re-inicialización del editor «la segunda vez» (PCCF, Unidades, Cont. defecto unidades)
- 🐞 **Causa raíz de «funciona la primera vez, pero la segunda entrada no sale bien» (cambio de apartado del PCCF, reabrir un tema, o entrar a otra opción)**: en esta build de TinyMCE 7.9.1, `tinymce.remove('id')` (con id como string) **no hace nada** — el teardown real es `editor.destroy()`. Al re-inicializar un id cuya instancia anterior sigue viva, el `init` nuevo compite con la instancia pendiente y el editor queda fantasma: registrado pero **sin adjunto al DOM y sin iframe**, con contenido vacío o antiguo; además `guardar()` lee `tinymce.get(id)` en un estado inconsistente (cambios perdidos en silencio). El caso más visible: los ids `contexto` / `recursos` / `metodologia` / `adaptaciones` se reutilizan en **«Unidades»** y **«Cont. defecto unidades»**, y el editor del PCCF se re-inicializa al cambiar de apartado sin salir de la vista.
- 🧩 **`frontend/js/tinymce-helpers.js`** (nuevo, se carga en `index.html` tras `tinymce.min.js`): global `TinyMCEUtils` que serializa el ciclo de vida — `quitar(ids)` (un solo `destroy()` por id, sin doble-destrucción, y espera la promesa de destrucción si la devolviera), `iniciar(config, ids)` (espera a que no quede ninguna destrucción pendiente, con salvaguarda que destruye cualquier instancia viva antes del `tinymce.init`), y `leer(id)` / `get(id)` / `disponible()`.
- 🧩 **Las 7 vistas con editor** pasan a usar el helper (`await TinyMCEUtils.iniciar(...)` y `borrar*() → TinyMCEUtils.quitar(...)`): `pccf-view` (re-init al cambiar de apartado), `pccf-contenidos-defecto-view`, `programaciones-aula-view`, `programaciones-contenidos-defecto-view`, `programaciones-seguimiento-view` (3 editores), `temas-view` (9 editores) y `temas-contenidos-defecto-view` (4 editores con los mismos ids que Unidades). `guardar` / `limpiar` / `leerEditores` siguen usando `tinymce.get(id)` en solo-lectura, que ahora siempre apunta a la instancia viva.
- ✅ **Verificado en navegador real** (Chromium headless contra Laragon, app real, `testadmin`): PCCF — editor en la 1ª entrada y **re-inicialización al cambiar de apartado** (2ª vez, misma vista); Unidades — editores al abrir un tema con **contenido igual al real** del `obtener` (1ª vez); «Cont. defecto unidades» — los 4 ids colisionantes con **contenido igual al real** de la API (con el código antiguo, el editor de `contexto` salía vacío y los editores quedaban sin adjunto al DOM); y volver a Unidades y reabrir el tema (3ª entrada). En cada paso se aserta que el editor está registrado **y** adjunto al DOM y que su contenido coincide con el dato real (el mismo E2E con el código antiguo falla en la 2ª y 3ª entradas). Sin errores de página.
- ⚠️ Archivo nuevo `tinymce-helpers.js` + carga en `index.html` + refactoring de las 7 vistas. Sin cambios de backend (seguirá PHP 5), de esquema de BD ni de la versión de TinyMCE (la misma 7.9.1 local, idéntica a v3).

### Materias — botones por materia (competencias y datos por grupo) y formulario completo (fiel a v3)
- 🧩 **Botones por materia** (fiel a `v3/ajax/materias/cargar_materias.php`): cada fila ahora tiene, además de editar/borrar, dos botones: **asociar competencias** (siempre) y **gestionar datos por grupo** (solo si el curso tiene grupos, igual que v3). La visibilidad del botón de grupos se decide con los grupos cargados una vez y contados por curso en cliente (patrón de `especialidades-view`), fiel al `SELECT * FROM grupos WHERE idCurso=…` de v3.
- 🧩 **Formulario de alta/edición completo** (fiel a `v3/modales/materias.php`): ahora pide todos los campos de v3 — Nombre, Código oficial, Nombre oficial, Créditos ECTS, Horas anuales, Tipo (Tutoría/Inglés/Otras), **Departamento** y **Especialidad en cascada** (al cambiar de departamento se vacía la especialidad, como en v3), las 4 casillas (computables, asignada por el equipo directivo, tiene programación, divisible) y la información de referencia (cantidad de unidades, horas/semana, horas complementarias, mín. nº de profesores, máx. grupos por profesor).
- 🧩 **Al crear** `guardar.php` puebla `materias_grupos` con los datos de referencia para cada grupo del curso (igual que v3); **al editar** no se toca `idCurso` (fiel a v3). Los campos vacíos (código/nombre oficial, ECTS, horas anuales, departamento, especialidad) se guardan como `NULL`, igual que v3.
- 🧩 **Datos por grupo** (fiel a `v3/modales/materias_grupos.php` + `cargar_forms_materias_grupos.php`): modal con un formulario por cada grupo (cantidad, horas, horas complementarias, mín. profesores, máx. grupos/profesor) y el botón **«Importar datos generales»** que rellena todos los grupos con los datos de referencia (sin guardar); cada **«Guardar»** persiste por grupo (`insertar_materia_grupo`, jefe/admin).
- 🧩 **Competencias de la materia** (fiel a `v3/modales/competencias_materia.php`): modal con el listado de competencias asociadas (con botón de borrar) y un desplegable para **añadir** nuevas (las del ciclo de la materia, que se resuelve por `cursos → cursos_ciclos → ciclos`, primer ciclo, igual que v3). **Solo admin** puede asociar/borrar (fiel a v3); el listado es de solo lectura para el resto (el jefe, que no tiene el menú, tampoco llegaría).
- 🔧 **Backend nuevo** (todos PHP 5 / `mysqli_*` con preparadas, sin `??`): `listar_materias_grupos.php` (grupos + valores existentes + datos generales), `insertar_materia_grupo.php` (upsert, jefe/admin), `competencias_listar.php` (asociadas + opciones del ciclo), `competencias_asociar.php` (solo admin, evita duplicados), `competencias_borrar.php` (solo admin).
- 🔧 **`guardar.php` ampliado** (fiel a `v3/ajax/materias/insertar_materia.php`): lee el conjunto completo de campos; CREATE inserta 18 campos (la columna `grupo` es NOT NULL sin default → se guarda vacía) y puebla `materias_grupos` de cada grupo del curso; UPDATE setea todos los campos salvo `idCurso`.
- 🔧 **`eliminar.php` en cascada** (fiel a `v3/ajax/materias/borrar_materia.php`): al borrar una materia también borra sus filas de `materias_grupos`, `seleccion` y `contenidos_programaciones` → no quedan huérfanas.
- 🔧 **Cliente** `api/materias.js`: `listar_materias_grupos`, `insertar_materia_grupo`, `competencias_listar`, `competencias_asociar`, `competencias_borrar`.
- ✅ **Verificado** (`php -l` / `node --check` limpios; por HTTP con sesión): CREATE con el payload exacto de la vista (todos los campos + `materias_grupos` pobladas en los 2 grupos del curso 17), UPDATE (los campos vacíos → `NULL` en BD; las casillas 0/1 correctas), upsert de un grupo (el valor cambia y `listar_materias_grupos` lo refleja), competencias como **admin** (asociar → queda 1 en BD, re-asociar avisa «ya está asociada», borrar → vuelve a 0) y como **jefe** (`testadmin`) → 403 en asociar/borrar (fiel a v3); borrado en cascada (grupos/competencias de la materia a 0, sin restos); `config.admin` restaurado y verificado tras probar con credenciales temporales.
- ⚠️ Frontend (vista + cliente) + 5 endpoints nuevos + `guardar`/`eliminar` ampliados. Sin cambios de esquema de BD. `?v=3` en los 2 scripts de materias de `index.html`.

### Materias — desplegable de curso primero (fiel a v3) y alineación de campos
- 🧩 **Desplegable de curso como primer elemento** (fiel a `v3/materias.php`): `materias-view.js` añade el desplegable de curso como primer elemento de la vista (igual que v3). Al elegir un curso se lista **solo** las materias de ese curso, y «Nueva Materia» exige tener un curso elegido antes (aviso «Debes seleccionar un curso primero»), igual que v3; las materias nuevas se crean asociadas al curso elegido.
- 🐞 **Alineación de campos** (mismo tipo de fallo que la corrección de `grupos`): la tabla y el `:key` usaban `m.idMateria` (campo inexistente; la PK de `materias` es `id`) → la columna ID salía en blanco y el borrado fallaba (el cliente enviaba `idMateria` y `eliminar.php` lee `id` → 400). Vista y cliente alineados a `id`.
- 🔧 **Cliente**: `api/materias.js` — `listar(idCurso)` pasa el filtro `?idCurso=` al endpoint cuando hay curso (sin él, todas) y `eliminar` envía `id` (lo que lee `eliminar.php`).
- 🔧 **Backend**: `backend/api/materias/listar.php` ahora acepta `idCurso` opcional (si `> 0` filtra `WHERE idCurso = ?`), fiel a `v3/ajax/materias/cargar_materias.php`.
- ✅ **Verificado**: `php -l` / `node --check` limpios; por HTTP: `listar` sin curso (298), `?idCurso=17` (10), `?idCurso=1` (6); round-trip con sesión de `testadmin` (crear una materia `ZZ` en el curso 17 → aparece en el filtro → borrar por `id` → sin restos; BD intacta, 0 `ZZ`).
- ⚠️ Frontend (vista + cliente) + consulta de `listar.php`; sin cambios de esquema de BD. `?v=2` en los 2 scripts de materias de `index.html`.

### Especialidades — acceso del jefe corregido (ítem de primer nivel) y `?v=` para forzar la carga de los scripts
- 🐞 **El jefe no podía llegar a «Especialidades»**: el ítem del sidebar (submenú de «Profesores y Departamentos») listaba `ROLE_JEFE_DEPARTAMENTO`, pero el **padre es solo de admin** → para el jefe el ítem existía pero quedaba oculto (`v-show`) sin forma de desplegarlo. Ahora el ítem de submenú es **solo admin** (sigue dentro de «Profesores y Departamentos») y se añade un ítem **de primer nivel** «Especialidades» (id 12, `getMenus` en `backend/config.php`) visible **solo para el jefe de departamento** → acceso directo a la misma vista (solo lectura).
- 🕵️ **«Las especialidades siguen sin salir» en el navegador**: se verificó que Apache sirve los JS nuevos (disco == servido) y el flujo completo funciona en un navegador real → el navegador del usuario seguía ejecutando el **JS antiguo** desde su caché (los scripts locales no llevaban versión y una SPA ya cargada no recarga los scripts al navegar). `index.html` carga ahora `especialidades-view.js`, `api/especialidades.js`, `grupos-view.js` y `api/grupos.js` con **`?v=2`** → son URLs nuevas para la caché del navegador y una recarga normal garantiza la versión actual.
- ✅ **Verificado en navegador real** (Chromium headless contra Laragon, con la app real): **admin** — desplegable de departamentos, listado filtrado, creación de una especialidad de prueba **vinculada al departamento** (sale en la tabla y en `listar`) y borrado; **jefe** (`testadmin`) — ítem «Especialidades» de primer nivel, **sin** desplegable ni botón «Nueva Especialidad» ni columna de acciones, y el nº de filas coincide con la BD de su departamento. Además, `guardar`/`eliminar` por HTTP probados con sesión de admin real (credenciales temporales de `config` restauradas y verificadas).
- ⚠️ Un ítem de menú en `backend/config.php` + `?v=2` en los 4 scripts de `index.html`. Sin cambios de esquema de BD ni de endpoints.

### Especialidades — desplegable de departamentos (admin) y vista directa del propio departamento (jefe)
- 🐞 **La lista salía vacía («Sin especialidades»)**: `api/especialidades.js` devolvía el array crudo y la vista comprobaba `result.success` (el sobre `{success, data}` que usan el resto de clientes) → siempre `undefined` → lista vacía. Ahora `listar()` devuelve el sobre, igual que el resto de clientes.
- 🐞 **Nombres de campo**: la tabla `especialidades` usa `id` (código de 3 letras, p. ej. `INF`, `BYG`) y `descripcion`; la vista usaba `idEspecialidad`/`nombre` (inexistentes) → el ID y el nombre no se pintaban. Vista alineada a los campos reales (`id`, `descripcion`, `idDepartamento` + `departamento` del JOIN).
- 🔀 **Comportamiento por rol (fiel a v3)**: el **admin** ve el **desplegable de departamentos** y la tabla se filtra por el elegido («Nueva Especialidad» exige tener departamento elegido, como en v3); el **jefe de departamento** no ve desplegable y ve **directamente las especialidades de su departamento** (solo lectura: `guardar`/`eliminar` siguen siendo solo-admin, fiel a v3).
- 📝 **Menú**: en `getMenus` (`backend/config.php`) el item «Especialidades» ahora incluye `ROLE_JEFE_DEPARTAMENTO` para que el jefe lo vea en el sidebar.
- 🧩 **Modal** (solo admin, fiel a v3): Código (3 letras, bloqueado al editar), Descripción, horas de tutoría/inglés (estimación) y departamento en solo lectura.
- 🔧 **Cliente**: `eliminar` envía `id` (lo que lee `eliminar.php`); antes enviaba `idEspecialidad` → siempre 400 «ID inválido».
- ✅ **Verificado**: `php -l` y `node --check` limpios; `listar` por HTTP devuelve las 18 especialidades con su `departamento`; `departamentos/listar.php` (opciones del desplegable) 200.
- ⚠️ Frontend + item de menú en `config.php`; sin cambios de esquema de BD.

### Grupos — columna de Curso en la tabla de grupos
- 🏷️ **Columna «Curso»**: `backend/api/grupos/listar.php` ahora hace `LEFT JOIN cursos` y devuelve `nombreCurso`; la vista muestra el curso de cada grupo junto a su ID y nombre (y `—` si no tuviera), para verlo todo en conjunto.
- 🐞 **La columna ID salía vacía**: la vista usaba `g.idGrupo` y la PK de `grupos` es `id` → ID en blanco y borrado roto (el cliente enviaba `idGrupo` y `eliminar.php` lee `id` → 400 «ID inválido»). Vista y cliente alineados a `id` (tabla, `:key`, editar y eliminar).
- 🧩 **«Nuevo Grupo»**: el modal ahora pide el **curso** (obligatorio al crear; al editar queda bloqueado, fiel a v3 — `guardar.php` no toca `idCurso` al editar) → ya no se crean grupos huérfanos sin curso.
- ✅ **Verificado**: `php -l` / `node --check` limpios; JOIN probado en la BD en vivo (cada grupo devuelve su curso, p. ej. `(mañanas)` → `2º DAW`).
- ⚠️ Frontend + consulta de `listar.php`; sin cambios de esquema de BD.

### Selector de temas — Bootstrap por defecto + 26 temas Bootswatch (claros y oscuros)
- 🎨 **Selector de tema en la barra superior** (botón de icono 🎨 junto al «Salir»): desplegable con **los temas claros agrupados por un lado y los oscuros por el otro** (cabeceras en negrita «Temas claros» / «Temas oscuros», temas con sangría) y ✓ en el tema activo: 20 claros (Bootstrap + brite, cerulean, cosmo, flatly, journal, litera, lumen, lux, materia, minty, morph, pulse, sandstone, simplex, sketchy, spacelab, united, yeti, zephyr) + 8 oscuros (Bootstrap + cyborg, darkly, quartz, slate, solar, superhero, vapor). El «Bootstrap» del grupo oscuro es la **misma** hoja por defecto con `data-bs-theme="dark"` (el modo oscuro oficial de Bootstrap 5.3); la etiqueta la repiten los dos grupos porque el grupo ya dice si es claro u oscuro. Se cambia al instante y **persiste en el navegador** (`localStorage`, clave `tema-gesionies`); al recargar, un script inline en `index.html` lo aplica **antes de pintar** (sin destello) y, si el CDN no lo sirve, **vuelve a Bootstrap estándar**.
- 📦 **Fuente**: los CSS de tema se cargan de `cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/<tema>/bootstrap.min.css` (mismo CDN y misma versión 5.3.8 que Bootstrap; la hoja por defecto sigue sirviéndose de `bootstrap@5.3.8` con SRI, que se retira al cambiar la URL porque la `integrity` solo vale para el CSS por defecto).
- 🌗 **Adaptación a temas**: cabecera y menú lateral llevan una **superficie clara fija** (`#f8f9fa`) con **tinta fija** (`#212529`) en `app.css` — los tokens de tema (`bg-body-secondary`, `--bs-dark`) no garantizan contraste en todos los Bootswatch, así que en temas oscuros es una **barra clara** (patrón habitual); la marca, el usuario y los textos del menú heredan esa tinta. Todo lo demás es de Bootstrap y se adapta solo al tema: filas de listas y `card-header` (sin color fijo), el cuadrante de preferencias (`bg-danger`/`bg-warning`), `btn-*` (incluido el `btn-link` de toggle/tema), `badge` y `table`. `app.css` añade también la regla de `max-height`/scroll del desplegable de temas.
- ✅ **Verificado**: los 26 temas + el por defecto en HTTP 200 en el CDN; `node --check` en todos los JS; parse del script inline; balance de etiquetas; login `testprofe` OK.
- ⚠️ Solo frontend. Backend y datos intactos.

### Pase de simplificación UI — CSS propio mínimo + Bootstrap 5.3 puro (se pierde el tema cálido)
- ✂️ **`frontend/css/app.css` de 560 líneas a 45**: ahora solo contiene el layout off-canvas (`.header-bar` fija de 56 px, `#page-content-wrapper` con `margin-top`, `#sidebar-wrapper` + `#wrapper.toggled`), `.datostema` (altura mínima del editor TinyMCE) y la regla `.btn:has(> .bi:only-child)` que centra iconos en botones solo-icono (sigue requiriendo `:has()`; en navegadores antiguos se ignora sin roturas). Se elimina **todo** el tema cálido (`--bs-primary`, variables `--brand`/`--tinta`/…), los overrides de botones, tarjetas, tablas, modales, formularios, scrollbar y foco: **Bootstrap 5.3 y Bootstrap Icons deciden el aspecto** (primario azul por defecto). Se pierde el diseño cálido original, deliberadamente.
- 🧩 **Plantillas a clases de Bootstrap (las clases propias quedan sin uso y se retiran del CSS)**:
  - Las 9 filas `.listado/.claro/.izquierda/.derecha` (competencias, cualificaciones ×3, histórico, resultados ×2, selección ×2) → `d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2 mb-2 bg-light` + texto `flex-grow-1` + botones `d-flex gap-2`.
  - 4× `control-label` → `form-label` (`temas-contenidos-defecto`).
  - Marcadores sin estilo `profesor-item`/`preferencias` retirados (`profesores`); `sidebar-heading` → `p-3 text-center border-bottom` + `<strong>`; `.submenu` → `ps-4`.
  - Login (`login-container`/`login-header`) → `container py-5` + `row justify-content-center` + `col-12 col-md-8 col-lg-5 col-xxl-4`.
  - Inicio (`.inicio`) → `d-flex align-items-center justify-content-center min-vh-100`.
- 📱 **Responsive**: se añade `table-responsive` a las 5 tablas que no la tenían (listado de `temas`, preferencias de `profesores`, y las 3 de `excel`); el resto ya usaba su wrapper.
- ✅ **Verificado**: `node --check` limpio en los 60 JS; balance de etiquetas en los 12 archivos tocados; los 62 recursos locales en HTTP 200; login `testprofe` + `check`/`logout` OK en vivo.
- ⚠️ Solo frontend (CSS + plantillas). Sin cambios de backend ni de datos. El diseño queda **estándar de Bootstrap** (azul primario, tarjetas/tablas/modales por defecto): la ganancia es menos CSS propio, más legible y 100 % mantenible sin reglas a medida.

### Pase de interfaz de usuario (UI) — tema cálido, iconos centrados y pulido general
- 🎨 **`frontend/css/app.css` reescrito**: tema cálido coherente (continuidad con v3; `--bs-primary` → `#9c6644`, de modo que `btn-primary`/`text-primary`/`bg-primary`/spinners heredan el marrón), el layout (header fijo de 56 px + sidebar off-canvas) y el pulido de botones, tarjetas, modales, tablas y formularios. Cabeceras de modal unificadas (marrón cálido con título y X en blanco), scrollbar, foco visible y espaciados en tono cálido.
- 🎯 **Iconos centrados en botones solo-icono**: regla `.btn:has(> .bi:only-child) { display:inline-flex; align-items:center; justify-content:center; padding-top:0; padding-bottom:0 }` centra el icono cuando el botón **solo** contiene un icono (cubre los iconos de las vistas y los botones dinámicos de `departamentos.js`). Requiere `:has()` (Chrome 105+ / Safari 15.4+ / Firefox 121+); en navegadores antiguos la regla se ignora y los iconos quedan como antes (sin roturas). El botón de cerrar sesión del header (icono + `<span class="d-none d-md-inline">`) no es «solo-icono» y no entra en la regla.
- 🔧 **Clases heredadas de v3 sin definir → definidas**: `.listado`, `.claro`, `.izquierda`, `.derecha` (filas de listado de 5 vistas: competencias, cualificaciones, histórico, resultados, selección) ahora son fila flex con fondo tinte cálido; `.control-label` (etiquetas de sección de TinyMCE en `temas-contenidos-defecto`). Antes estas clases no tenían estilo en v4.
- 🏠 **Inicio funcional**: los 6 accesos rápidos de `home-view.js` ahora navegan de verdad (antes eran `<a href="#">` inoperativos): Programaciones, PCCF, Selección (Desideratas), Actas, Perfil y «Ayuda» (éste muestra un aviso con SweetAlert2: la página de ayuda es la Fase 7.5, pendiente). `app-layout.js` ahora escucha `@navigate` del `<component :is>`.
- 🧭 **Elemento de menú activo**: el item del sidebar de la vista actual queda resaltado — `app-layout.js` pasa `linkActual` al `sidebar`, que aplica `active` (CSS) al elemento cuyo `link` coincide.
- 🔧 **Estructura de listado (1 vista)**: en `cualificaciones_uc-view.js`, las asociaciones de «Asociar unidades» pasan de un único `.listado` con varios `.izquierda` (incoherente con el flex) a **una fila `.listado claro` por asociación** (patrón estándar `izquierda` + `derecha`).
- ✅ **Verificado**: `node --check` limpio en los 60 JS del frontend; los 62 recursos locales en HTTP 200; login `testadmin`/`testprofe` + `check`/`menus`/`logout` OK en vivo (Laragon).
- ⚠️ **Solo frontend** (CSS + JS de vistas/componentes); sin cambios de backend ni de datos.

### v4.4.1 — Revisión exhaustiva: simplificación, correcciones de escapado y código muerto
- 🐞 **Escapado doble (corrupción de datos)**: `pccf/guardar.php`, `pccf_apartados/guardar.php`, `pccf_contenidos_defecto/guardar.php` y `programaciones_apartados/guardar.php` escapaban el texto **antes** de `mysqli_stmt_bind_param` (`escapeString`/`real_escape_string` + `bind_param`) → los textos con `'`, `"` u `\` se guardaban doble-escapados (literal `\'`, `\\`). Ahora se enlazan los valores tal cual: la sentencia preparada ya escapa. Verificado en vivo con round-trips byte-exactos (apóstrofos, comillas y backslash).
- 🐞 **Año académico del PDF del PCCF**: `pccf/generar.php` hacía `list($anyo1, $anyo2) = cursoActual()`, pero `cursoActual()` devuelve la cadena `"2025/2026"` → años vacíos en portada e identificación. Ahora `list($anyo1, $anyo2) = explode('/', cursoActual())`. Verificado: el PDF emite `[(2025/2026)] TJ` en el apartado «Identificación».
- 🔧 **Fuga de conexión** en `departamentos/guardar.php`: `mysqli_real_escape_string(getDBConnection(), …)` abría una segunda conexión sin cerrar; ahora se escapa sobre la única conexión que se usa.
- 🗑 **Código muerto**: `getPDOConnection()` (nunca llamada) fuera de `config.php`; `api/materias/index.php` fuera de juego (solo `?action=listar` era usado y duplicaba `listar.php`; sus ramas POST/DELETE referenciaban columnas inexistentes de `materias`) — `programaciones-view.js` ahora usa `materias/listar.php` (devuelve el array directamente); `components/modales/modales.js` (`ModalConfirmacion`/`ModalMensaje`, sin uso) fuera de juego con sus registros en `app.js` y su `<script>` en `index.html`; mapa local `components` redundante fuera de `app-layout.js` (las vistas ya se registran globalmente en `app.js`), su `console.log` y `mounted()` vacío; `computed menusFiltrados` y el listener `hashchange` fuera de `sidebar.js` (v4 no usa routing por hash).
- ♻️ **Deduplicación de sesión/permisos**: el bloque repetido `@session_start(); $session = $_SESSION; if (empty($session['idUsuario'])) { 401 }` → `checkSession()` (14 archivos: `programaciones_aula/*`, `programaciones_seguimiento/*`, `temas.php`, `temas_contenidos_defecto.php`); el chequeo repetido `if ($session['rol'] === 'admin' || $session['rol'] === 'jefeDepartamento')` → `esUsuarioSuper($rol)` (helper nuevo en `config.php`, misma distinción que v3) en los 10 archivos de `programaciones_aula/*`/`programaciones_seguimiento/*` que lo usaban. Los chequeos admin/jefe y solo-admin repetidos → `checkPermission(array(…))` en `pccf/guardar.php`, `pccf_apartados/{guardar,borrar,ordenar}.php`, `pccf_contenidos_defecto/guardar.php`, `programaciones_apartados/{guardar,eliminar,ordenar}.php`, `programaciones_contenidos_defecto/guardar.php`, `cursos/{guardar,eliminar}.php`, `ciclos/{eliminar, guardar_asociacion_curso, borrar_asociacion_curso, guardar_asociacion_unidad, borrar_asociacion_unidad}.php` y `departamentos/guardar.php`. **Cambio de comportamiento (deliberado)**: el acceso **anónimo** a esos endpoints pasa de 403 a **401** «No hay sesión activa» (coherente con el resto de la API); el usuario logueado con rol insuficiente sigue recibiendo 403.
- ♻️ **Compatible PHP 5 (sin 5.6+)**: el helper `consultar()` de `pccf/generar.php` ya no usa `…` (desempaquetado de argumentos, PHP 5.6+); ahora `call_user_func_array('mysqli_stmt_bind_param', …)`. Era la única construcción que exigía 5.6; el resto del backend ya era PHP 5 (suelo efectivo 5.4 por los literales `[]`, como el resto del código).
- ✅ **Verificado en vivo** (Laragon): PDF PCCF completo y de apartado válidos con el año correcto; round-trips de escapado en los 5 endpoints afectados (HEX en BD) sin restos; matriz de roles (anónimo 401, `testprofe` 403 en escrituras jefe/admin y lecturas propias OK, `testadmin` jefe y admin real pasan); `materias/listar.php`, `programaciones/index.php` y `pccf/listar*.php` OK; todos los scripts de `index.html` en 200; `php -l` limpio en todo el backend y `node --check` limpio en el frontend.
- ⚠️ **Datos de prueba**: la clave de `testadmin` en `profesores` se normalizó a `MD5('admin1234')` (el valor previo en BD no coincidía con la contraseña documentada «admin1234» de esta sección); durante las verificaciones se cambió temporalmente `config.admin` y se restauró a `a6bdc78a74e71c67512990822c183d09` (el valor documentado). BD sin restos de prueba.

### Corrección de errores de la auditoría en vivo (B-1…B-6 + hueco de permisos)
- **B-1** `programaciones_contenidos_defecto/guardar.php`: `admin` → `jefeDepartamento || admin` (fiel a v3).
- **B-2** `configuracion.php`: ahora inicia sesión y exige `admin` (`checkPermission`) — antes no había chequeo.
- **B-3** `pccf/generar.php`: consulta de competencias sin `DISTINCT … ORDER BY` no agrupado (`GROUP BY … ORDER BY MIN(orden)`); ya no falla con `ONLY_FULL_GROUP_BY` (3087).
- **B-4** `programaciones_apartados/guardar.php`: `bind_param` corregido a `"siiisii"` (5º hueco `s` = cadena `categoria`) en INSERT y UPDATE.
- **B-5** `cualificaciones_uc.php` + `cualificaciones_uc-view.js`: edición por **código anterior** (`id`) fiel a v3 (UPDATE + join), la vista envía `id`; ya no hace `INSERT` duplicado al editar.
- **B-6** `grupos/guardar.php`: `bind_param` del UPDATE a `"ssiiiii"` (cadena `abreviatura`); además fiel a v3 el UPDATE no toca `idCurso`/`orden`, el CREATE no fija `orden` y **puebla `materias_grupos`**; `grupos/eliminar.php` borra `materias_grupos`/`programaciones_aula_temas`/`seleccion` del grupo.
- **Hueco de permisos**: `checkPermission` con el rol de v3 en `ciclos/grupos/especialidades` (admin), `materias/escenarios` (jefe/admin), `programaciones importar` (admin) y `programaciones_apartados` (jefe/admin); `historico`/`excel`/`estadisticas` exigen sesión iniciada.
- **Verificado en vivo**: matriz de roles (profe 403; jefe/admin pasan), CRUD de prueba de grupos sin restos, round-trips de apartados/cualificaciones, PDFs PCCF válidos; `php -l` limpio en los 20 archivos; BD devuelta a **idéntica al dump en las 40 tablas** (salvo `testadmin`/`testprofe`).

### Auditoría en vivo — paridad, perfiles y permisos (verificación)
- Suite en vivo contra la BD compartida (Laragon): matriz de roles/403 con los 3 perfiles (admin, `testadmin` jefe, `testprofe` profesor), round-trips de igual a igual, TinyMCE (idéntico al de v3) y permisos punto a punto.
- Detectados **6 bugs (B-1…B-6)** y el **hueco sistémico de permisos** (ciclos/grupos/materias/especialidades/escenarios, `programaciones import`, `configuracion.php`) — **sin corregir**, entregado como informe.
- **Datos**: BD restaurada y **idéntica al dump en las 40 tablas** (salvo `testadmin`/`testprofe`); eliminados los 8 restos de prueba; restaurada la contraseña `config.admin`.

### v4.4.0 — Fases 4 a 9 Completadas
- ✅ **Fase 4 — Resultados y Competencias** (backend `backend/api/resultados_aprendizaje.php`, `competencias_ciclos.php`, `cualificaciones_uc.php`; frontend `resultados_aprendizaje-view.js`, `competencias_ciclos-view.js`, `cualificaciones_uc-view.js` + clientes `api/`): RA por materia con % empresa, % evaluación, RA clave y criterios de evaluación (CRUD completo de `resultados_aprendizaje` + `criterios_evaluacion`); competencias por ciclo (CRUD + reordenar); cualificaciones profesionales y unidades de competencia con asociaciones (CRUD + `guardar/eliminar/listar_asociaciones`).
- ✅ **Fase 5 — Selección** (backend `backend/api/seleccion.php`; frontend `seleccion-view.js` + `api/seleccion.js`): selectores departamento/escenario/profesor, listado de materias, `insertar_seleccion` / `borrar_seleccion` / `borrar_toda_seleccion` / `ordenar_seleccion`, total de horas.
- ✅ **Fase 6 — Actas** (backend `backend/api/actas.php`; frontend `actas-view.js` + `api/actas.js`): `listar` / `obtener` / `guardar` de `actas_departamentos` con fecha; permisos solo admin/jefe del dpto.
- ✅ **Fase 7 — Utilidades** (backend `historico.php`, `estadisticas.php`, `configuracion.php`, `excel.php`; frontend `historico-view.js`, `estadisticas-view.js`, `configuracion-view.js`, `excel-view.js` + clientes): histórico de selecciones por profesor con conflictos, estadísticas de horas, cambio de contraseña y activaciones, exportación a CSV.
- ✅ **Fase 8 — PDFs**: TCPDF (compatible PHP 5) copiado en `backend/lib/php/tcpdf/`; `backend/pdf_acta.php` (acta de dpto, fiel a `v3/pdf_acta.php`) y `backend/pdf_seleccion.php` (selección de un profesor, reimplantación de `v3/pdf_desiderata.php`).
- ✅ **Fase 9 — Modales reutilizables** (frontend `js/components/modales/modales.js`): `ModalConfirmacion` y `ModalMensaje`, equivalentes a `modales/mensaje.php` y a las ventanas de confirmación de v3; los modales específicos se definen inline en sus vistas.
- 🔧 **Correcciones de integración**: los endpoints de las fases 4-7 ahora inician sesión con `@session_start()` y leen el cuerpo JSON vía `json_decode(file_get_contents('php://input'))` (el `$_POST` no se rellena con `Content-Type: application/json`); se captura `mysqli_insert_id` **antes** de `closeDBConnection` en `guardar`/`insertar`; todas las fases integradas en `index.html`, `app.js` y `app-layout.js`.

### v4.3.0 — Fase 3 «PCCF» Completada
- ✅ **3.1 PCCF**: backend `backend/api/pccf/{listar, listar_ciclos, guardar}.php` (PHP 5 / `mysqli_*`); `listar` carga el contenido por ciclo (o ciclo + apartado), `listar_ciclos` lista los ciclos, `guardar` inserta/actualiza/elimina fiel a v3 (`contenidos_pccf`, con texto vacío → borra fila). Frontend `pccf-view.js` + cliente `api/pccf.js` con editor TinyMCE.
- ✅ **3.2 Apartados PCCF**: backend `backend/api/pccf_apartados/{listar, obtener, guardar, borrar, ordenar}.php`; CRUD de apartados con numeración v3 (cont++/cont2++). Frontend `pccf-apartados-view.js`.
- ✅ **3.3 Cont. defecto PCCF**: backend `backend/api/pccf_contenidos_defecto/{cargar, guardar}.php`; contenido por defecto de un apartado para un departamento (fiel a v3). Frontend `pccf-contenidos-defecto-view.js`.
- ✅ **Integración**: los tres módulos están cargados en `index.html`, registrados en `app.js` y mapeados en `app-layout.js`.

### v4.2.5 — Fase 2.7 «Contenidos por Defecto de Temas» Completada
- ✅ **Backend** `backend/api/temas_contenidos_defecto.php` (PHP 5 / `mysqli_*` con sentencias preparadas; acciones `cargar` / `guardar`):
  - `cargar`: devuelve los contenidos por defecto de un departamento (`contexto`, `recursos`, `metodología`, `adaptaciones`) desde `contenidos_defcto_temas` (PK `idDepartamento`).
  - `guardar`: inserta o actualiza la fila del departamento; rol `admin` o `jefeDepartamento` (este último solo para su propio depto).
  - ⚠️ **Corrección**: la consulta hacía referencia al nombre de tabla con un typo (`contenidos_defcto_temas`); corregido para coincidir con el esquema real de v3 (`contenidos_defcto_temas`).
- ✅ **Frontend** `frontend/js/views/temas-contenidos-defecto-view.js` (+ cliente `js/api/temas-contenidos-defecto.js`): selector de departamento fiel a v3 (admin elige; jefe fijo a su propio dpto) y cuatro editores TinyMCE (Contexto / Recursos / Metodología / Adaptaciones) con la misma configuración que la 2.3. Botones «Guardar cambios» (inserta/actualiza) y «Limpiar todo».

### v4.2.4 — Fase 2.6 «Temas / Unidades de programación» Completada
- ✅ **Backend** `backend/api/temas.php` (PHP 5 / `mysqli_*` con sentencias preparadas; acciones por parámetro `action`): `listar_materias` / `listar` / `obtener`, `nuevo` / `guardar` / `borrar` (en transacción), `accordion_ra_ce`, `actualizar_ra`, `recalcular_porcentajes`, `repetir_evaluacion`.
- ✅ **Frontend** `frontend/js/views/temas-view.js` (+ cliente `js/api/temas.js`): listado por materia con control visual de sumas (%=100, horas) en verde/rojo; editor por pestañas **Datos / RA-CE** con TinyMCE fiel a v3 (`initTinyMCE('datostema', 350)`); acordeón dinámico RA→CE y botones «Repetir en resto de unidades» y «Calcular y actualizar porcentajes».

### v4.2.3 — Fase 2.5 «Seguimiento de Programaciones» Completada
- ✅ **Backend** `backend/api/programaciones_seguimiento/{profesores, materias, grupos, evaluaciones, cargar, guardar}.php`: selectores en cascada fieles a v3; `cargar`/`guardar` el registro de impartición (temporalización, resultados académicos, inclusión del alumnado). Admin guarda para cualquier profesor; un profesor solo para sí mismo.
- ✅ **Frontend** `frontend/js/views/programaciones-seguimiento-view.js`: selector en cascada + tres editores TinyMCE con la misma configuración que 2.3/2.4; botones «Guardar cambios» y «Vista previa».

### v4.2.2 — Fase 2.4 «Programación de Aula» Completada
- ✅ **Backend** `backend/api/programaciones_aula/{materias, grupos, temas, contenido, guardar}.php`: materias con programación activa, grupos, temas y texto introductorio por triplete tema+grupo+profesor desde `programaciones_aula_temas`; `guardar` inserta/actualiza y **con texto vacío borra la fila** (igual que v3).
- ✅ **Frontend** `frontend/js/views/programaciones-aula-view.js`: selector en cascada fiel a v3 + editor TinyMCE con los mismos plugins de v3; los botones PDF son stubs informativos (se activan en la Fase 8).

### v4.2.1 — Fase 2.3 «Contenidos por Defecto» Completada
- ✅ **Backend** `backend/api/programaciones_contenidos_defecto/{cargar, guardar}.php`: `cargar` devuelve el `texto` de `contenidos_defecto_programaciones` para apartado+departamento; `guardar` (solo rol `admin`) inserta/actualiza o borra con texto vacío, idéntico a v3.
- ✅ **Frontend** `frontend/js/views/programaciones-contenidos-defecto-view.js`: selector de departamento + apartado con numeración global fiel a v3 y editor TinyMCE activo; comportamiento por rol fiel a v3.
- 🔧 **Corrección**: los clientes de las fases 2.x llamaban a `backend/api/…` relativo (resolvía en `/v4/frontend/backend/api/…` → 404). Se pasa a `'../backend/api/…'` y se corrige la numeración cuando `subapartado` llega como texto (`"0"` no es falsy en JS).

### v4.2.x — Decisión B — Fase 2.1 «Programaciones» Fiel a v3
- ✅ **Reencuadre**: en v3 **no existe** la tabla `programaciones`. Se retiran crear/guardar/eliminar y la tabla ficticia. Entregables finales fieles a v3:
  - Backend `backend/api/programaciones/index.php`: `listar` (materias con programación + nº de apartados; curso vía `idCurso → cursos`), `obtener` (apartados + contenidos, solo lectura) e `importar` (conservado sobre las tablas reales v3).
  - Frontend `frontend/js/views/programaciones-view.js` + cliente `api/programaciones.js`: listado **Materia | Curso | Horas | Apartados**, modal «Ver» y modal de importación; sin create/edit/delete.

### v4.1.3 — Fase 1 Completa (Módulos básicos de mantenimiento)
- ✅ **Especialidades**, **Ciclos Formativos**, **Cursos**, **Grupos**, **Materias**, **Escenarios** implementados con CRUD completo.
- ✅ **Correcciones generales**: todos los endpoints migrados a `mysqli_*` con sentencias preparadas; APIs frontend consistentes; vistas sin errores de sintaxis; orden de carga de scripts verificado; componentes registrados.

### v4.1.2 — Fase 1.2 «Profesores» Completada
- ✅ **Profesores**: módulo completo (CRUD, filtrado por departamento, actualizar jefe de departamento, activar/desactivar, ordenar). Backend `backend/api/profesores.php`, frontend `profesores-view.js` + cliente `api/profesores.js`.

### v4.1.0 — Fase 1.1 «Departamentos» Completada
- ✅ **Departamentos**: módulo completo (CRUD). Backend `backend/api/departamentos.php`, frontend `departamentos-view.js`.

### v4.0.1 — 2025
- ✅ Migrado `mysql_*` → `mysqli_*`; login funcional usando la tabla `profesores` de v3; roles basados en `jefe_departamento`; filtrado por usuarios activos; contraseñas MD5.

### v4.0.0 — Versión inicial
- Estructura base fullstack creada; frontend Vue 3 + Bootstrap 5; sistema de autenticación básico.

---

## Registro de Decisiones Técnicas

Las decisiones importantes de diseño e implementación se documentan aquí.

#### D-2025: Fase 2.1 «Programaciones» — Decisión B, FIEL A V3 (no tabla propia)
- **Decidido por**: Usuario («FIEL a v3»).
- **Contexto**: la primera entrega de la 2.1 modelaba una tabla `programaciones` simplificada (una fila por materia/grupo con objetivos/metodología/etc.). Al contrastar con el modelo real de v3 se detectó que **no existe** esa tabla: en v3 la programación didáctica son **apartados + contenidos** asociados a cada materia (flag `materias.tiene_programacion`).
- **Decisión**: rehacer la 2.1 para ser fiel a v3. Se retiran crear/guardar/eliminar y la tabla ficticia.
- **Consecuencia**: la edición real de los apartados/contenidos se hace en las fases 2.2–2.5 (CRUD de esos módulos); la 2.1 da visibilidad fiel al estado y conserva el Importar existente.
- **Verificación**: `php -l` limpio; `listar` y `obtener` devuelven datos reales del curso en marcha.

#### D-2026: Unificación de chequeos de sesión y permisos (v4.4.1)
- **Decidido por**: Usuario («simplificar sin perder legibilidad»).
- **Contexto**: 20+ endpoints repetían a mano los mismos bloques de `@session_start()` + 401 y de chequeo de rol (admin/jefe/solo-admin), con tres formas distintas de escribirlo.
- **Decisión**: todos esos puntos usan los helpers existentes de `config.php`: `checkSession()` (401 «No hay sesión activa»), `checkPermission(array(…))` (403 «No tiene permisos para realizar esta acción») y el nuevo `esUsuarioSuper($rol)` para la distinción admin/jefe de v3.
- **Consecuencia**: el acceso **anónimo** a esos endpoints devuelve ahora **401** (antes 403, porque el rol vacío no era admin); el usuario logueado con rol insuficiente sigue recibiendo 403. Comportamiento verificable en la matriz de roles de la auditoría en vivo.
- **Verificación**: matriz de roles en vivo (anónimo 401 / `testprofe` 403 / jefe y admin OK); `php -l` limpio.

#### D-2026: «Programaciones de aula» (2.4) — opción propia de v4 (sustituye a la 2.4 fiel a v3)
- **Decidido por**: Usuario («la opción de programaciones de aula será igual que la de la propuesta pedagógica... el botón importar hará una copia para el grupo y profesor de la propuesta pedagógica correspondiente... la propuesta pedagógica no la tocaremos», más la confirmación de sustituir la antigua 2.4 y de usar selector de materia).
- **Contexto**: la 2.4 entregada fiel a v3 (texto de introducción por tema + botones PDF pendientes) no encajaba con lo pedido: una copia de trabajo de la propuesta pedagógica, por profesor + grupo, editable apartado a apartado.
- **Decisión**: sustituir la 2.4 fiel a v3 por la opción propia de v4. Se añaden la tabla `contenidos_programaciones_aula` (copia por profesor + grupo) y el flag `materias.terminada_programacion` como compuerta del importar. Se retiran el texto de introducción por tema y los botones PDF de la antigua vista (pertenecían a la Fase 8 pendiente y a la vista sustituida).
- **Consecuencia**: el ítem de menú «Programaciones de aula» (misma ruta `programaciones_aula.php`) es ahora la opción nueva; «Programaciones» se renombra a «Propuesta Pedagógica» (2.1 fiel a v3, más el interruptor «terminada» propio de v4); el importar de la 2.1 sigue siendo el de v3 (materia origen→destino).
- **Verificación**: pruebas en vivo (compuerta 400, import 16/16 filas, guardar, permisos) y `php -l`/`node --check` limpios.

#### D-2026: «Unidades» — porcentajes de evaluación de los RA por peso de unidad
- **Decidido por**: Usuario («los porcentajes que se aplican a cada RA para la evaluación se deben calcular a partir del % que se ha puesto en la unidad como peso en la evaluación anual… al final debe quedar en cada RA el porcentaje total final de la nota de cada RA de toda la asignatura teniendo en cuenta en cuantos temas influye y cuantos criterios de evaluación»). Distribución **proporcional a los CE** de cada RA en la unidad, **redondeo mayor**, y % **solo lectura** (solo lo fija «Calcular y actualizar porcentajes»).
- **Contexto**: el % de evaluación de cada RA se calculaba en `recalcular_porcentajes` (Fase 2.6, fiel a v3 `calcularPorcentajesRA`) **solo** en proporción al nº total de criterios de evaluación (CE) de cada RA sobre toda la asignatura, **sin** usar el peso de las unidades.
- **Decisión**: el % de cada RA se calcula **a partir del peso de cada unidad** (`peso_evaluacion`): el peso de una unidad se reparte entre los RA que intervienen en ella, **en proporción a los CE de cada RA en esa unidad** (`criterios_temas` / `criterios_temas_aula`). El % final de cada RA es la **suma** de su parte en cada unidad en la que interviene — un RA que influye en más unidades, y con más CE en cada una, se lleva más de la nota. Redondeo mayor (la suma puede desviarse unos puntos de 100). El % es **solo lectura** en la UI: solo lo fija «Calcular y actualizar porcentajes»; `actualizar_ra` solo cambia `es_clave` (el flag «RA/CE clave»).
- **Consecuencia**: `recalcular_porcentajes` (temas + temas_aula) se rehace con la fórmula por peso de unidad; `actualizar_ra` (temas + temas_aula) solo actualiza `es_clave`; en `temas-view.js` / `temas-aula-view.js` el % del RA pasa a **etiqueta de solo lectura** y el modal solo cambia `es_clave`.
- **Verificación**: en vivo (Laragon, `testadmin`): materia 5 → RA `12/34/15/18/10/10/0` (suma 99); copia de aula (54,7,7) → RA `24/17/19/22/19` (suma 101); ambos coinciden con el cálculo a mano; `actualizar_ra` solo toca `es_clave` (el % queda intacto); `php -l`/`node --check` limpios.

---

## Metodología de desarrollo

### Principios de diseño

1. **Bootstrap First** — Uso máximo de las clases utilitarias de Bootstrap 5.3.8; CSS personalizado solo cuando es estrictamente necesario.
2. **Iconografía** — Bootstrap Icons 1.13.1 exclusivamente; sin imágenes PNG/SVG personalizadas.
3. **Componentes Vue** — Vue 3 desde CDN (sin build step); templates como strings en `.js`; registro global; comunicación padre-hijo vía props/events.
4. **Responsive Design** — Mobile-first; sidebar colapsable en pantallas < 768px.
5. **Arquitectura Fullstack** — Backend PHP 5 devuelve JSON; frontend Vue consume APIs con `fetch()`; validación siempre en servidor.

### Patrón CRUD base

```
backend/
└── api/
    └── {modulo}/            # API REST (GET, POST, DELETE)
frontend/
├── js/
│   ├── views/
│   │   └── {modulo}-view.js    # Template Vue del módulo
│   └── api/
│       └── {modulo}.js         # Lógica de negocio (cargar, crear, editar, borrar)
```

### Convenciones de código

- **Backend PHP**: respuestas `{success: bool, data: any, message: string}`; `mysqli_*` con prepared statements; validación de permisos por rol.
- **Frontend JS**: componentes como objetos literales; eventos con `$emit()`; SweetAlert2 para feedback; iconos Bootstrap en templates.
- **Comunicación**: `fetch()` con `credentials: 'include'` (sesiones) y `'../backend/api/…'` (ruta relativa a la vista).

---

## Diferencias con v3

| v3 | v4 |
|----|-----|
| PHP monolítico | Fullstack (PHP + Vue) |
| jQuery | Vue 3 |
| Imágenes PNG para iconos | Bootstrap Icons |
| CSS personalizado extenso | Bootstrap 5.3.8 + CSS mínimo |
| AJAX con jQuery | Fetch API + JSON |
| Templates PHP | Componentes Vue |
| Funcionalidad completa | Paridad funcional completada (Fases 1–8) |

---

## Notas importantes

- La aplicación está diseñada para funcionar en servidores antiguos con PHP 5 (sin build/compilación).
- El frontend se sirve directamente desde el navegador; las sesiones se manejan desde el backend PHP.
- Se debe usar **la misma base de datos que v3** (tabla `profesores`, no `usuarios`; contraseñas en MD5).
- **Seguridad**: validar siempre en backend, escapar salidas HTML, usar `prepared statements` y verificar permisos por rol.

### Usuarios de prueba

Cuentas creadas en `gestionies.profesores` para probar sobre Laragon (usuario real, `activo=1`, depto 1). **No borrarlas de la BD sin avisar** (se usan en las verificaciones).

| Usuario | Contraseña | Rol v4 | jefatura | Notas |
|---------|-----------|--------|----------|-------|
| `testadmin` | `admin1234` | jefeDepartamento | `jefe_departamento=1` | Accede a todas las secciones de administración/jefe (guardar 2.2–2.7, 3.x, 4.x, 5.1, actas, históricos, configuración). **No** es el `admin` de la tabla `config`. |
| `testprofe` | `profesor1` | profesor | `jefe_departamento=0` | Simula un profesor: menús filtrados y 403 en escrituras de jefe/admin. |

El **`admin`** de verdad (usuario `admin` de la tabla `config`, contraseña en MD5) sigue funcionando para el login de `v4`; es el único con rol `admin` (p. ej. menús restringidos a admin). Para probar con él temporalmente se cambió la contraseña de `config` a un valor de prueba durante las verificaciones y se restauró después.

---

## Verificación de paridad con v3 (realizada)

### Cobertura de pruebas
- **Suite GET** (`/tmp/test_v4_api.sh`): 55 comprobaciones de solo lectura, todas `PASS` con el admin real y con `testadmin`; `testprofe` devuelve 403 esperados en las escrituras de jefe/admin y lee el resto de módulos como profesor.
- **Suite de escritura** (`/tmp/test_v4_write.sh`): 52 operaciones de escritura con **rollback** (filas `ZZ` distinguibles, creadas y eliminadas; `config` restaurado). Todas `PASS` con el admin real.
- **PDF** (`pdf_acta.php?idActa=1`, `pdf_seleccion.php?idProfesor=217&idEscenario=1`): HTTP 200 y PDF válido.
- **Sintaxis**: `php -l` limpio en todos los `.php` del `backend/` (salvo `lib/`); `node --check` limpio en todos los `.js` del `frontend/`.

### Compatibilidad PHP 5.6
- **Sin sintaxis PHP 7+**: 0 usos de `??`, `?->`, `fn(`, `match`, tipado de propiedades, ni builtins de 7.3+/8.x (`str_contains`, `array_key_first`, `is_countable`, etc.).
- **TCPDF** (`backend/lib/php/tcpdf/`) **idéntico byte a byte** al de v3 (mismo `diff`), por lo que su compatibilidad con PHP 5.6 es la misma que la de v3.
- La verificación de sintaxis se hace con `php -l` y `grep` porque el servidor Laragon actual sirve PHP 8.3; el objetivo de compatibilidad es PHP 5.6 (mismo criterio que v3).

### Errores latentes de v3 corregidos en v4
v4 usa el mismo esquema que v3, pero corrige varios errores latentes que en MySQL con `STRICT_TRANS_TABLES` harían fallar INSERTs:
- `ciclos.horas`, `materias.grupo`, `profesores.grupo`: columnas `NOT NULL` sin default → v4 las explícitamente al INSERT.
- `competencias_ciclos.orden`: v4 inserta `MAX(orden)+1` por ciclo (antes era omisa y fallaba).
- `escenarios/eliminar`: v3 apuntaba a la tabla equivocada / dispatcher roto → v4 elimina de `escenarios_desideratas` con `LIMIT 1`.
- `ciclos/eliminar`: v4 mantiene la comprobación real de cursos asociados antes de borrar.

### Archivos muertos eliminados
Se eliminaron ficheros sin ninguna referencia (dead code) en v4:
- `backend/api/grupos/index.php`
- `backend/api/apartados_programaciones/` (directorio huérfano; el módulo real es `programaciones_apartados/`)
- `backend/_insp.php`, `backend/test_conn.php`, `backend/dbcheck.php` (rascunes de desarrollo)

Se **conservan** los que sí se usan, aunque parezcan duplicados: `backend/api/materias/index.php` (lo referencia `programaciones-view.js`), `backend/api/programaciones/index.php` (lo referencia `api/programaciones.js`), `backend/api/pccf/generar.php` (lo referencia `pccf-view.js`), y `frontend/js/departamentos.js` (lo monta `departamentos-view.js`).

### Duplicado de TCPDF eliminado
Había **dos copias** de TCPDF en v4 (`backend/lib/php/tcpdf` y `frontend/lib/php/tcpdf`, idénticas, ~32 MB cada una). La de `frontend/` solo la usaba `backend/api/pccf/generar.php` por una ruta relativa (`__DIR__ . '/../../../frontend/…'`), además de estar en un sitio inadecuado (una lib PHP bajo `frontend/`). Se unificó todo en **`backend/lib/php/tcpdf`** (la misma que ya usaban `pdf_acta.php` y `pdf_seleccion.php`) y se eliminó `frontend/lib/php/tcpdf`. Mientras, `pccf/generar.php` dejaba un error fatal por un `return` pegado al nombre de función (`returngenerar…`); corregido. Verificado por HTTP: `pdf_acta`, `pdf_seleccion` y `pccf/generar` (modos `completo` y `apartado`) devuelven PDF válidos.

### Auditoría en vivo sobre la BD compartida (realizada)

Suite de pruebas **en vivo** contra Laragon (BD `gestionies`, la misma que v3), con los tres perfiles reales:

- **Matriz de roles / 403**: `testprofe` (profesor) recibe 403 esperados en todas las escrituras de jefe/admin (2.2–2.7, 3.x, 4.x, 5.1, actas, configuración) y lee el resto como profesor; `testadmin` (jefeDepartamento, id 217) guarda en todo **salvo** `programaciones_contenidos_defecto/guardar` (403 → bug **B-1**); el `admin` real escribe en todas partes (incluidas competencias_ciclos, cualificaciones, cursos con su chequeo de admin).
- **Round-trips de igual a igual** (leer → guardar con el mismo valor): ciclos, cursos, grupos, materias, especialidades, escenarios, competencias_ciclos, pccf, contenidos_defecto, actas, seguimiento — todas devuelven `success`; algunos expusieron los bugs de binding **B-4/B-6** y el paso NULL→0 (datos restaurados).
- **TinyMCE**: 7.9.1 local, **idéntico al de v3** (verificado por md5); los editores de todas las vistas se inicializan con los mismos plugins/ajustes que v3 (`initTinyMCE`) y el contenido viaja y vuelve por `fetch` sin pérdida.
- **Permisos**: verificación punto a punto de los endpoints de escritura contra los chequeos de v3 → detectado el hueco sistémico de permisos (ver sección siguiente).

### Bugs de v4 detectados en la auditoría en vivo (corregidos)

Hallazgos de la auditoría, **corregidos y verificados en vivo** (round-trip CREATE/UPDATE/DELETE sin restos, BD devuelta a idéntica al dump):

| # | Archivo | Descripción | v3 |
|---|---------|-------------|-----|
| B-1 | `api/programaciones_contenidos_defecto/guardar.php` | exige solo `admin` (jefe → 403), pero el menú del jefe sí muestra la página | v3: `jefeDepartamento \|\| admin` |
| B-2 | `api/configuracion.php` | **sin chequeo de sesión ni rol**: `obtener` / `actualizar_activacion` / `actualizar_password` funcionan sin cookie | v3: solo admin |
| B-3 | `api/pccf/generar.php` (línea ~167) | `SELECT DISTINCT codigo, texto … ORDER BY orden` → error 3087 de MySQL 8 (`ONLY_FULL_GROUP_BY`) → `modo=completo` siempre devuelve un PDF de 1 página con el error; `modo=apartado` tipo 4 falla, 0/7/12 OK | v3: PDF de 48 páginas válido |
| B-4 | `api/programaciones_apartados/guardar.php` (línea 54) | `bind_param(…, "siisiss", …)`: el 5º hueco `i` recibe la cadena `categoria` → siempre llega como 0 | v3: correcto |
| B-5 | `api/cualificaciones_uc.php` (`guardar_cualificacion` / `guardar_unidad`) | el frontend no envía `id` (el formulario no lo tiene) → `$id` siempre 0 → siempre `INSERT` → `Duplicate entry 'ADG082_3' for key '…PRIMARY'` al editar filas existentes | v3: actualiza por `idCualificacion` / `idUnidad` |
| B-6 | `api/grupos/guardar.php` | el `bind_param` del UPDATE usa `"siiiiii"`: la cadena `abreviatura` entra en un hueco `i` → la abreviatura se trunca a `0` en cada edición (reproducido en vivo; fila restaurada) | v3: correcto |

**Correcciones aplicadas** (verificadas en vivo):
- **B-1**: `guardar` ahora acepta `jefeDepartamento || admin` (igual que v3). Verificado: jefe ya no recibe 403.
- **B-2**: `configuracion.php` ahora inicia sesión y exige `admin` (`checkPermission`). Verificado: admin 200, profesor/jefe 403.
- **B-3**: la consulta de competencias de `pccf/generar.php` ya no usa `DISTINCT … ORDER BY` no agrupado: `GROUP BY codigo, texto ORDER BY MIN(orden)`. Verificado: `modo=completo` y `modo=apartado` devuelven PDF válidos (0 errores 3087).
- **B-4**: el `bind_param` de `programaciones_apartados/guardar.php` (INSERT y UPDATE) corregido a `"siiisii"` (el 5º hueco `s` ahora recibe la cadena `categoria`). Verificado: `categoria` (`ESO/BACH`, `TODOS`) se guarda íntegra en CREATE y UPDATE.
- **B-5**: `cualificaciones_uc.php` ahora edita por el **código anterior** (`id`), fiel a v3 (`WHERE codigo=<id>` + actualización del join `cualificaciones_unidades` / `unidades_ciclos` si el código cambia); la vista (`cualificaciones_uc-view.js`) envía el `id` (código anterior) al editar. Verificado: editar una fila existente hace `UPDATE` (sin duplicado) y una nueva hace `INSERT`.
- **B-6**: el `bind_param` del UPDATE de `grupos/guardar.php` corregido a `"ssiiiii"` (la cadena `abreviatura` ya no entra en un hueco `i`). Además, fiel a v3: el UPDATE ya no sobreescribe `idCurso`/`orden` (el `orden` lo gestiona la reordenación), el CREATE no fija `orden` y **puebla `materias_grupos`** con las materias del curso; `grupos/eliminar.php` borra también `materias_grupos`, `programaciones_aula_temas` y `seleccion` del grupo (evita huérfanas). Verificado: CREATE/UPDATE/DELETE de un grupo de prueba sin restos.

### Hueco sistémico de permisos (v4) — corregido

v3 protegía todos estos endpoints con sesión + rol; en v4 **no había chequeo** y un usuario sin rol (e incluso un `POST` anónimo) podía escribir. **Corregido**: cada endpoint ahora aplica `checkPermission` con el rol exacto de v3. Verificado en vivo (profesor 403; jefe/admin pasan la puerta y llegan a la validación):

| Endpoint | Rol en v3 | Chequeo en v4 (tras corrección) |
|----------|----------|---------------------------------|
| `ciclos/guardar` / `ciclos/eliminar` | admin | `admin` ✅ |
| `grupos/guardar` / `grupos/eliminar` | admin | `admin` ✅ |
| `materias/guardar` / `materias/eliminar` | jefe o admin | `jefeDepartamento \|\| admin` ✅ |
| `especialidades/guardar` / `especialidades/eliminar` | admin | `admin` ✅ |
| `escenarios/guardar` / `escenarios/eliminar` | jefe o admin | `jefeDepartamento \|\| admin` ✅ |
| `programaciones/index.php` (`importar`) | admin | `admin` ✅ (es **destructivo**: `DELETE` del destino + copia del origen; ahora solo admin) |
| `configuracion.php` | admin | `admin` ✅ (ver B-2) |
| `programaciones_contenidos_defecto/guardar` / `programaciones_apartados/{guardar,ordenar,eliminar}` | jefe o admin | `jefeDepartamento \|\| admin` ✅ (antes solo `admin` → ver B-1) |

`cursos/guardar.php` ya hacía su chequeo de admin. **Exposición de solo lectura**: los `listar`/`obtener`/`cargar` y `pccf/generar.php` quedan **sin sesión, fiel a v3** (los handlers `cargar_*` de v3 tampoco la inician; `pdf_pccf.php` de v3 es de navegador directo). Los endpoints de página `historico.php`, `excel.php` y `estadisticas.php` ahora exigen **sesión iniciada** (`checkSession`, 401) porque en v3 eran páginas con cabecera (login). `temas.php` sin chequeo de rol es **fiel a v3** (OK).

### Coherencia de los datos (BD compartida)

- Auditoría de las **40 tablas** de `gestionies.sql` contra la BD en vivo (cuentas + hash de contenido fila a fila): **40/40 idénticas** salvo `profesores` (los usuarios de prueba documentados 217/218).
- Las pruebas expusieron y se **restauraron desde el dump** las filas tocadas por los guardar de v4: `grupos.id=1` (abreviatura `P`, `orden` `NULL`), `especialidades.id='ADE'` (horas `NULL`), `actas_departamentos.id=1` (texto), `contenidos_defecto_temas` deptos 1 y 2 (recursos/metodología/adaptaciones) y, de la sesión previa, pccf `2,6`, `cdpccf` 1–2, `apartados_pccf.id=6` y la fila 6 de `apartados_programaciones`.
- **Restos de prueba eliminados** (filas ausentes del dump, inequívocamente de prueba): `apartados_pccf` «TEST APARTADO», `resultados_aprendizaje` «RA de prueba v4», 2 filas de `seguimiento_programaciones_aula`, 3 filas de `seleccion`, `temas` «Tema prueba v2.6». Se conservan `testadmin`/`testprofe` (necesarios para las verificaciones).
- `config.admin` **restaurado** a la contraseña original (MD5 `a6bdc78a74e71c67512990822c183d09`) tras el cambio temporal de las pruebas.

### Notas de paridad
- **Menú «Selección»**: v3 **no** bloquea la vista de selección en función de `desideratas` (v3/`seleccion.php` no usa `$desideratasActivadas`), por lo que v4 tampoco la bloquea; la activación solo afecta a la edición, igual que en v3.
- **Seguridad de datos**: se restauraron desde `gestionies.sql` todas las filas de prueba creadas durante las verificaciones (filas `ZZ` y las 8 filas restantes eliminadas en la auditoría en vivo; ver «Coherencia de los datos») y se corrigió un borrado accidental. La BD compartida quedó **idéntica al dump en las 40 tablas** (salvo los usuarios de prueba documentados) y no quedan restos de datos de prueba.
