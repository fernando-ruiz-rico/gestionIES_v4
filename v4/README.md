# GestionIES v4

Aplicación fullstack para la gestión interna de centros educativos (IESSV).

## Estructura del proyecto

```
v4/
├── backend/           # API PHP 5
│   ├── config.php     # Configuración y funciones comunes
│   └── api/           # Endpoints de la API
│       ├── auth.php   # Autenticación (login, logout, check)
│       └── app.php    # Datos de la aplicación (menús, activaciones)
│
└── frontend/          # Aplicación Vue 3
    ├── index.html     # Punto de entrada (acceder directamente)
    ├── css/
    │   └── app.css    # Estilos personalizados mínimos
    └── js/
        ├── app.js                 # Aplicación principal Vue 3
        ├── api/
        │   ├── auth.js            # API de autenticación
        │   └── app.js             # API de datos de la aplicación
        ├── components/
        │   ├── login-view.js      # Componente de login
        │   ├── app-layout.js      # Layout principal
        │   ├── sidebar.js         # Menú lateral
        │   └── header-bar.js      # Barra superior
        └── views/
            └── home-view.js       # Página de inicio
```

## Tecnologías utilizadas

### Backend
- **PHP 5** compatible con servidores antiguos (Apache ~2010)
- **MySQL** con extensiones `mysql_*` (nativas de PHP 5)
- **JSON** para comunicación con el frontend
- Sesiones PHP para autenticación

### Frontend
- **Vue 3** (desde CDN, sin compilación)
- **Bootstrap 5.3.8** para estilos responsive
- **Bootstrap Icons 1.13.1** para iconos
- **SweetAlert2** para mensajes y confirmaciones
- CSS personalizado mínimo basado en Bootstrap

## Requisitos del servidor

- PHP 5.x (compatible con versiones antiguas)
- MySQL/MariaDB
- Apache con módulo mod_rewrite (opcional)
- No requiere Node.js ni procesos de compilación

## Instalación

1. Subir la carpeta `v4` al servidor web

