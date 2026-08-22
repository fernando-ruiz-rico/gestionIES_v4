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
│   ├── pdf_resultados_aprendizaje.php # Fase 8 (PDFs de RA/CE empresa, TCPDF)
│   ├── pdf_seleccion.php             # Fase 8 (PDF de la selección, TCPDF)
│   ├── pdf_programaciones.php        # Fase 2.1 (PDF completo de la programación, TCPDF)
│   ├── pdf_programaciones_apartado.php # Fase 2.1 (PDF de un apartado, TCPDF)
│   ├── pdf_unidades_programacion.php # Fase 2.1 (PDF de unidades/temas, TCPDF)
│   └── lib/                          # lib/php/tcpdf (TCPDF, desde v3) + lib/programaciones_pdf.php
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
| **Fase 8 – PDFs** | ✅ | N/A | Completado (TCPDF: actas, selección y RA/CE empresa) |
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

### Fase 8: Generación de PDFs (Completado con matices)

Endpoints que generan PDF con TCPDF (compatible PHP 5, copiado en `backend/lib/php/tcpdf/`):

- `backend/pdf_acta.php` — PDF del acta de departamento (`?idActa=X`), fiel a `v3/pdf_acta.php`. ✅ Verificado en vivo (≈27 KB, el de v3 hace 27 KB).
- `backend/pdf_seleccion.php` — PDF de la selección de materias de un profesor (`?idProfesor=X&idEscenario=Y`), reimplantación funcional de `v3/pdf_desiderata.php`. ✅ Verificado en vivo (≈7,5 KB).
- `backend/api/pccf/generar.php` — PDF del PCCF (`modo=completo` / `modo=apartado`). ⚠️ Con MySQL 8 (`ONLY_FULL_GROUP_BY`) el modo `completo` genera siempre un PDF de una página con el error SQL 3087 (ver bug **B-3**); en `modo=apartado` funcionan los tipos 0/7/12 y falla el tipo 4.
- `backend/pdf_programaciones.php` — **PDF completo** de la programación de una materia (`?idMateria=X`), Fase 2.1, fiel a `v3/pdf_programaciones.php` (portada + índice con TOC + apartados con `Bookmark`, FE en página propia, temas en su página). ✅ Verificado en vivo.
- `backend/pdf_programaciones_apartado.php` — **PDF de un apartado** (`?idMateria=X&idApartado=Y`), el apartado pedido + sus subapartados hasta el siguiente principal, fiel a v3. ✅ Verificado en vivo.
- `backend/pdf_unidades_programacion.php` — **PDF de unidades/temas** (`?idMateria=X`, una página por tema), fiel a `v3/pdf_unidades_programacion.php`. El «PDF de Apartado» de la vista enruta aquí si el apartado es de temas (`tipo = 13`), igual que v3. ✅ Verificado en vivo.

> Los botones PDF de `programaciones-aula` y `programaciones-seguimiento` siguen siendo stubs informativos (igual que en la entrega 2.4/2.5); los PDFs de la Fase 2 se abren desde sus vistas (los de «Programaciones» ahora son los 3 de arriba, y el de «Resultados de aprendizaje» el de la empresa).

### Fase 9: Características Avanzadas (Parcial)

- ✅ Edición de temas con accordion RA/CE (entregado en la Fase 2.6)
- ➖ Modales reutilizables: `ModalConfirmacion`/`ModalMensaje` (v4.4.0) se retiraron en v4.4.1 por no usarse en ninguna vista; los modales específicos de cada módulo se definen inline en sus vistas (los genéricos se resuelven con SweetAlert2).
- ✅ Sistema de activaciones (ON/OFF de `programaciones` y `desideratas`): lo cubre la Fase 7.3 en `configuracion.php` (el frontend envía `evaluacionRA`/`seleccion`; se mapea a las filas `programaciones`/`desideratas`, mismo modelo que v3)
- ⬜ Copia de seguridad y restauración
- ⬜ Importación/exportación de datos (parcial: la exportación a CSV en `excel-view.js` cubre la exportación; la importación queda pendiente)

---

## Historial de cambios

> Registro cronológico (más reciente primero) de las entregas por versión.

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
