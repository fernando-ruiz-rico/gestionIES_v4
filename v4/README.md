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

#### 2.1 Programaciones
- **Backend**: `backend/api/programaciones.php`
  - CRUD de programaciones
  - Importar programación
- **Frontend**: `frontend/js/views/programaciones-view.js`
- **Referencia v3**: `programaciones.php`, `ajax/programaciones/`, `modales/importar_programacion.php`

#### 2.2 Apartados de Programación
- **Backend**: `backend/api/programaciones_apartados.php`
  - CRUD de apartados
  - Generar apartados por defecto
- **Frontend**: `frontend/js/views/programaciones-apartados-view.js`
- **Referencia v3**: `programaciones_apartados.php`, `ajax/programaciones_apartados/`

#### 2.3 Contenidos por Defecto
- **Backend**: `backend/api/programaciones_contenidos_defecto.php`
  - Gestión de contenidos estándar
- **Frontend**: `frontend/js/views/programaciones-contenidos-defecto-view.js`
- **Referencia v3**: `programaciones_contenidos_defecto.php`, `ajax/programaciones_contenidos_defecto/`

#### 2.4 Programación de Aula
- **Backend**: `backend/api/programaciones_aula.php`
  - Desarrollo diario de clases
- **Frontend**: `frontend/js/views/programaciones-aula-view.js`
- **Referencia v3**: `programaciones_aula.php`, `ajax/programaciones_aula/`

#### 2.5 Seguimiento de Programaciones
- **Backend**: `backend/api/programaciones_seguimiento.php`
  - Registro de impartición
  - Vista previa
- **Frontend**: `frontend/js/views/programaciones-seguimiento-view.js`
- **Referencia v3**: `programaciones_seguimiento.php`, `ajax/programaciones_seguimiento/`

#### 2.6 Temas
- **Backend**: `backend/api/temas.php`
  - CRUD de temas
  - Cargar checkboxes RA/CE
  - Recalcular porcentajes
  - Repetir evaluación
- **Frontend**: `frontend/js/views/temas-view.js`
- **Referencia v3**: `temas.php`, `editar_tema.php`, `ajax/temas/`

#### 2.7 Contenidos por Defecto de Temas
- **Backend**: `backend/api/temas_contenidos_defecto.php`
  - Gestión de contenidos estándar para temas
- **Frontend**: `frontend/js/views/temas-contenidos-defecto-view.js`
- **Referencia v3**: `temas_contenidos_defecto.php`, `ajax/temas_contenidos_defecto/`

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
- [ ] Edición de temas con accordion RA/CE
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
│       ├── programaciones_aula.php     # Pendiente
│       ├── programaciones_seguimiento.php # Pendiente
│       ├── temas.php                   # Pendiente
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
| Departamentos | ❌ | ❌ | Pendiente |
| Profesores | ❌ | ❌ | Pendiente |
| Especialidades | ❌ | ❌ | Pendiente |
| Ciclos | ❌ | ❌ | Pendiente |
| Cursos | ❌ | ❌ | Pendiente |
| Grupos | ❌ | ❌ | Pendiente |
| Materias | ❌ | ❌ | Pendiente |
| Escenarios | ❌ | ❌ | Pendiente |
| Programaciones | ❌ | ❌ | Pendiente |
| Temas | ❌ | ❌ | Pendiente |
| PCCF | ❌ | ❌ | Pendiente |
| Resultados Aprendizaje | ❌ | ❌ | Pendiente |
| Selección | ❌ | ❌ | Pendiente |
| Actas | ❌ | ❌ | Pendiente |
| PDFs | ❌ | N/A | Pendiente |

✅ = Implementado | ❌ = Pendiente

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

1. **Implementar módulo de Departamentos** (primer módulo básico)
2. **Implementar módulo de Profesores** (segundo módulo básico)
3. **Crear patrón base** para CRUDs que pueda replicarse en otros módulos
4. **Implementar sistema de modales** reutilizables
5. **Continuar con el resto de módulos** siguiendo la planificación

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
| Funcionalidad completa | En desarrollo (login funcional) |

## Historial de cambios

### v4.1.1 - 2025
- ✅ **Sidebar**: Menú lateral se cierra automáticamente al seleccionar una opción
  - Implementado evento `close-menu` en sidebar.js
  - Listener en `mounted()` para cerrar menú en cambio de ruta (hashchange)
  - Previene que el menú tape la nueva pantalla después de navegar
  - Mejora UX en dispositivos móviles y tablets

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