2. Configurar la base de datos en `backend/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gestionies');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. Asegurarse de que la base de datos tiene la tabla `usuarios` con la estructura:
   ```sql
   CREATE TABLE usuarios (
       idUsuario INT PRIMARY KEY AUTO_INCREMENT,
       loginUsuario VARCHAR(50),
       password VARCHAR(255),
       rol VARCHAR(50),
       nombre VARCHAR(100),
       apellidos VARCHAR(100)
   );
   ```

4. Acceder directamente a la carpeta frontend:
   ```
   http://tudominio.com/v4/frontend/
   ```

## Características

### Responsive Design
- La aplicación se adapta a dispositivos móviles y tablets
- Menú lateral colapsable en pantallas pequeñas
- Uso de clases utilitarias de Bootstrap 5

### Iconos
- Todos los iconos son de Bootstrap Icons (sin imágenes)
- Iconos semánticos para cada sección del menú

### CSS Mínimo
- Se utiliza al máximo el CSS de Bootstrap 5.3.8
- CSS personalizado solo para:
  - Layout del sidebar
  - Colores corporativos
  - Ajustes específicos de la aplicación

### Fullstack Architecture
- Backend PHP devuelve JSON
- Frontend Vue 3 consume APIs
- Separación clara de responsabilidades
- Fácil mantenimiento y escalabilidad

## Funcionalidades implementadas

- ✅ Login de usuario
- ✅ Logout
- ✅ Comprobación de sesión activa
- ✅ Menú lateral con submenús
- ✅ Filtrado de menús por rol de usuario
- ✅ Página de inicio con accesos rápidos
- ✅ Interfaz responsive
- ✅ Mensajes de feedback (SweetAlert2)

## Planificación de trabajo pendiente

Para completar la funcionalidad de v3 en v4, se debe migrar toda la aplicación siguiendo esta hoja de ruta:

### Fase 1: Módulos básicos de mantenimiento (PRIORIDAD ALTA)

Estos módulos son la base del sistema y deben implementarse primero:

#### 1.1 Departamentos
- **Backend**: `backend/api/departamentos.php`
  - Listar departamentos
  - Crear departamento
  - Editar departamento
  - Eliminar departamento
- **Frontend**: `frontend/js/views/departamentos-view.js`
- **Referencia v3**: `departamentos.php`, `ajax/departamentos/`

#### 1.2 Profesores
- **Backend**: `backend/api/profesores.php`
  - Listar profesores (filtrados por departamento)
  - Crear profesor
  - Editar profesor
  - Eliminar profesor
  - Actualizar jefe de departamento
  - Activar/desactivar profesor
  - Ordenar profesores
- **Frontend**: `frontend/js/views/profesores-view.js`
- **Referencia v3**: `profesores.php`, `ajax/profesores/`

#### 1.3 Especialidades
- **Backend**: `backend/api/especialidades.php`
  - CRUD completo de especialidades
- **Frontend**: `frontend/js/views/especialidades-view.js`
- **Referencia v3**: `especialidades.php`, `ajax/especialidades/`

#### 1.4 Ciclos Formativos
- **Backend**: `backend/api/ciclos.php`
  - CRUD de ciclos
  - Asociar cursos a ciclos
  - Asociar unidades a ciclos
- **Frontend**: `frontend/js/views/ciclos-view.js`
- **Referencia v3**: `ciclos.php`, `ajax/ciclos/`

#### 1.5 Cursos
- **Backend**: `backend/api/cursos.php`
  - CRUD de cursos
  - Gestión de categorías (ESO/BACH/FP/OTROS)
- **Frontend**: `frontend/js/views/cursos-view.js`
- **Referencia v3**: `cursos.php`, `ajax/cursos/`

#### 1.6 Grupos
- **Backend**: `backend/api/grupos.php`
  - CRUD de grupos
  - Ordenar grupos
- **Frontend**: `frontend/js/views/grupos-view.js`
- **Referencia v3**: `grupos.php`, `ajax/grupos/`

#### 1.7 Materias
- **Backend**: `backend/api/materias.php`
  - CRUD de materias
  - Asociar materias a grupos
  - Gestionar horas empresa
- **Frontend**: `frontend/js/views/materias-view.js`
- **Referencia v3**: `materias.php`, `ajax/materias/`

#### 1.8 Escenarios
- **Backend**: `backend/api/escenarios.php`
  - CRUD de escenarios
- **Frontend**: `frontend/js/views/escenarios-view.js`
- **Referencia v3**: `escenarios.php`, `ajax/escenarios/`

### Fase 2: Programaciones Didácticas (PRIORIDAD MEDIA)

#### 2.1 Programaciones — Fiel a v3 (Decisión B)
- **Backend**: `backend/api/programaciones/index.php`
  - ✅ Listar materias con programación activa + estado real (nº de apartados)
  - ✅ Ver programación por materia: sus apartados + contenidos (solo lectura)
  - ✅ Importar programación desde otra materia (conservado; opera sobre las tablas reales v3)
  - ⚠️ Sin crear/guardar/eliminar una fila única: en v3 no existe tabla `programaciones` — la programación vive en apartados + contenidos (edición en 2.2–2.5)
- **Frontend**: `frontend/js/views/programaciones-view.js` (+ cliente `frontend/js/api/programaciones.js`)
  - ✅ Listado por materia (Materia | Curso | Horas | Apartados) con filtro por materia
  - ✅ Modal «Ver» solo lectura de apartados + contenidos
  - ✅ Modal de importación con confirmación (conservado)
- **Modelo de datos real (fiel a v3)**: no hay tabla `programaciones`. La programación = `apartados_programaciones` + `contenidos_programaciones` asociados a cada materia (flag `materias.tiene_programacion`). El curso se resuelve con `materias.idCurso → cursos`.
- **Referencia v3**: `programaciones.php`, `ajax/programaciones/`, `modales/importar_programacion.php`
- **Estado**: ✅ Completado (Decisión B — FIEL a v3). Ver [Registro de Decisiones Técnicas](#registro-de-decisiones-técnicas).

#### 2.2 Apartados de Programación
- **Backend**: `backend/api/programaciones_apartados.php`
  - CRUD de apartados
  - Generar apartados por defecto
- **Frontend**: `frontend/js/views/programaciones-apartados-view.js`
- **Referencia v3**: `programaciones_apartados.php`, `ajax/programaciones_apartados/`

#### 2.3 Contenidos por Defecto
- **Backend**: `backend/api/programaciones_contenidos_defecto/{cargar,guardar}.php`
  - ✅ Cargar el contenido por defecto de un apartado para un departamento (`cargar.php`)
  - ✅ Guardar: inserta o actualiza; con texto vacío borra la fila, idéntico a v3 (`guardar.php`, solo rol `admin`)
- **Frontend**: `frontend/js/views/programaciones-contenidos-defecto-view.js` (+ cliente `frontend/js/api/programaciones-contenidos-defecto.js`)
  - ✅ Selector de departamento + apartado (solo los que admiten contenido por defecto, numeración fiel a v3) y editor con botones Limpiar/Guardar
- **Referencia v3**: `programaciones_contenidos_defecto.php`, `ajax/programaciones_contenidos_defecto/`
- **Estado**: ✅ Completado (v4.2.1 — FIEL a v3). Ver [Historial de cambios](#historial-de-cambios).

#### 2.4 Programación de Aula
- **Backend**: `backend/api/programaciones_aula/{materias,grupos,temas,contenido,guardar}.php`
  - ✅ Listar materias con programación activa para un profesor (admin elige profesor; uno usa el suyo)
  - ✅ Cargar grupos asignados en la selección actual del curso
  - ✅ Cargar temas (unidades) de una materia
  - ✅ Cargar/guardar texto introductorio (TinyMCE) por tema+grupo+profesor en `programaciones_aula_temas`
- **Frontend**: `frontend/js/views/programaciones-aula-view.js` (+ cliente `js/api/programaciones-aula.js`)
  - ✅ Selector de profesor (solo admin) + materia + grupo
  - ✅ Editor TinyMCE con los mismos plugins que v3 (`autolink lists advlist code fullscreen wordcount`) y CSS propio
  - ✅ Guardar / borrar (texto vacío → fila eliminada, igual que v3)
  - Botones PDF separata CE y programación de aula (pendiente Fase 8): muestran aviso informativo
- **Referencia v3**: `programaciones_aula.php`, `ajax/programaciones_aula/{cargar_grupos,cargar_temas,cargar_contenido_programacion,insertar_contenido_programacion}.php`
- **Estado**: ✅ Completado. Ver [Historial de cambios](#historial-de-cambios).

#### 2.5 Seguimiento de Programaciones
- **Backend**: `backend/api/programaciones_seguimiento/{profesores,materias,grupos,evaluaciones,cargar,guardar}.php`
  - ✅ Selectores en cascada fieles a v3: profesor (solo admin; uno el suyo) → materia → grupo → evaluación (`profesores`, `materias`, `grupos`, `evaluaciones`)
  - ✅ Cargar registro de impartición por profesor+materia+grupo+evaluación (`cargar`)
  - ✅ Guardar registro: temporalización + resultados académicos + inclusión del alumnado (`guardar`), inserta/actualiza
- **Frontend**: `frontend/js/views/programaciones-seguimiento-view.js` (+ cliente `frontend/js/api/programaciones-seguimiento.js`)
  - ✅ Selector en cascada fiel a v3: profesor (solo admin) + materia + grupo + evaluación
  - ✅ Tres editores TinyMCE WYSIWYG con la misma configuración que 2.3/2.4: temporalización, resultados académicos e inclusión del alumnado
  - ✅ Botones «Guardar cambios» y «Vista previa» (modal con las tres secciones renderizadas)
- **Referencia v3**: `programaciones_seguimiento.php`, `ajax/programaciones_seguimiento/`
- **Estado**: ✅ Completado. Ver [Historial de cambios](#historial-de-cambios).

#### 2.6 Temas (Unidades de programación)
- **Backend**: `backend/api/temas.php` (PHP 5 / `mysqli_*` con sentencias preparadas)
  - ✅ CRUD de temas/unidades: `listar_materias`, `listar`, `obtener`, `nuevo`, `guardar`, `borrar`
  - ✅ Cargar RA/CE por materia (`accordion_ra_ce`) y editar un RA (`actualizar_ra`)
  - ✅ Recalcular porcentajes de evaluación de los RA (`recalcular_porcentajes`)
  - ✅ Repetir el campo «Evaluación» en el resto de unidades de la materia (`repetir_evaluacion`)
- **Frontend**: `frontend/js/views/temas-view.js` (+ cliente `frontend/js/api/temas.js`)
  - ✅ Listado de temas por materia (orden, título, % y horas) con control visual de sumas (%=100, horas anuales) en verde/rojo
  - ✅ Editor por pestañas (Datos / RA-CE) con editor TinyMCE fiel a v3 (`initTinyMCE('datostema', 350)`), «Dejar valores por defecto» por campo
  - ✅ Acordeón dinámico RA→CE con checkboxes, modal de edición de RA (% + clave) y suma controlada
  - ✅ Botones «Repetir en resto de unidades» y «Calcular y actualizar porcentajes» (confirma antes de sobreescribir)
- **Referencia v3**: `temas.php`, `editar_tema.php`, `ajax/temas/`
- **Estado**: ✅ Completado. Ver [Historial de cambios](#historial-de-cambios).

#### 2.7 Contenidos por Defecto de Temas
- **Backend**: `backend/api/temas_contenidos_defecto.php` (PHP 5 / `mysqli_*` con sentencias preparadas; acciones `cargar` / `guardar`)
  - ✅ `cargar`: devuelve los contenidos por defecto de un departamento (`contexto`, `recursos`, `metodología`, `adaptaciones`) desde `contenidos_defcto_temas`
  - ✅ `guardar`: inserta o actualiza la fila del departamento (PK `idDepartamento`); rol `admin` o `jefeDepartamento` (este último solo para su propio depto)
  - ✅ Modelo fiel a v3: no hay borrado por campo. La fila es por departamento
  - ⚠️ Corrección: la consulta hacía referencia a `contenidos_defcto_temas` (con typo «defcto»); corregido para coincidir con el esquema real de v3 (`contenidos_defcto_temas`)
- **Frontend**: `frontend/js/views/temas-contenidos-defecto-view.js` (+ cliente `js/api/temas-contenidos-defecto.js`)
  - ✅ Selector de departamento fiel a v3 (admin elige; jefe fijo a su propio dpto), igual que la 2.3
  - ✅ Cuatro editores TinyMCE (Contexto / Recursos / Metodología / Adaptaciones) con la misma configuración que la 2.3
  - ✅ Botones «Guardar cambios» (inserta/actualiza) y «Limpiar todo»
- **Referencia v3**: `temas_contenidos_defecto.php`, `ajax/temas_contenidos_defecto/`
- **Estado**: ✅ Completado. Ver [Historial de cambios](#historial-de-cambios).

### Fase 3: PCCF (Proyecto Curricular de Centro de Formación)

#### 3.1 PCCF
- **Backend**: `backend/api/pccf.php`
  - Gestión del PCCF
- **Frontend**: `frontend/js/views/pccf-view.js`
- **Referencia v3**: `pccf.php`, `ajax/pccf/`

#### 3.2 Apartados PCCF
- **Backend**: `backend/api/pccf_apartados.php`
  - CRUD de apartados del PCCF
- **Frontend**: `frontend/js/views/pccf-apartados-view.js`
- **Referencia v3**: `pccf_apartados.php`, `ajax/pccf_apartados/`

#### 3.3 Contenidos por Defecto PCCF
- **Backend**: `backend/api/pccf_contenidos_defecto.php`
  - Contenidos estándar PCCF
- **Frontend**: `frontend/js/views/pccf-contenidos-defecto-view.js`
- **Referencia v3**: `pccf_contenidos_defecto.php`, `ajax/pccf_contenidos_defecto/`

### Fase 4: Resultados de Aprendizaje y Competencias

#### 4.1 Resultados de Aprendizaje
- **Backend**: `backend/api/resultados_aprendizaje.php`
  - CRUD de RA
  - Vistas previas (texto plano, empresa)
- **Frontend**: `frontend/js/views/resultados-aprendizaje-view.js`
- **Referencia v3**: `resultados_aprendizaje.php`, `ajax/resultados_aprendizaje/`

#### 4.2 Competencias por Ciclo
- **Backend**: `backend/api/competencias_ciclos.php`
  - Asociación competencias-ciclos
- **Frontend**: `frontend/js/views/competencias-ciclos-view.js`
- **Referencia v3**: `competencias_ciclos.php`, `ajax/competencias_ciclos/`

#### 4.3 Cualificaciones y Unidades de Competencia
- **Backend**: `backend/api/cualificaciones_uc.php`
  - Gestión de cualificaciones profesionales
- **Frontend**: `frontend/js/views/cualificaciones-uc-view.js`
- **Referencia v3**: `cualificaciones_uc.php`, `ajax/cualificaciones_uc/`

### Fase 5: Selección y Asignaciones

#### 5.1 Selección de Destinos
- **Backend**: `backend/api/seleccion.php`
  - Listar selección
  - Insertar/borrar selecciones
  - Ordenar selección
  - Sumar horas
  - Botones de acción
  - Listado de profesores por materia
- **Frontend**: `frontend/js/views/seleccion-view.js`
- **Referencia v3**: `seleccion.php`, `ajax/seleccion/`

### Fase 6: Actas y Evaluación

#### 6.1 Actas de Evaluación
- **Backend**: `backend/api/actas.php`
  - Gestión de actas
- **Frontend**: `frontend/js/views/actas-view.js`
- **Referencia v3**: `actas.php`, `ajax/actas/`

### Fase 7: Utilidades y Reportes

#### 7.1 Histórico
- **Backend**: `backend/api/historico.php`
  - Consultas históricas
- **Frontend**: `frontend/js/views/historico-view.js`
- **Referencia v3**: `historico.php`

#### 7.2 Estadísticas
- **Backend**: `backend/api/estadisticas.php`
  - Generación de estadísticas
- **Frontend**: `frontend/js/views/estadisticas-view.js`
- **Referencia v3**: `estadisticas.php`

#### 7.3 Configuración
- **Backend**: `backend/api/configuracion.php`
  - Parámetros del sistema
- **Frontend**: `frontend/js/views/configuracion-view.js`
- **Referencia v3**: `configuracion.php`

#### 7.4 Exportación a Excel
- **Backend**: `backend/api/excel.php`
  - Generación de archivos Excel
- **Referencia v3**: `excel.php`

#### 7.5 Ayuda
- **Frontend**: `frontend/js/views/ayuda-view.js`
- **Referencia v3**: `ayuda.php`, `docs/Manual_*.md`

### Fase 8: Generación de PDFs

Implementar endpoints para generación de PDFs (usando librería compatible con PHP 5):

- `pdf_acta.php` → `backend/api/pdf/acta.php`
- `pdf_desiderata.php` → `backend/api/pdf/desiderata.php`
- `pdf_pccf.php` → `backend/api/pdf/pccf.php`
- `pdf_preferencias.php` → `backend/api/pdf/preferencias.php`
- `pdf_programaciones.php` → `backend/api/pdf/programaciones.php`
- `pdf_programaciones_apartado.php` → `backend/api/pdf/programaciones-apartado.php`
- `pdf_programaciones_aula.php` → `backend/api/pdf/programaciones-aula.php`
- `pdf_programaciones_seguimiento.php` → `backend/api/pdf/programaciones-seguimiento.php`
- `pdf_separata_ce.php` → `backend/api/pdf/separata-ce.php`
- `pdf_unidades_programacion.php` → `backend/api/pdf/unidades-programacion.php`
- `listado_programaciones.php` → `backend/api/pdf/listado-programaciones.php`
- `listado_programaciones_simple.php` → `backend/api/pdf/listado-programaciones-simple.php`
- `listado_urls_pdfs.php` → `backend/api/pdf/listado-urls-pdfs.php`

### Fase 9: Características Avanzadas

- [ ] Vistas previas de criterios de evaluación
- [x] Edición de temas con accordion RA/CE (entregado en la Fase 2.6 — ver [Historial de cambios](#historial-de-cambios))
- [ ] Modales reutilizables (migrar desde `modales/` de v3)
- [ ] Sistema de activaciones por curso académico
- [ ] Copia de seguridad y restauración
- [ ] Importación/exportación de datos

## Estructura de archivos recomendada

```
v4/
├── backend/
│   ├── config.php
│   └── api/
│       ├── auth.php                    # ✅ Implementado
│       ├── app.php                     # ✅ Implementado
│       ├── departamentos.php           # Pendiente
│       ├── profesores.php              # Pendiente
│       ├── especialidades.php          # Pendiente
│       ├── ciclos.php                  # Pendiente
│       ├── cursos.php                  # Pendiente
│       ├── grupos.php                  # Pendiente
│       ├── materias.php                # Pendiente
│       ├── escenarios.php              # Pendiente
│       ├── programaciones.php          # Pendiente
│       ├── programaciones_apartados.php # Pendiente
│       ├── programaciones_aula.php     # ✅ Implementado
│       ├── programaciones_seguimiento.php # ✅ Implementado
│       ├── temas.php                   # ✅ Implementado
│       ├── pccf.php                    # Pendiente
│       ├── resultados_aprendizaje.php  # Pendiente
│       ├── seleccion.php               # Pendiente
│       ├── actas.php                   # Pendiente
│       └── pdf/                        # Carpeta para generadores PDF
│
└── frontend/
    ├── index.html
    ├── css/
    │   └── app.css
    └── js/
        ├── app.js
        ├── api/
        │   ├── auth.js                 # ✅ Implementado
        │   ├── app.js                  # ✅ Implementado
        │   ├── departamentos.js        # Pendiente
        │   ├── profesores.js           # Pendiente
        │   └── ...                     # Por módulo
        ├── components/
        │   ├── login-view.js           # ✅ Implementado
        │   ├── app-layout.js           # ✅ Implementado
        │   ├── sidebar.js              # ✅ Implementado
        │   └── header-bar.js           # ✅ Implementado
        └── views/
            ├── home-view.js            # ✅ Implementado
            ├── departamentos-view.js   # Pendiente
            ├── profesores-view.js      # Pendiente
            └── ...                     # Por módulo
