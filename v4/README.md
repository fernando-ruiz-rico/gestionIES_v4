# GestionIES v4

Migración full-stack de GestionIES v3 con separación estricta entre cliente y servidor.

## Arquitectura

- `frontend/`: cliente estático Vue 3. No contiene PHP ni depende de jQuery. Usa Bootstrap 5, Bootstrap Icons, `fetch`, TinyMCE y una pequeña utilidad DOM nativa para conservar los flujos de la v3.
- `backend/`: autenticación, sesión, vistas, operaciones AJAX, generación de PDF/Excel y acceso MySQL en PHP 5.4 o posterior.
- `docs/`: documentación histórica y notas de la migración.

La entrada de la aplicación es `frontend/index.html`. El frontend y el backend deben publicarse bajo el mismo dominio y conservar la estructura de carpetas relativa mostrada arriba.

## Puesta en marcha

1. Copiar la carpeta `v4` al servidor web.
2. Configurar la conexión MySQL en `backend/includes/database.php`.
3. Verificar que PHP dispone de la extensión `mysqli` y permisos de sesión.
4. Abrir `https://servidor/ruta/v4/frontend/index.html`.

No hay paso de compilación: Vue 3, Bootstrap 5, Bootstrap Icons y SweetAlert se cargan desde CDN, igual que Bootstrap en la versión anterior. TinyMCE se sirve localmente.

## Compatibilidad y rutas

- Las páginas funcionales de v3 se ejecutan en `backend/view.php`, con una lista blanca de vistas permitidas.
- Las peticiones existentes a `ajax/...` y `modales/...` se resuelven automáticamente contra el backend mediante `frontend/js/dom.js`.
- Los PDF, Excel y vistas imprimibles se abren directamente desde el backend.
- La navegación de aplicación se realiza mediante rutas hash de Vue, por ejemplo `frontend/index.html#/programaciones`.
- Se conserva el esquema de contraseñas MD5 de v3 para no invalidar usuarios existentes. Conviene planificar una migración posterior a `password_hash` cuando se abandone PHP 5.

## Comprobaciones incluidas en la entrega

- frontend sin archivos PHP;
- frontend sin bibliotecas jQuery/jQuery UI;
- sintaxis validada para todo el JavaScript propio;
- inventario de operaciones AJAX de v3 contrastado con el backend v4;
- vistas PHP principales, modales, exportación PDF/Excel y recursos de backend migrados;
- nuevo generador que faltaba en v3: `pdf_pccf_apartado.php`;
- corrección de sintaxis no compatible con PHP 5 (`??`) y de la variable de profesorado no inicializada en el PDF del PCCF.

## Limitación de la validación local

La prueba integral con datos requiere la base MySQL real y un intérprete PHP 5 con `mysqli`. El paquete se ha validado de forma estática y por correspondencia de rutas; tras desplegarlo debe realizarse una prueba de humo con una copia de la base de datos.
