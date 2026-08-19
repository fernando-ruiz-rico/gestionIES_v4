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

## Roles de usuario

- **admin**: Acceso completo a todas las opciones
- **jefeDepartamento**: Acceso a opciones de departamento
- **profesor**: Acceso limitado a opciones básicas

## Próximos pasos (pendientes)

Para completar la funcionalidad de v3, habría que implementar:

1. **APIs adicionales** en `backend/api/`:
   - departamentos.php
   - profesores.php
   - ciclos.php
   - cursos.php
   - materias.php
   - programaciones.php
   - pccf.php
   - etc.

2. **Componentes Vue** para cada sección:
   - Vistas de listados
   - Formularios de alta/modificación
   - Modales para operaciones CRUD

3. **Base de datos**:
   - Migrar esquema desde v3
   - Adaptar consultas SQL si es necesario

## Notas importantes

- La aplicación está diseñada para funcionar en servidores antiguos con PHP 5
- No requiere procesos de build/compilación
- El frontend se sirve directamente desde el navegador
- Las sesiones se manejan desde el backend PHP
- La comunicación frontend-backend usa fetch API nativo

## Diferencias con v3

| v3 | v4 |
|----|-----|
| PHP monolítico | Fullstack (PHP + Vue) |
| jQuery | Vue 3 |
| Imágenes PNG para iconos | Bootstrap Icons |
| CSS personalizado extenso | Bootstrap 5.3.8 + CSS mínimo |
| AJAX con jQuery | Fetch API + JSON |
| Templates PHP | Componentes Vue |
