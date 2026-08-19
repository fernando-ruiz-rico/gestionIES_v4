# GestionIES - Manual para profesores

En este manual se recoge el uso de la aplicación *GestionIES* por parte de los profesores del centro.

## Índice de contenidos

<ol>
    <li><a href="#acceso">Acceso a la aplicación</a></li>
    <li><a href="#perfil">Perfil del profesor</a></li>
    <li><a href="#actas">Actas del departamento</a></li>
    <li><a href="#desideratas">Gestión de desideratas</a></li>
    <li><a href="#programaciones">Gestión de programaciones didácticas</a></li>
</ol>

<a name="acceso">

## 1. Acceso a la aplicación

Lo primero que tendremos que hacer es acceder a la aplicación con las credenciales que nos habrán facilitado:

<div align="center">
    <img src="img_doc/prof/login.png" width="50%">
</div>

En el menú izquierdo podemos consultar las opciones disponibles para profesores:

* Perfil del profesor
* Salir (para hacer *logout* y salir de la aplicación, volviendo al formulario anterior)

<a name="perfil">

## 2. Perfil del profesor

Desde el menú *Perfil* se abrirá un formulario para que podamos actualizar o completar nuestro perfil:

<div align="center">
    <img src="img_doc/admin/profesores_form.png" width="60%">
</div>

Los administradores de la web ya habrán dado de alta nuestro nombre, abreviatura, login, password y especialidad. Podemos completar el resto de información disponible: teléfono, e-mail, observaciones sobre el horario (explicar de palabra qué horas preferimos y cuáles no), y completar el cuadro derecho marcando en rojo las horas en las que no podemos/queremos ir, y en amarillo las que preferimos no estar. El sistema está configurado para aceptar un máximo número de horas en rojo.

En cuanto al password o **clave**, si no rellenamos la casilla conservaremos el password actual. 

<a name="actas">

## 3. Gestión de actas de departamento

Desde la sección *Actas* del menú izquierdo se puede acceder a las actas del departamento al que pertenece el profesor/a que accede. Si no somos jefe/a de departamento sólo podremos elegir la fecha del acta a consultar y generar el PDF de la misma:

<div align="center">
    <img src="img_doc/prof/actas.png" width="80%">
</div>

En el caso de ser jefe/a de departamento, al elegir cualquier fecha de acta anterior se cargarán sus datos en un formulario inferior. También si elegimos el botón *Nueva acta* se generarán los datos iniciales de la nueva acta (profesores asistentes, inicio del "Orden del día"), para que lo editemos. También debemos especificar la fecha de esa nueva reunión, y luego guardar los cambios al finalizar.

<div align="center">
    <img src="img_doc/prof/actas2.png" width="80%">
</div>

<a name="desideratas">

## 4. Gestión de desideratas

La gestión de desideratas desde el punto de vista del profesor se centra en tres aspectos:

* Definir escenarios de selección para cada curso académico y grupo(s) de departamento(s) (sólo el jefe del departamento)
* Consultar el histórico de selecciones de otros cursos
* Poder seleccionar cada profesor sus materias (o el jefe de departamento las de todo el departamento)

### 4.1. Gestión de escenarios de desideratas

Sólo en el caso de jefes de departamento tendremos habilitada esta opción. Un escenario de desideratas es una especie de "bolsa" que contiene las selecciones de un conjunto de departamentos para un curso en concreto. Se gestionan desde la sección *Escenarios* del menú *Desideratas*. Aparecerán listados los escenarios vinculados con el departamento al que pertenece el profesor que accede (escenarios tanto de este curso como de cursos pasados):

<div align="center">
    <img src="img_doc/prof/escenarios.png" width="80%">
</div>

Para cada escenario tenemos una serie de botones disponibles en la parte derecha. En orden de izquierda a derecha:

* Borrar el escenario indicado (previa confirmación)
* Editar los datos del escenario
* Marcar el escenario como actual (vigente para el curso actual, queda en verde), o desmarcarlo si ya no es el actual
* Marcar el escenario como activo para desideratas (candado abierto, fondo verde), o cerrarlo (candado cerrado).
* Duplicar el escenario. Se creará otro con el sufijo *bis* y las mismas características y selección de materias que el original.

