# Arquitectura de la versión 4

## Flujo de una pantalla

1. Vue obtiene la sesión y el menú autorizado de `backend/api/session.php`.
2. La ruta actual solicita una vista permitida a `backend/view.php`.
3. PHP aplica sesión, rol y configuración, y devuelve sólo el fragmento de contenido.
4. Vue inserta el fragmento y carga el módulo JavaScript correspondiente.
5. Las operaciones de datos usan `fetch` contra los endpoints de `backend/ajax/`.

Esta estrategia conserva toda la funcionalidad y el modelo de datos de v3, pero elimina PHP del frontend y permite sustituir gradualmente cada fragmento heredado por componentes Vue sin volver a cambiar el contrato del backend.

## Compatibilidad del código heredado

`frontend/js/dom.js` no es jQuery. Es una utilidad nativa y acotada que implementa únicamente las operaciones que usaba GestionIES: selección DOM, formularios, `fetch`, modales Bootstrap, acordeones sencillos y ordenación por arrastre con HTML5 Drag and Drop. Esto evita incluir una biblioteca general de compatibilidad y mantiene pequeños los cambios de los módulos ya probados.

## Seguridad de rutas

`backend/view.php` no acepta nombres de archivo arbitrarios. Normaliza el nombre solicitado y lo contrasta con una lista blanca. Cada vista sigue declarando sus roles y `includes/cabecera.php` responde con 401 o 403 en modo API.

El login usa consultas preparadas para profesores y regenera el identificador de sesión al autenticar. El hash MD5 se mantiene exclusivamente por compatibilidad con los datos existentes.