```

## Estado actual del proyecto

| Módulo | Backend API | Frontend View | Estado |
|--------|-------------|---------------|--------|
| Autenticación | ✅ | ✅ | Completado |
| App Layout | ✅ | ✅ | Completado |
| Departamentos | ✅ | ✅ | Completado |
| Profesores | ✅ | ✅ | Completado |
| Especialidades | ✅ | ✅ | Completado |
| Ciclos | ✅ | ✅ | Completado |
| Cursos | ✅ | ✅ | Completado |
| Grupos | ✅ | ✅ | Completado |
| Materias | ✅ | ✅ | Completado |
| Escenarios | ✅ | ✅ | Completado |
| Programaciones | ✅ | ✅ | Completado |
| Programaciones de aula | ✅ | ✅ | Completado |
| Seguimiento de programaciones | ✅ | ✅ | Completado |
| Temas | ✅ | ✅ | Completado |
| PCCF | ❌ | ❌ | Pendiente |
| Resultados Aprendizaje | ❌ | ❌ | Pendiente |
| Selección | ❌ | ❌ | Pendiente |
| Actas | ❌ | ❌ | Pendiente |
| PDFs | ❌ | N/A | Pendiente |

✅ = Implementado | ❌ = Pendiente

## Usuarios de prueba

Cuentas locales creadas en `gestionies.profesores` para probar las fases 2.x sobre Laragon (usuario real, `activo=1`, depto 1). Borrarlas una vez comprobado:

| Usuario | Contraseña | Rol (v4) | jefatura | Notas |
|---------|-----------|----------|----------|-------|
| `testadmin` | `admin1234` | admin | `jefe_departamento=1` | Permite acceder a las secciones con permisos (guardar 2.2/2.3, menús de administración) |
| `testprofe` | `profesor1` | profesor | `jefe_departamento=0` | Simula un profesor: sin acceso al menú «Contenidos generales» y 403 en `guardar` |

## Notas importantes para la migración

1. **Compatibilidad PHP 5**: Todo el código backend debe ser compatible con PHP 5.x
   - Usar `mysqli_*` en lugar de `mysql_*` (obsoleto)
   - Evitar características de PHP 7+
   - No usar type hints estrictos

2. **Base de datos**: Usar el mismo esquema que v3
   - Tabla principal: `profesores` (no `usuarios`)
   - Campos clave: `id`, `nombre`, `usuario`, `clave`, `idDepartamento`, `jefe_departamento`, `activo`
   - Contraseñas en MD5

3. **Comunicación API**: 
   - Backend devuelve JSON: `json_encode(['success' => true/false, 'data' => ..., 'message' => ...])`
   - Frontend usa `fetch()` con `credentials: 'include'` para sesiones
   - Manejo consistente de errores

4. **Componentes Vue**:
   - Usar Vue 3 desde CDN (sin build step)
   - Componentes registrados globalmente
   - Templates como strings en JS

5. **Estilos**:
   - Bootstrap 5.3.8 como base
   - CSS personalizado mínimo
   - Bootstrap Icons para todos los iconos

6. **Seguridad**:
   - Validar siempre en backend
   - Escapar salidas HTML
   - Usar prepared statements para SQL
   - Verificar permisos por rol

## Próximos pasos inmediatos

- **Fase 2.7 — Contenidos por Defecto de Temas**: ✅ Completada (editor TinyMCE para textos por defecto por departamento). Ver [Historial de cambios](#historial-de-cambios).
- **Fase 3 — PCCF** completo: apartados + contenidos por defecto + vista previa.

## Notas importantes

- La aplicación está diseñada para funcionar en servidores antiguos con PHP 5
- No requiere procesos de build/compilación
- El frontend se sirve directamente desde el navegador
- Las sesiones se manejan desde el backend PHP
- La comunicación frontend-backend usa fetch API nativo
- **IMPORTANTE**: Se debe usar la misma base de datos que v3 (tabla `profesores`, no `usuarios`)

## Diferencias con v3

| v3 | v4 |
|----|-----|
| PHP monolítico | Fullstack (PHP + Vue) |
| jQuery | Vue 3 |
| Imágenes PNG para iconos | Bootstrap Icons |
| CSS personalizado extenso | Bootstrap 5.3.8 + CSS mínimo |
| AJAX con jQuery | Fetch API + JSON |
| Templates PHP | Componentes Vue |
| Funcionalidad completa | En desarrollo (Fase 1 y 2.1–2.6 completas) |

## Historial de cambios

### v4.2.5 - 2026 - Fase 2.7 «Contenidos por Defecto de Temas» Completada
- ✅ **Backend** `backend/api/temas_contenidos_defecto.php` (PHP 5 / `mysqli_*` con sentencias preparadas; acciones `cargar` / `guardar`)
  - `cargar`: devuelve los contenidos por defecto de un departamento (`contexto`, `recursos`, `metodología`, `adaptaciones`) desde `contenidos_defecto_temas` (PK `idDepartamento`).
  - `guardar`: inserta o actualiza la fila del departamento; rol `admin` o `jefeDepartamento` (este último solo para su propio depto).
  - ✅ **Verificado contra la BD real** (Laragon): `cargar` y `guardar` devuelven datos reales de los deptos 1 y 2; `guardar` actualiza y restituido al terminar; 401 sin sesión y 403 para rol `profesor`.
  - ⚠️ **Corrección**: la consulta hacía referencia al nombre de tabla con un typo (`contenidos_defcto_temas`); corregido para coincidir con el esquema real de v3 (`contenidos_defecto_temas`).
- ✅ **Frontend** `frontend/js/views/temas-contenidos-defecto-view.js` (+ cliente `js/api/temas-contenidos-defecto.js`)
  - ✅ Selector de departamento fiel a v3 (admin elige; jefe fijo a su propio dpto), igual que la 2.3.
  - ✅ Cuatro editores TinyMCE (Contexto / Recursos / Metodología / Adaptaciones) con la misma configuración que la 2.3.
  - ✅ Botones «Guardar cambios» (inserta/actualiza) y «Limpiar todo».
- ✅ **Integración** (verificada por inspección de código y git): scripts en `index.html`, componente registrado en `app.js` y mapeado a `/temas_contenidos_defecto.php` en `app-layout.js`; acceso «Cont. defecto unidades» en el menú.

### v4.2.4 - 2026 - Fase 2.6 «Temas / Unidades de programación» Completada
- ✅ **Backend** `backend/api/temas.php` (PHP 5 / `mysqli_*` con sentencias preparadas; acciones por parámetro `action`):
  - `listar_materias` / `listar` / `obtener`: materias y temas/unidades por materia (orden, título, peso, horas).
  - `nuevo` / `guardar` / `borrar`: CRUD de temas dentro de transacción (`mysqli_begin_transaction`).
  - `accordion_ra_ce`: carga RA/CE de la materia (cada RA con sus CE asociados, total de RA).
  - `actualizar_ra`: edita un RA (porcentaje de evaluación, «es clave»).
  - `recalcular_porcentajes`: recalcula y actualiza los porcentajes de evaluación de los RA.
  - `repetir_evaluacion`: copia el campo «Evaluación» al resto de unidades de la materia.
- ✅ **Frontend** `frontend/js/views/temas-view.js` (+ cliente `js/api/temas.js`):
  - Listado de temas por materia con control visual de sumas (%=100, horas = horas anuales) en verde/rojo.
  - Editor por pestañas **Datos / RA-CE** con editor TinyMCE fiel a v3 (`initTinyMCE('datostema', 350)`): contexto, recursos, metodología, adaptaciones, evaluación, con «Dejar valores por defecto» por campo.
  - Acordeón dinámico RA→CE: checkboxes de RA/CE, modal de edición de RA (% + clave), suma controlada (debe dar 100%).
  - Botones «Repetir en resto de unidades» y «Calcular y actualizar porcentajes» (confirma antes de sobreescribir).
- ✅ **Integración** (verificada por inspección de código y git): scripts en `index.html`, componente registrado en `app.js` y mapeado a `/temas.php` en `app-layout.js`; acceso «Temas» en el menú.

### v4.2.3 - 2026 - Fase 2.5 «Seguimiento de Programaciones» Completada
- ✅ **Backend** `backend/api/programaciones_seguimiento/{profesores,materias,grupos,evaluaciones,cargar,guardar}.php` (PHP 5 / `mysqli_*` con sentencias preparadas):
  - `profesores` / `materias` / `grupos` / `evaluaciones`: selectores en cascada fieles a v3.
  - `cargar`: registro de impartición por profesor+materia+grupo+evaluación.
  - `guardar`: inserta/actualiza el registro (temporalización, resultados académicos, inclusión del alumnado). Admin guarda para cualquier profesor; un profesor solo para sí mismo (fuerza `idProfesor` al usuario de la sesión).
- ✅ **Frontend** `frontend/js/views/programaciones-seguimiento-view.js` (+ cliente `js/api/programaciones-seguimiento.js`):
  - Selector en cascada fiel a v3: profesor (solo admin) + materia + grupo + evaluación.
  - Tres editores TinyMCE WYSIWYG con la **misma configuración que 2.3/2.4**: temporalización, resultados académicos e inclusión del alumnado.
  - Botones «Guardar cambios» y «Vista previa» (modal con las tres secciones renderizadas).
- ✅ **Integración** (verificada por inspección de código y git): scripts en `index.html`, componente registrado en `app.js` y mapeado a `/programaciones_seguimiento_aula.php` en `app-layout.js`.

### v4.2.2 - 2026 - Fase 2.4 «Programación de Aula» Completada
- ✅ **Backend** `backend/api/programaciones_aula/{materias,grupos,temas,contenido,guardar}.php` (PHP 5 / `mysqli_*` con sentencias preparadas):
  - `materias`: lista las materias con `tiene_programacion=1` para un profesor (admin elige; profesor usa el suyo).
  - `grupos`: grupos asignados en la selección actual del curso (`seleccion` + `escenarios_desideratas.actual`).
  - `temas`: unidades de una materia ordenadas por `orden`.
  - `contenido`: texto introductorio por triplete tema+grupo+profesor desde `programaciones_aula_temas`.
  - `guardar`: inserta/actualiza; **con texto vacío borra la fila**, idéntico al comportamiento de v3. Permisos: admin puede guardar para cualquier profesor; un profesor solo para sí mismo.
- ✅ **Frontend** `frontend/js/views/programaciones-aula-view.js` + cliente `js/api/programaciones-aula.js`:
  - Selector de profesor (solo visible para admin/jefe) + materia + grupo, en cascada fiel a v3.
  - Editor TinyMCE WYSIWYG con los mismos plugins/toolbar de v3 y `css/estilos_tiny.css`, botones «Guardar cambios».
  - Botones PDF (Separata CE / Programación de aula) como stubs informativos: se activan en la Fase 8.
  - Integración: scripts cargados en `index.html`, componente registrado en `app.js` y mapeado a `/programaciones_aula.php` en `app-layout.js`; el acceso «Programaciones de aula» ya estaba presente en `config.php → getMenus` (visible para todos los roles, como en v3).
- ✅ **Verificado contra la BD real** vía HTTP (Laragon): login admin OK; `materias` devuelve las materias reales del curso con programación; `grupos` resuelve por selección+escenario actual; `temas` lista unidades ordenadas; `guardar` inserta→actualiza→borra con texto vacío, restituido al terminar; 401/403 sin sesión o para roles restringidos.
- ⚠️ **Nota de permisos**: v3 permite guardar a admin/jefe (con selector de profesor) y a cualquier profesor activo (solo el suyo). Se replica igual en v4: la API detecta rol desde la sesión y, si no es admin, fuerza `idProfesor` al propio usuario.

### v4.2.1 - 2026 - Fase 2.3 «Contenidos por Defecto» Completada
- ✅ **Backend** `backend/api/programaciones_contenidos_defecto/{cargar,guardar}.php` (PHP 5 / `mysqli_*` con sentencias preparadas):
  - `cargar`: devuelve el `texto` de `contenidos_defecto_programaciones` para el par apartado+departamento (vacío si no existe).
  - `guardar` (solo rol `admin`, como en la 2.2): inserta o actualiza; **con texto vacío borra la fila**, idéntico al comportamiento de v3 (`ajax/programaciones_contenidos_defecto/insertar_contenido_defecto_programacion.php`).
- ✅ **Frontend** `frontend/js/views/programaciones-contenidos-defecto-view.js` + cliente `js/api/programaciones-contenidos-defecto.js`:
  - Selector de departamento y de apartado. El desplegable solo muestra los apartados con `contenido_defecto = 1` y `tipo = 0`, aplicando la **numeración global fiel a v3** (1, 2… y 1.1, 1.2 en subapartados).
  - **Comportamiento por rol (como en v3)**: un jefe de departamento queda fijo a su propio departamento (desplegable deshabilitado con su nombre) y ve los contenidos por defecto del dpto al que pertenece; un admin sin departamento asignado puede elegir cualquier departamento.
  - **Editor TinyMCE WYSIWYG activo (igual que en v3)**: TinyMCE 7.9.1 se activa en el editor de contenidos — `frontend/lib/js/tinymce/` copiado íntegro desde `v3/lib/js/tinymce`, más `css/estilos_tiny.css`, cargado en `index.html` tras SweetAlert2 con el mismo orden que `v3/includes/cabecera.php`. La inicialización replica la `initTinyMCE('progeditar')` de v3: mismos plugins (`autolink lists advlist code fullscreen wordcount`), misma toolbar, altura 300px y `content_css: css/estilos_tiny.css`; botones «Limpiar» y «Guardar cambios» con lectura/vaciado vía `getContent()` / `setContent()`, feedback con SweetAlert2 y diseño homogéneo al resto de la aplicación.
  - Integración: scripts cargados en `index.html`, componente registrado en `app.js` y ruta mapeada en `app-layout.js`; el acceso «Contenidos generales» ya existe en `config.php → getMenus` (visible para jefe de departamento/admin) y el sidebar lo resuelve añadiendo `.php` al link.
- ✅ **Verificado contra la BD real** vía HTTP (Laragon): `cargar` devuelve el contenido real de un apartado existente; `guardar` probado íntegro sobre un par sin datos reales (inserta → actualiza → borra con texto vacío, restituido al terminar); 403 para rol profesor y sin sesión; menú presente para admin y ausente para profesor; `index.html` sirve los scripts nuevos y el listado de apartados que alimenta la vista.
- 🔧 **Corrección**: los clientes de las fases 2.x llamaban a `backend/api/…` relativo (resolvía en `/v4/frontend/backend/api/…` → 404 y desplegables vacíos). Se pasa a la convención de la app `'../backend/api/…'` (vista + `js/api/programaciones-contenidos-defecto.js` + `js/api/programaciones-apartados.js`) y se corrige la numeración cuando `subapartado` llega como texto (`"0"` no es falsy en JS).
- ⚠️ **Nota de roles**: v3 permitía guardar a `jefeDepartamento` o `admin`; en v4 todo jefe de departamento se mapea a `admin` (`auth.php`), por lo que el chequeo único de rol es equivalente y consistente con la 2.2.
- ⚠️ **Alcance**: los datos (tabla `contenidos_defecto_programaciones`) son compartidos por apartado+departamento tal y como en v3; si un profesor rellena su propio contenido en su programación, ese prevalece sobre el por defecto (comportamiento de la generación de programaciones, fuera de alcance de esta fase).

### v4.2.x - Decisión B — FASE 2.1 FIEL A V3 (Programaciones)
- ✅ **Reencuadre de la Fase 2.1** tras detectar que el modelo inicial no era fiel a v3: en v3 **no existe tabla `programaciones`**. La programación vive en **apartados + contenidos** asociados a cada materia (flag `materias.tiene_programacion`). Se retiran las acciones crear/guardar/eliminar y la tabla ficticia.
- **Entregables redefinidos (FIEL a v3)**:
  - Backend `backend/api/programaciones/index.php` (compatible PHP 5 con `mysqli_*`):
    - `listar`: materias con programación activa + estado real (nº de apartados). Curso vía `materias.idCurso → cursos`.
    - `obtener`: apartados + contenidos de la materia (solo lectura), agrupando varios contenidos por apartado.
    - `importar` (conservado, opera sobre las tablas reales v3): borra y re-inserta de destino←origen `contenidos_programaciones`, `temas`, `competencias_temas`/`criterios_temas`, replicando `v3/ajax/programaciones/importar_programacion.php`.
  - Frontend `frontend/js/views/programaciones-view.js` + cliente `frontend/js/api/programaciones.js`:
    - Listado **Materia | Curso | Horas | Apartados** con filtro por materia.
    - Modal «Ver» (solo lectura) de apartados + contenidos; sin create/edit/delete.
    - Importar origen→destino con doble confirmación (SweetAlert2), conservado.
  - Integración: cargado en `index.html`, registrado en `app.js` y mapeado a la ruta `/programaciones` en `app-layout.js`.
- ✅ **Verificado contra la BD real** (`gestionies.sql`): `php -l` limpio; `listar` devuelve materias reales (p. ej. 4º ESO «Lenguaje y Literatura», L2, 15 apartados) y `obtener` devuelve el texto real de su programación.
- ⚠️ **Alcance**: la edición de los datos se hace en las fases 2.2–2.5 (CRUD de apartados/contenidos + temas); aquí solo se listan, ven e importan. Ver [Registro de Decisiones Técnicas](#registro-de-decisiones-técnicas).

### v4.1.3 - 2025 - Fase 1 Completa (Módulos Básicos)
- ✅ **Especialidades**: CRUD completo implementado
- ✅ **Ciclos Formativos**: CRUD con asociación a especialidades
- ✅ **Cursos**: CRUD con categorías (ESO/BACH/FP/OTROS)
- ✅ **Grupos**: CRUD básico
- ✅ **Materias**: CRUD básico
- ✅ **Escenarios**: CRUD adaptado a tabla `escenarios_desideratas`
- ✅ **Correcciones generales**:
  - Todos los endpoints backend migrados a PDO con sentencias preparadas
  - APIs frontend consistentes con el patrón establecido
  - Vistas formateadas correctamente sin errores de sintaxis
  - Orden de carga de scripts verificado en index.html
  - Componentes registrados en app.js y app-layout.js

### v4.1.2 - 2025 - Fase 1.2 Profesores Completada
- ✅ **Profesores**: Módulo completo con diseño Bootstrap 5
  - Backend: `backend/api/profesores.php` (CRUD completo, filtrado por departamento)
  - Frontend View: `frontend/js/views/profesores-view.js`
  - Frontend API: `frontend/js/api/profesores.js`
  - Diseño homogéneo con Bootstrap 5.3.8
  - Iconos Bootstrap Icons (sin imágenes PNG)
  - CSS personalizado mínimo
  - SweetAlert2 para mensajes y confirmaciones
  - Modal centrado con header coloreado
  - Listado en card con list-group-flush
  - Funcionalidades implementadas:
    - Listar profesores filtrados por departamento
    - Crear profesor con asignación de departamento
    - Editar profesor (nombre, apellidos, usuario, contraseña)
    - Eliminar profesor con confirmación
    - Actualizar jefe de departamento
    - Activar/desactivar profesor
    - Ordenar profesores (subir/bajar)
  - ✅ **Corrección de errores**:
    - Solucionado error de sintaxis por backticks mal escapados en templates
    - Corregido orden de carga de scripts en index.html
    - Backends y frontends cargan en el orden correcto

### v4.1.0 - Fase 1.1 Departamentos Completada
- ✅ **Departamentos**: Módulo completo con diseño Bootstrap 5
  - Backend: `backend/api/departamentos.php` (CRUD completo)
  - Frontend View: `frontend/js/views/departamentos-view.js`
  - Frontend JS: `frontend/js/departamentos.js`
  - Diseño homogéneo con Bootstrap 5.3.8
  - Iconos Bootstrap Icons (sin imágenes PNG)
  - CSS personalizado mínimo
  - SweetAlert2 para mensajes y confirmaciones
  - Modal centrado con header coloreado
  - Listado en card con list-group-flush

### v4.0.1 - 2025
- ✅ Corregido error de compatibilidad PHP: migrado de `mysql_*` a `mysqli_*`
- ✅ Login funcional usando tabla `profesores` de v3
- ✅ Adaptado sistema de autenticación a estructura real de BD
- ✅ Contraseñas MD5 compatibles con v3
- ✅ Roles basados en campo `jefe_departamento`
- ✅ Filtrado por usuarios activos (`activo = 1`)
- ✅ README actualizado con planificación detallada

### v4.0.0 - Versión inicial
- Estructura base fullstack creada
- Frontend Vue 3 con Bootstrap 5
- Sistema de autenticación básico (requería correcciones)

---

## Metodología de Desarrollo v4

### Principios de Diseño

1. **Bootstrap First**
   - Utilizar al máximo las clases utilitarias de Bootstrap 5.3.8
   - CSS personalizado solo cuando sea estrictamente necesario
   - Componentes nativos de Bootstrap (modals, cards, list-groups, etc.)

2. **Iconografía**
   - Bootstrap Icons 1.13.1 exclusivamente
   - Sin imágenes PNG/SVG personalizadas
   - Iconos semánticos según contexto

3. **Componentes Vue**
   - Vue 3 desde CDN (sin build step)
   - Templates como strings en archivos .js
   - Componentes registrados globalmente
   - Comunicación padre-hijo vía props/events

4. **Responsive Design**
   - Mobile-first approach
   - Sidebar colapsable en pantallas <768px
   - Menú se cierra automáticamente tras navegación
   - Uso de container-fluid para contenido principal

5. **Arquitectura Fullstack**
   - Backend PHP 5 devuelve JSON
   - Frontend Vue consume APIs con fetch()
   - Sesiones manejadas desde backend
   - Validación siempre en servidor

### Patrón CRUD Base

Cada módulo sigue esta estructura:

```
backend/
└── api/
    └── {modulo}.php       # API REST (GET, POST, DELETE)