Tanto con el botón de editar como con el botón de *Nuevo Escenario* que hay bajo el listado se abre un formulario para editar los datos del escenario en cuestión. Básicamente hay que indicar el nombre del escenario y los departamentos involucrados (por defecto aparecerá marcado el departamento con el que se está trabajando actualmente):

<div align="center">
    <img src="img_doc/admin/escenarios2.png" width="30%">
</div>

### 4.2. Consultar el histórico de selecciones

Todos los profesores de un departamento pueden acceder a la sección *Histórico* del menú *Desideratas* para consultar el histórico de selecciones que se han hecho tanto en este curso como en cursos anteriores, en los distintos escenarios creados. Basta con elegir el escenario deseado y aparecerá un listado con los profesores involucrados y lo que eligió cada uno/a.

<div align="center">
    <img src="img_doc/prof/historico.png" width="80%">
</div>

### 4.3. Gestión de selección de profesores

Podemos editar la selección de materias para un escenario activo, desde el menú *Desideratas > Selección*. En primer lugar deberemos elegir el escenario en cuestión. 

#### 4.3.1. Acceso como profesores

Si accedemos como profesores veremos únicamente dos columnas: los cursos y materias que podemos elegir (izquierda) y nuestra selección actual (derecha):

<div align="center">
    <img src="img_doc/prof/seleccion2.png" width="80%">
</div>

**Columna de materias y cursos**

En la columna izquierda podremos elegir qué materias va a impartir el profesor/a, y de qué cursos y grupos. Basta con que despleguemos las materias de los distintos grupos y vayamos eligiéndolas con su botón de `+`. Nos pedirá que elijamos cuántas horas semanales elige el profesor (normalmente todas, salvo que la pueda partir con otro profesor/a). Además, pulsando en los números a la derecha de cada materia podremos ver qué profesores la han elegido:

<div align="center">
    <img src="img_doc/admin/seleccion2.png" width="25%">
</div>

**Columna de selección**

La columna derecha mostrará la selección de materias del profesor actual. Veremos su nombre, el listado de materias (que se pueden ordenar por prioridad arrastrando y soltando entre sí) y el total de horas lectivas acumuladas. Además, bajo estos paneles veremos diferentes iconos de herramientas:

* Borrar la materia actualmente seleccionada del panel derecho (icono del cubo de basura)
* Borrar todas las materias seleccionadas (salvo las que le haya asignado la directiva)
* Ver las estadísticas del escenario actual (conflictos encontrados, horas elegidas por especialidad, etc)
* Mostrar un PDF con su selección y preferencias horarias actuales
* Mostrar un PDF con sus preferencias horarias nada más (en otra plantilla PDF diferente)
* Generar un Excel resumen de la selección del escenario actual

#### 4.3.2. Acceso como jefe de departamento

Si somos jefes de departamento veremos un panel de 3 columnas

<div align="center">
    <img src="img_doc/prof/seleccion.png" width="80%">
</div>

Las últimas dos son iguales que las que se ven como profesor, y aparece en este caso una nueva columna a la izquierda, la **columna de profesores**. En esta columna podremos listar todos los profesores del departamento, y también filtrar con los botones superiores por las distintas especialidades asociadas al departamento. Seleccionando un profesor en concreto se marcará de otro color, y podremos elegir materias en el panel central para ese profesor, y ver su selección actual en el panel derecho.

Además, en este panel de profesores tenemos dos iconos:

* Generar un PDF con el listado de selecciones de todos los profesores que aparecen en el listado
* Eliminar las selecciones de todos los profesores (previa confirmación)

### 4.4. Activar/desactivar el período de desideratas

Si somos jefes de departamento, desde la sección de *Desideratas > Escenarios* podemos cerrar el candado de todos los escenarios, para que no se puedan elegir desideratas en ellos (para un departamento concreto, o departamento a departamento). Además, podemos marcar en verde cuál (o cuáles) son los escenarios en vigor para el curso actual.

<div align="center">
    <img src="img_doc/admin/desideratas_elegir_escenario.png" width="70%">
