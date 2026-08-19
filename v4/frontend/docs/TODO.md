# Cambios y dudas pendientes

## A tener en cuenta

Si alguna vez se cambia el horario del centro (tabla `horas`), hay que hacer un UPDATE para actualizar cada hora en la tabla de `preferencias_horario` para cada profesor. Básicamente es tomar cada hora y hacer un UPDATE. Por ejemplo, si la primera hora pasa a ser las 08:00 en lugar de las 07:55, cambiar la hora a mano en la tabla `horas` y luego ejecutar esto:

```
UPDATE preferencias_horario SET hora = '08:00' WHERE hora = '07:55'
```

Los formularios modales de `modales/cursos.php` y `modales/programaciones_apartados.php` contienen un *select* con valores puestos a mano para elegir la categoría del curso o del apartado de la programación, respectivamente. Se deben editar para cambiar los valores admitidos según el caso.

## Base de datos

**Tabla *departamentos***

HECHO: Quitar campos *tutoriasPS*, *tutoriasPT*, *inglesPS*, *inglesPT*

**Tabla *grupos***

HECHO: Quitar tabla
HECHO: Renombrar tabla *grupos_cursos* a *grupos*

**Tabla *especialidades***

HECHO: Crear nueva tabla con sus campos, y añadido campo `idEspecialidad` en tabla *profesores* y *materias*.
HECHO: Eliminar campo *grupo* de tabla *profesores*
HECHO: Rellenar especialidades para cada departamento y rellenar el campo *idEspecialidad* de cada profesor según el departamento. Algunas consultas útiles:
   HECHO: UPDATE profesores SET idEspecialidad = 'INF' WHERE idDepartamento = 1 AND grupo = 'PS'
   HECHO: UPDATE profesores SET idEspecialidad = 'SAI' WHERE idDepartamento = 1 AND grupo = 'PT'
HECHO: Añadir campo *idEspecialidad* en tabla *materias*, ponerle longitud 3 y que admita nulos, y actualizar los id de especialidades. Algunas consultas útiles:
   HECHO: UPDATE materias SET idEspecialidad = 'INF' WHERE idDepartamento = 1 AND grupo = 'PS'
   HECHO: UPDATE materias SET idEspecialidad = 'SAI' WHERE idDepartamento = 1 AND grupo = 'PT'

**Tabla *tipos_cursos***

HECHO: Quitar tabla, y reemplazarla por nuevo campo `categoria` en tabla *cursos*, de tipo *varchar(5)*, con posibles valores ESO/BACH/FP/OTROS
HECHO: Añadir el mismo campo en tabla `apartados_programaciones`, y actualizarlo según el valor del campo *tipo_curso*. Eliminar luego el campo *tipo_curso*. En este caso los valores posibles son ESO/BACH/FP/TODOS

**Tabla resultados_aprendizaje**

HECHO: creada tabla con los siguientes campos:

* *id* autonumérico (CP)
* *idMateria* (clave ajena a tabla *materias*)
* *orden* (entero, valor predeterminado 0)
* *texto* (texto)
* *porcentaje_empresa* (entero, valor predeterminado 0)

**Tabla *materias***

* HECHO: Añadir campo *horas_empresa*, de tipo entero, valor por defecto 0. Se rellenará desde los resultados de aprendizaje del módulo

## Troubleshooting

Si en alguna vista principal da algún error en una operación aparentemente sencilla, puede que sea porque no se ha fijado correctamente el departamento con que trabajar. Echar un vistazo a la vista "profesores.php" para ver cómo actualizar el departamento (al final del código).