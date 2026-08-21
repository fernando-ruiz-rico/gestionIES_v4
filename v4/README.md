# GestionIES v4

Aplicación fullstack para la gestión interna de centros educativos (IESSV). Reimplementación de `v3` con **backend PHP 5 + frontend Vue 3** (sin build step), comunicándose vía JSON.

## Tabla de contenido

- [Estructura del proyecto](#estructura-del-proyecto)
- [Tecnologías](#tecnologías)
- [Requisitos del servidor](#requisitos-del-servidor)
- [Instalación](#instalación)
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
│       └── excel.php                   # Fase 7.4
│   ├── pdf_acta.php                  # Fase 8 (PDF del acta, TCPDF)
│   ├── pdf_seleccion.php             # Fase 8 (PDF de la selección, TCPDF)
│   └── lib/php/tcpdf/                # Fase 8 (TCPDF, copiado desde v3)
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
        │   ├── header-bar.js      # Barra superior
        │   └── modales/modales.js # Fase 9 (ModalConfirmacion, ModalMensaje)
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
| 2.1 Programaciones (fiel a v3) | ✅ | ✅ | Completado |
| 2.2 Apartados de programación | ✅ | ✅ | Completado |
| 2.3 Contenidos por defecto | ✅ | ✅ | Completado |
| 2.4 Programación de aula | ✅ | ✅ | Completado |
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
| **Fase 8 – PDFs** | ✅ | N/A | Completado (TCPDF: actas y selección) |
| **Fase 9 – Características Avanzadas** | | | |
| Edición de temas con accordion RA/CE | ✅ | ✅ | Completado (Fase 2.6) |
| Modales reutilizables | ✅ | ✅ | Completado |
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

> **Modelo fiel a v3**: en v3 **no existe** la tabla `programaciones`. La programación vive en `apartados_programaciones` + `contenidos_programaciones` asociados a cada materia (flag `materias.tiene_programacion`); el curso se resuelve con `materias.idCurso → cursos`.

| Módulo | Backend | Frontend | Referencia v3 | Estado |
|--------|---------|----------|---------------|:------:|
| 2.1 Programaciones | `backend/api/programaciones/` | `programaciones-view.js` + `api/programaciones.js` | `programaciones.php`, `ajax/programaciones/`, `modales/importar_programacion.php` | Completado |
| 2.2 Apartados | `backend/api/programaciones_apartados/` | `programaciones-apartados-view.js` | `programaciones_apartados.php`, `ajax/programaciones_apartados/` | Completado |
| 2.3 Cont. defecto | `backend/api/programaciones_contenidos_defecto/` | `programaciones-contenidos-defecto-view.js` | `programaciones_contenidos_defecto.php`, `ajax/programaciones_contenidos_defecto/` | Completado |
| 2.4 Programación de aula | `backend/api/programaciones_aula/` | `programaciones-aula-view.js` | `programaciones_aula.php`, `ajax/programaciones_aula/` | Completado |
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
| 6.1 Actas de Evaluación | `backend/api/actas.php` | `actas-view.js` + `api/actas.js` | `actas.php`, `ajax/actas/` |

### Fase 7: Utilidades y Reportes (Completado)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 7.1 Histórico | `backend/api/historico.php` | `historico-view.js` + `api/historico.js` | `historico.php` |
| 7.2 Estadísticas | `backend/api/estadisticas.php` | `estadisticas-view.js` + `api/historico.js` (EstadisticasAPI) | `estadisticas.php` |
| 7.3 Configuración | `backend/api/configuracion.php` | `configuracion-view.js` + `api/configuracion.js` | `configuracion.php` |
| 7.4 Exportación a Excel | `backend/api/excel.php` | `excel-view.js` | `excel.php` |
| 7.5 Ayuda | — | — | Pendiente (página estática de ayuda) |

### Fase 8: Generación de PDFs (Completado)

Endpoints que generan PDF con TCPDF (compatible PHP 5, copiado en `backend/lib/php/tcpdf/`):

- `backend/pdf_acta.php` — PDF del acta de departamento (`?idActa=X`), fiel a `v3/pdf_acta.php`.
- `backend/pdf_seleccion.php` — PDF de la selección de materias de un profesor (`?idProfesor=X&idEscenario=Y`), reimplantación funcional de `v3/pdf_desiderata.php`.

> Los documentos de planificación (programaciones, PCCF, separata de CE, etc.) ya se generan con el flujo de la app; los PDFs de los módulos de la Fase 2 se siguen abriendo desde sus vistas.

### Fase 9: Características Avanzadas (Parcial)

- ✅ Edición de temas con accordion RA/CE (entregado en la Fase 2.6)
- ✅ Modales reutilizables: `frontend/js/components/modales/modales.js` (`ModalConfirmacion`, `ModalMensaje`), equivalentes a `modales/mensaje.php` y a las ventanas de confirmación de v3; los modales específicos de cada módulo se definen inline en sus vistas.
- ✅ Sistema de activaciones (ON/OFF de `programaciones` y `desideratas`): lo cubre la Fase 7.3 en `configuracion.php` (el frontend envía `evaluacionRA`/`seleccion`; se mapea a las filas `programaciones`/`desideratas`, mismo modelo que v3)
- ⬜ Copia de seguridad y restauración
- ⬜ Importación/exportación de datos (parcial: la exportación a CSV en `excel-view.js` cubre la exportación; la importación queda pendiente)

---

## Historial de cambios

> Registro cronológico (más reciente primero) de las entregas por versión.

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

### Notas de paridad
- **Menú «Selección»**: v3 **no** bloquea la vista de selección en función de `desideratas` (v3/`seleccion.php` no usa `$desideratasActivadas`), por lo que v4 tampoco la bloquea; la activación solo afecta a la edición, igual que en v3.
- **Seguridad de datos**: se restauraron desde `gestionies.sql` todas las filas de prueba `ZZ` creadas durante las verificaciones, y se corrigió (restaurando la fila real `apartados_pccf.id=1`) un borrado accidental detectado durante las pruebas. No quedan restos de datos de prueba en la BD compartida.