</div>

### 4.5. El "modo rueda"

Desde la sección de escenarios los administradores o jefes de departamento pueden habilitar un "modo rueda" para un escenario en concreto. Esto implica que el profesorado pierde el control en la selección de materias, y es el jefe/a de departamento (o el administrador) quien puede asignar a cada profesor sus materias, en el orden establecido. 

Si el "modo rueda" está habilitado, para cualquier profesor que no sea jefe de departamento o administrador:

* Desaparece el botón junto a cada asignatura para poderla seleccionar
* Desaparecen los botones para borrar su selección
* Se deshabilita la opción de reordenar sus preferencias

<a name="programaciones">

## 5. Gestión de programaciones didácticas

A la hora de gestionar las programaciones didácticas de los departamentos, este apartado incluye diversas tareas:

* Editar el contenido por defecto que tendrán ciertos apartados de la programación (sólo para jefes de departamento)
* Editar los contenidos particulares de cada apartado para cada programación (cada profesor/a podrá editar los de las materias que imparta)
* Llevar el seguimiento de las programaciones de forma trimestral (cada profesor/a editará el de sus grupos y materias)
* ...

### 5.1. Gestión de los contenidos por defecto de algunos apartados

Desde el menú *Programaciones > Contenidos generales* que tendrán disponibles los jefes de departamento se podrán editar el contenido general de ciertos apartados de las programaciones, para el departamento en cuestión. Simplemente tendremos que elegir el apartado a editar, y enviando el formulario se guardarán los cambios.

<div align="center">
    <img src="img_doc/prof/programaciones_contenidos_defecto.png" width="80%">
</div>

Para eliminar el contenido del apartado basta con enviar el formulario con el texto vacío.

### 5.2. Gestión de los resultados de aprendizaje y criterios de evaluación

Desde el menú *Programaciones > Resultados aprendizaje y CE* podemos editar los resultados de aprendizaje asociados a una determinada materia, y los criterios de evaluación derivados. Si somos jefes de departamento, en el desplegable veremos todas las materias asociadas a nuestro departamento. En caso de ser profesores "normales", sólo veremos las materias que hayamos elegido:

<div align="center">
    <img src="img_doc/prof/resultados_aprendizaje.png" width="80%">
</div>

Para cada resultado podemos editarlo o gestionar sus criterios de evaluación, y también podemos crear nuevos. Para editar un RA debemos indicar el número de orden del resultado, su texto oficial y el porcentaje de docencia que estimamos que se asignará a la empresa (en el caso de materias dualizables):

<div align="center">
    <img src="img_doc/admin/resultados_aprendizaje2.png" width="30%">
</div>

Si gestionamos los criterios de evaluación (botón izquierdo junto a cada RA en el listado) podremos borrar/editar cada criterio, o añadir nuevos con el formulario inferior.

<div align="center">
    <img src="img_doc/admin/criterios_evaluacion.png" width="50%">
</div>

### 5.3. Gestión de los contenidos de las programaciones

Desde el menú *Programaciones > Programaciones* gestionamos los contenidos de los distintos apartados de cada programación. Debemos elegir la materia y el apartado a editar.

<div align="center">
    <img src="img_doc/prof/programaciones.png" width="80%">
</div>

Pulsando el botón de *Guardar cambios* en la parte inferior se guardan los cambios realizados para el apartado y materia elegidos. También tenemos botones en la parte superior para: 

* Tener una vista previa en HTML de toda la programación. Se mostrarán en rojo los apartados obligatorios que aún no se han rellenado.
* Generar un fichero PDF de la programación para la materia seleccionada
* Generar un fichero PDF del apartado indicado para la materia seleccionada
* En el caso de jefes/as de departamento, hay un cuarto botón para importar el contenido de una programación en la actual. Se abrirá una ventana para que elijamos de qué programación importar y se actualizarán los datos (se borrarán previamente los contenidos antiguos, antes de importar los nuevos).

> **NOTA**: cuando se desactive el período de edición de programaciones, sólo podremos visualizar la programación, pero no editar sus contenidos.

### 5.4. Gestión del seguimiento de las programaciones

