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
│   ├── config.php     # Configuración y funciones comunes
│   ├── create_table.sql
│   ├── dbcheck.php    # Diagnóstico de conexión/esquema
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
│       ├── temas/                 # Fase 2.6
│       ├── temas_contenidos_defecto.php         # Fase 2.7
│       ├── pccf/                  # Fase 3.1
│       ├── pccf_apartados/        # Fase 3.2
│       └── pccf_contenidos_defecto/# Fase 3.3
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
| 4.1 Resultados de Aprendizaje | ❌ | ❌ | Pendiente |
| 4.2 Competencias por Ciclo | ❌ | ❌ | Pendiente |
| 4.3 Cualificaciones y UC | ❌ | ❌ | Pendiente |
| **Fase 5 – Selección** | | | |
| 5.1 Selección de Destinos | ❌ | ❌ | Pendiente |
| **Fase 6 – Actas** | | | |
| 6.1 Actas de Evaluación | ❌ | ❌ | Pendiente |
| **Fase 7 – Utilidades y Reportes** | | | |
| 7.1 Histórico | ❌ | ❌ | Pendiente |
| 7.2 Estadísticas | ❌ | ❌ | Pendiente |
| 7.3 Configuración | ❌ | ❌ | Pendiente |
| 7.4 Exportación a Excel | ❌ | N/A | Pendiente |
| 7.5 Ayuda | ❌ | ❌ | Pendiente |
| **Fase 8 – PDFs** | ❌ | N/A | Pendiente |
| **Fase 9 – Características Avanzadas** | | | |
| Edición de temas con accordion RA/CE | ✅ | ✅ | Completado (Fase 2.6) |
| Resto de características avanzadas | ❌ | ❌ | Pendiente |

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
| 3.1 PCCF | `backend/api/pccf/{listar, listar_ciclos, guardar}.php` | `pccf-view.js` + `api/pccf.js` | `pccf.php`, `ajax/pccf/` | Completado |
| 3.2 Apartados PCCF | `backend/api/pccf_apartados/{listar, obtener, guardar, borrar, ordenar}.php` | `pccf-apartados-view.js` | `pccf_apartados.php`, `ajax/pccf_apartados/` | Completado |
| 3.3 Cont. defecto PCCF | `backend/api/pccf_contenidos_defecto/{cargar, guardar}.php` | `pccf-contenidos-defecto-view.js` | `pccf_contenidos_defecto.php`, `ajax/pccf_contenidos_defecto/` | Completado |

### Fase 4: Resultados de Aprendizaje y Competencias (Pendiente)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 4.1 Resultados de Aprendizaje | `backend/api/resultados_aprendizaje.php` | `resultados-aprendizaje-view.js` | `resultados_aprendizaje.php`, `ajax/resultados_aprendizaje/` |
| 4.2 Competencias por Ciclo | `backend/api/competencias_ciclos.php` | `competencias-ciclos-view.js` | `competencias_ciclos.php`, `ajax/competencias_ciclos/` |
| 4.3 Cualificaciones y UC | `backend/api/cualificaciones_uc.php` | `cualificaciones-uc-view.js` | `cualificaciones_uc.php`, `ajax/cualificaciones_uc/` |

### Fase 5: Selección y Asignaciones (Pendiente)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 5.1 Selección de Destinos | `backend/api/seleccion.php` | `seleccion-view.js` | `seleccion.php`, `ajax/seleccion/` |

### Fase 6: Actas y Evaluación (Pendiente)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 6.1 Actas de Evaluación | `backend/api/actas.php` | `actas-view.js` | `actas.php`, `ajax/actas/` |

### Fase 7: Utilidades y Reportes (Pendiente)

| Módulo | Backend | Frontend | Referencia v3 |
|--------|---------|----------|---------------|
| 7.1 Histórico | `backend/api/historico.php` | `historico-view.js` | `historico.php` |
| 7.2 Estadísticas | `backend/api/estadisticas.php` | `estadisticas-view.js` | `estadisticas.php` |
| 7.3 Configuración | `backend/api/configuracion.php` | `configuracion-view.js` | `configuracion.php` |
| 7.4 Exportación a Excel | `backend/api/excel.php` | — | `excel.php` |
| 7.5 Ayuda | — | `ayuda-view.js` | `ayuda.php`, `docs/Manual_*.md` |

### Fase 8: Generación de PDFs (Pendiente)

Implementar endpoints usando una librería compatible con PHP 5. Referencias a migrar desde `v3/`:

`pdf_acta.php`, `pdf_desiderata.php`, `pdf_pccf.php`, `pdf_preferencias.php`, `pdf_programaciones.php`, `pdf_programaciones_apartado.php`, `pdf_programaciones_aula.php`, `pdf_programaciones_seguimiento.php`, `pdf_separata_ce.php`, `pdf_unidades_programacion.php`, `listado_programaciones.php`, `listado_programaciones_simple.php`, `listado_urls_pdfs.php`.

### Fase 9: Características Avanzadas (Parcial)

- ✅ Edición de temas con accordion RA/CE (entregado en la Fase 2.6)
- ⬜ Modales reutilizables (migrar desde `modales/` de v3)
- ⬜ Sistema de activaciones por curso académico
- ⬜ Copia de seguridad y restauración
- ⬜ Importación/exportación de datos

---

## Historial de cambios

> Registro cronológico (más reciente primero) de las entregas por versión.

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
| Funcionalidad completa | En desarrollo (Fases 1, 2.1–2.7 y 3 completas) |

---

## Notas importantes

- La aplicación está diseñada para funcionar en servidores antiguos con PHP 5 (sin build/compilación).
- El frontend se sirve directamente desde el navegador; las sesiones se manejan desde el backend PHP.
- Se debe usar **la misma base de datos que v3** (tabla `profesores`, no `usuarios`; contraseñas en MD5).
- **Seguridad**: validar siempre en backend, escapar salidas HTML, usar `prepared statements` y verificar permisos por rol.

### Usuarios de prueba

Cuentas creadas en `gestionies.profesores` para probar las fases 2.x sobre Laragon (usuario real, `activo=1`, depto 1). **Borrarlas una vez comprobado**:

| Usuario | Contraseña | Rol (v4) | jefatura | Notas |
|---------|-----------|----------|----------|-------|
| `testadmin` | `admin1234` | admin | `jefe_departamento=1` | Permite acceder a las secciones con permisos (guardar 2.2/2.3, menús de administración) |
| `testprofe` | `profesor1` | profesor | `jefe_departamento=0` | Simula un profesor: sin acceso al menú «Contenidos generales» y 403 en `guardar` |
