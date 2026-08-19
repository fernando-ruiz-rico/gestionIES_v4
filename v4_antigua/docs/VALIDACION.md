# Validación de la migración

Fecha: 19 de agosto de 2026.

## Resultado estático

| Comprobación | Resultado |
| --- | ---: |
| Archivos totales de la entrega | 1203 |
| Archivos PHP en frontend | 0 |
| Bibliotecas jQuery/jQuery UI en frontend | 0 |
| Referencias jQuery en el JavaScript propio | 0 |
| Módulos JavaScript propios validados | 29/29 |
| Endpoints AJAX/modales referenciados | 139 |
| Endpoints AJAX/modales ausentes | 0 |
| Archivos PHP del backend | 674 |
| Modales migrados | 26 |
| Sintaxis `??` incompatible con PHP 5 | 0 |

La validación de JavaScript se realizó con el analizador sintáctico de Node.js. También se contrastaron automáticamente las rutas literales usadas por el frontend con los archivos existentes en `backend/ajax/` y `backend/modales/`.

## Comprobaciones funcionales cubiertas por estructura

- autenticación y cierre de sesión mediante API JSON;
- carga de sesión, rol, departamento, especialidad, activaciones y menú;
- lista blanca de vistas y control de rol heredado;
- carga de las 26 áreas JavaScript de v3 sin jQuery;
- formularios con `FormData` y peticiones GET/POST mediante `fetch`;
- modales Bootstrap, TinyMCE, fecha de actas, acordeones y listas ordenables;
- navegación interna Vue y apertura de vistas imprimibles, PDF y Excel;
- recursos de ayuda, imágenes, plantillas PDF y bibliotecas PHP conservados;
- generación completa y por apartado de programaciones y PCCF.

## Prueba pendiente del entorno de destino

No se incluyó una base de datos MySQL ejecutable ni un intérprete PHP en el entorno de migración. Por ello, la prueba final con datos reales debe realizarse en el servidor de destino siguiendo el `README.md`: login de cada rol, una alta/edición/borrado por módulo y una exportación de cada tipo.