El seguimiento de las programaciones implica llevar un control trimestral de las mismas. Para ello tenemos disponible el menú *Programaciones > Seguimiento*. A la hora de hacer el seguimiento tendremos que elegir el curso que editar (por defecto sólo tenemos disponible el curso actual, salvo que seamos jefes de departamento), la evaluación y la materia.

<div align="center">
    <img src="img_doc/prof/programaciones_seguimiento.png" width="80%">
</div>

Bajo este primer panel aparece un formulario donde podemos editar la temporalización, los resultados obtenidos y el porcentaje de aprobados para la materia seleccionada. En el caso de que haya varios grupos de esa materia, todos deberán compartir estos datos e irlos acumulando.

<div align="center">
    <img src="img_doc/admin/programaciones_seguimiento2.png" width="80%">
</div>

Con el botón *Guardar cambios* de la parte inferior guardamos toda la información de golpe. Además, en la parte superior hay 3 botones adicionales:

* El primero servirá para importar los datos de la evaluación anterior en la nueva que hayamos seleccionada (servirá siempre que no hayamos elegido la 1ª evaluación)
* El segundo servirá para importar los datos del curso anterior, para la misma evaluación. Por ejemplo, para repetir un seguimiento similar en la Evaluación Final para un nuevo curso, basándonos en lo que escribimos en el curso anterior y modificando algunos datos.
* El tercer botón sirve para tener una vista previa de cómo quedan los datos introducidos (una vez guardados).

Finalmente, si somos jefes/as de departamento, disponemos de un tercer panel bajo el anterior para rellenar información de seguimiento común a todo el departamento seleccionado, para el curso y evaluación elegidas (para todas las materias).

<div align="center">
    <img src="img_doc/admin/programaciones_seguimiento3.png" width="80%">
</div>

Como vemos, se trata de indicar el funcionamiento general del departamento durante la evaluación, indicar las actividades extraescolares que se han llevado a cabo y la temporalización general de las materias. Nuevamente, el botón *Guardar cambios* inferior servirá para guardar todos estos datos comunes de golpe (independientemente de los anteriores) y, además, disponemos de cuatro botones en la parte superior:

* Importar datos de la evaluación anterior (funcionamiento similar al del seguimiento de materias, pero para importar los datos de los contenidos comunes)
* Importar datos del curso anterior (lo mismo pero basándonos en la misma evaluación del curso anterior)
* Vista previa de los resultados comunes
* Generar un PDF completo con el seguimiento tanto de los elementos comunes del departamento como desglosado para cada curso y materia.

### 5.5. Gestión de las unidades/temas de las programaciones

En el formulario para elegir la programación hay un botón *Unidades* para definir las unidades o temas asociados a esa programación.

<div align="center">
    <img src="img_doc/prof/boton_temas.png" width="80%">
</div>

Se abrirá una pestaña nueva donde podremos editar los datos de cada tema. Primero tendremos que añadirlo con sus datos básicos: número y título del tema:

<div align="center">
    <img src="img_doc/prof/gestion_temas.png" width="40%">
</div>

Una vez aparezca en el listado de temas, podremos borrarlo o editar su información avanzada.

<div align="center">
    <img src="img_doc/prof/gestion_temas2.png" width="80%">
</div>

Si pulsamos en el botón de edición veremos un formulario más detallado para completar la información del tema: horas estimadas, trimestre en que se impartirá, porcentaje de peso en la evaluación del trimestre, y varios datos complejos que se muestran en pestañas. Entre ellos, podremos asociar el tema con criterios de evaluación (RA/CE) o con competencias (profesionales, etc).

<div align="center">
    <img src="img_doc/prof/gestion_temas3.png" width="80%">
</div>

### 5.6. Programaciones de aula

Desde el submenú *Programaciones de aula* en la sección de *Programaciones* accedemos a la programación de aula de cada profesor para cada materia y grupo que imparte. En el formulario deberemos elegir la materia, el grupo y el tema, y rellenar el cuadro de texto con el seguimiento y temporización que se realice para ese tema.

<div align="center">
    <img src="img_doc/prof/programaciones_aula.png" width="70%">
</div>