frontend/
├── js/
│   ├── views/
│   │   └── {modulo}-view.js    # Template Vue del módulo
│   └── {modulo}.js             # Lógica de negocio (cargar, crear, editar, borrar)
```

### Convenciones de Código

- **Backend PHP**: 
  - Respuestas JSON: `{success: bool, data: any, message: string}`
  - Usar `mysqli_*` con prepared statements
  - Validar permisos por rol

- **Frontend JS**:
  - Componentes como objetos literales
  - Events personalizados con `$emit()`
  - SweetAlert2 para UX feedback
  - Iconos Bootstrap en templates

### Registro de Decisiones Técnicas

Las decisiones importantes de diseño e implementación se documentan en este README bajo la sección correspondiente de cada versión.

#### D-2025: Fase 2.1 «Programaciones» — Decisión B, FIEL A V3 (no tabla propia)
- **Decidido por**: Usuario («FIEL a v3»).
- **Contexto**: la primera entrega de la 2.1 modelaba una `tabla programaciones` simplificada (una fila por materia/grupo con objetivos/metodología/etc.). Al contrastar con el modelo real de v3 se detectó que **no existe** esa tabla: en v3 la programación didáctica son **apartados + contenidos** asociados a cada materia (flag `materias.tiene_programacion`), y no hay una «fila» que crear.
- **Decisión**: rehacer la 2.1 para ser fiel a v3. Se retiran crear/guardar/eliminar y la tabla ficticia. Entregables finales:
  - `backend/api/programaciones/index.php`: `listar` (materias con programación + nº de apartados; curso vía `idCurso → cursos`), `obtener` (apartados + contenidos, solo lectura) e `importar` (conservado sobre las tablas reales v3).
  - `frontend/js/views/programaciones-view.js` + cliente `api/programaciones.js`: listado **Materia | Curso | Horas | Apartados**, modal «Ver» y modal de importación; sin create/edit/delete.
- **Consecuencia**: la edición real de los apartados/contenidos se hace en las fases 2.2–2.5 (CRUD de esos módulos). La 2.1 da visibilidad fiel al estado y conserva el Importar existente.
- **Verificación**: `php -l` limpio; comprobado contra la BD real (`gestionies.sql`) vía HTTP: `listar` y `obtener` devuelven datos reales del curso en marcha.


## Historial de cambios

### v4.1.3 - Fase 1 completa (Módulos básicos de mantenimiento)

**Fecha**: Implementación completada

**Módulos implementados**:
- ✅ **1.3 Especialidades**: CRUD completo
  - Backend: `backend/api/especialidades/{listar,obtener,guardar,eliminar}.php`
  - Frontend: `frontend/js/views/especialidades-view.js`, `frontend/js/api/especialidades.js`

- ✅ **1.4 Ciclos Formativos**: CRUD con asociación a especialidades
  - Backend: `backend/api/ciclos/{listar,obtener,guardar,eliminar}.php`
  - Frontend: `frontend/js/views/ciclos-view.js`, `frontend/js/api/ciclos.js`

- ✅ **1.5 Cursos**: CRUD con categorías (ESO/BACH/FP/OTROS)
  - Backend: `backend/api/cursos/{listar,obtener,guardar,eliminar}.php`
  - Frontend: `frontend/js/views/cursos-view.js`, `frontend/js/api/cursos.js`

- ✅ **1.6 Grupos**: CRUD básico
  - Backend: `backend/api/grupos/{listar,obtener,guardar,eliminar}.php`
  - Frontend: `frontend/js/views/grupos-view.js`, `frontend/js/api/grupos.js`

- ✅ **1.7 Materias**: CRUD básico
  - Backend: `backend/api/materias/{listar,obtener,guardar,eliminar}.php`
  - Frontend: `frontend/js/views/materias-view.js`, `frontend/js/api/materias.js`

- ✅ **1.8 Escenarios**: CRUD básico (aulas, salas, etc.)
  - Backend: `backend/api/escenarios/{listar,obtener,guardar,eliminar}.php`
  - Frontend: `frontend/js/views/escenarios-view.js`, `frontend/js/api/escenarios.js`

**Consideraciones técnicas**:
1. Todos los módulos siguen el mismo patrón de diseño para facilitar mantenimiento
2. Backend compatible con PHP 5.x usando `mysqli_*` functions
3. Frontend Vue 3 desde CDN sin proceso de compilación
4. Uso consistente de Bootstrap 5.3.8 y SweetAlert2
5. Los nombres de campos en frontend coinciden con las claves primarias de BD (idEspecialidad, idCiclo, etc.)

**Próximos pasos**: Fase 2 - Programaciones Didácticas
