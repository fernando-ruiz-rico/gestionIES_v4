# GestionIES - Manual para el administrador

El administrador de la aplicación *GestionIES* será el encargado de dar de alta los datos necesarios para su correcto funcionamiento. En este documento se explican los pasos a seguir para ello.

## Índice de contenidos

<ol start="0">
    <li><a href="#requisitos">Requisitos software</a></li>
    <li><a href="#inicio">Puesta en marcha inicial</a></li>
    <li><a href="#departamentos">Gestión de departamentos, especialidades y profesores</a></li>
    <li><a href="#actas">Actas del departamento</a></li>
    <li><a href="#cursos">Gestión de cursos, grupos y materias</a></li>
    <li><a href="#desideratas">Gestión de desideratas</a></li>
    <li><a href="#programaciones">Gestión de programaciones didácticas</a></li>
    <li><a href="#pccf">Gestión de Proyectos Curriculares de Ciclo</a></li>
</ol>

<a name="requisitos">

## 0. Requisitos software

Para poder ejecutar esta aplicación se necesita un servidor Apache con una versión de PHP 5.4, y un servidor de base de datos MySQL donde importar la base de datos.

<a name="inicio">

## 1. Puesta en marcha inicial

La primera vez que se instala o utiliza la aplicación es necesario seguir estos pasos. Si ya se está utilizando la aplicación se puede saltar al paso siguiente. Sólo es necesario conocer cuál es el password del usuario *admin* para poder trabajar.

1. Crear en MySQL una base de datos con el nombre que se quiera. Por ejemplo, *gestionies*.
2. Acceder al backup de la base de datos, disponible en la subcarpeta *backup* del proyecto. Contiene una base de datos con todas las tablas vacías, salvo la tabla *config* que tiene los parámetros generales de configuración, y la tabla *horas* con el horario por defecto del centro (se puede cambiar a mano).
3. Importar el script SQL en la base de datos creada en el punto 1
4. Acudir desde *phpMyAdmin* o similar a la tabla *horas* y ajustarlas según el horario de apertura y turnos de clase del centro
5. En el código de la aplicación, editar el fichero `includes/database.php` para establecer los parámetros correctos de conexión a la base de datos
6. Desplegar la aplicación en Apache y acceder a la página de inicio *index.php*. Nos deberá redirigir a la página de *login*
7. En la configuración inicial, el usuario *admin* tiene password *admin*
8. Una vez dentro, desde el menú *Configuración* podemos cambiar la contraseña de *admin* por otra que prefiramos.
9. Ahora ya estamos en disposición de usar el resto de opciones que se comentan a continuación.

<a name="departamentos">

## 2. Gestión de departamentos, especialidades y profesores

Una de las opciones del menú disponible para el administrador es la de *Profesores y Departamentos* que, a su vez, se divide en tres opciones:

* Gestión de departamentos
* Gestión de especialidades asociadas a cada departamento
* Gestión de profesores asociados a cada departamento

<div align="center">
    <img src="img_doc/admin/profesores_departamentos.png" width="20%">
</div>

### 2.1. Gestión de departamentos

Si elegimos la opción de *Departamentos* del menú anterior tendremos un listado con los departamentos existentes. Podemos añadir nuevos con el botón de *Nuevo Departamento* al final del listado, o editar cada departamento con su icono del lápiz, o borrar el departamento con su icono de la *X*.

<div align="center">
    <img src="img_doc/admin/departamentos.png" width="80%">
</div>

### 2.2. Gestión de especialidades

Las especialidades están adscritas a un departamento. Por ejemplo, en un departamento de Informática pueden estar las especialidades de Informática y la de Sistemas y Aplicaciones Informáticas del cuerpo correspondiente (Profesores de Enseñanza Secundaria o Profesores Técnicos de FP). Desde la sección de *Especialidades* del menú anterior podemos elegir un departamento en la lista desplegable y añadir/editar/borrar especialidades de ese departamento.

<div align="center">
    <img src="img_doc/admin/especialidades.png" width="80%">
</div>

El formulario para añadir o editar especialidades pide cuatro datos (sólo los dos primeros son obligatorios):

* *Identificador* de la especialidad, que se establece como un código de 3 caracteres.
* *Nombre* completo de la especialidad (hasta 50 caracteres)
* Número de horas de tutoría de las que corresponden al departamento que se piensa destinar a los profesores de esa especialidad (en el caso de querer hacer un reparto proporcional entre especialidades)
* Número de horas de docencia en inglés a asignar a la especialidad, de entre las horas de docencia en inglés asignadas al departamento (por el mismo motivo que el campo anterior).
* Número de profesores asignados a esa especialidad

<div align="center">
    <img src="img_doc/admin/especialidades_form.png" width="40%">
</div>

Estos dos últimos campos se pueden dejar vacíos en el caso de no querer controlar estas horas en el departamento en cuestión.

### 2.3. Gestión de profesores

Desde la opción *Profesores* del menú anterior accedemos a la gestión de profesores por departamento. Igual que en los casos anteriores, debemos elegir primero el departamento y se mostrará un listado de sus actuales profesores. Con el botón final de *Nuevo Profesor* podemos dar de alta nuevos profesores, y con los botones de editar y borrar de cada profesor podemos cambiar los datos de cada profesor o borrarlo. Respectivamente.

<div align="center">
    <img src="img_doc/admin/profesores.png" width="80%">
</div>

Además, en el listado de profesores hay dos opciones más:

* Un icono de una medalla que estará en verde para quien sea designado jefe/a de departamento. Al hacer clic en cualquier profesor, se le nombra jefe/a de departamento y se "desasigna" a quien lo fuera anteriormente.
* Un icono de un *slider" que estará verde para los profesores actualmente en activo en el departamento. Al pulsar en ese botón se alternará entre verde y rojo. Los profesores en rojo son profesores que no están actualmente en el departamento, pero que se quedan guardados por motivos de datos históricos, o por una posible vuelta en el futuro.

En cuanto al formulario con la información de los profesores, se piden varios datos:

<div align="center">
    <img src="img_doc/admin/profesores_form.png" width="60%">
</div>

* Nombre completo del profesor
* Abreviatura, que se puede emplear por ejemplo en programas externos como el de la confección de horarios (*Kronowin* o herramientas similares)
* Login del usuario para acceder al sistema
* Clave o *password* para acceder. Si no es especifica ninguna y se está insertando al usuario, se le asigna como clave su mismo login. Si ya tiene una clave asignada y no se especifica ninguna, se le respeta la clave que tuviera guardada.
* E-mail del profesor
* Número de teléfono
* Especialidad a la que pertenece (de entre las que están asociadas al departamento)
* Observaciones referentes al horario, donde se indica de palabra preferencias sobre qué horas quiere evitar o cuáles prefiere
* En la parte derecha hay un horario semanal donde, haciendo clic en las distintas casillas, se pueden cambiar a rojo, amarillo o blanco. Las casillas rojas son las que quiere evitar a toda costa, las amarillas las que preferiría evitar y las blancas las que les da igual. La aplicación está preparada para admitir un máximo de 3 casillas rojas e ilimitadas amarillas, pero esto se puede cambiar desde el código del fichero *ajax/profesores/cargar_preferencias_profesor.php*, modificando la variable *maxRojas* de la línea 42.

> Como administrador, lo necesario es rellenar el nombre del profesor, su abreviatura, login y especialidad. Opcionalmente se le puede asignar una clave a mano (aunque si no, se le pone la misma que el login que tenga).
> 
> Después cada profesor desde su perfil personal puede completar la información, indicando e-mail, teléfono, preferencias horarias, etc

<a name="actas">

## 3. Gestión de actas de departamento

Desde la sección *Actas* del menú izquierdo se pueden gestionar las actas de los diferentes departamentos. Primero tendremos que elegir el departamento sobre el que trabajar:

<div align="center">
    <img src="img_doc/admin/actas.png" width="80%">
</div>

Una vez elegido el departamento, podemos:

* Elegir la fecha de un acta previa, para poderla editar o visualizar en el editor. También generar el PDF de dicha acta:

<div align="center">
    <img src="img_doc/admin/actas2.png" width="80%">
</div>

* Elegir el botón de *Nueva acta* para generar una nueva. Tendremos que especificar la fecha de la reunión y completar el contenido que se autogenera en el editor, donde ya se incluyen los miembros del departamento y se inicia el apartado de "Orden del día".

<a name="cursos">

## 4. Gestión de cursos, grupos y materias

Desde la opción *Cursos y Materias* del menú podemos gestionar los cursos del instituto, los grupos asignados a cada curso y las materias que se imparten en esos cursos.

### 4.1. Cursos

La opción de *Cursos* permite gestionar los cursos disponibles en el centro. Al inicio se mostrará un listado de los cursos disponibles. Cada curso tiene botones para poderlos borrar o editar, y al final del listado hay un botón para crear nuevos cursos.

<div align="center">
    <img src="img_doc/admin/cursos.png" width="80%">
</div>

> Es **IMPORTANTE** recalcar que los cursos sólo se podrán borrar si no tienen grupos ni materias asignados. En caso contrario primero habrá que eliminar las materias y grupos asignados al curso.

El formulario para insertar o editar los cursos nos pedirá la información básica de los mismos: nombre, abreviatura corta, categoría (ESO, Bachillerato o FP) y número de horas semanales de docencia que tiene (se puede dejar vacía o a 0 si no se quiere especificar):

<div align="center">
    <img src="img_doc/admin/cursos2.png" width="30%">
</div>

Adicionalmente, los cursos también se pueden **ordenar**, arrastrándolos y soltándolos en el listado. Este orden se utilizará en otras opciones de la aplicación, para sacar el listado en el orden establecido.

### 4.2. Grupos

La opción de *Grupos* nos deja crear nuevos grupos asignados a un curso. Por ejemplo, para el curso 1º DAM podemos crear el grupo 1º DAM A, o el 1º DAM B. Primero deberemos elegir el curso con el que queremos trabajar en el desplegable, y aparecerán los grupos vinculados a ese curso (es obligatorio que haya al menos un grupo para luego poder hacer selección de materias para esos grupos creados).

<div align="center">
    <img src="img_doc/admin/grupos.png" width="80%">
</div>

Para cada grupo podemos borrarlo o editarlo con los botones que tiene a su izquierda, y con el botón de *Nuevo* que hay en la parte inferior podemos rellenar los datos del grupo:

<div align="center">
    <img src="img_doc/admin/grupos2.png" width="30%">
</div>

Como vemos, se especifica un nombre del grupo, una abreviatura, y marcamos (o no) la casilla para que se muestre el texto del grupo en los listados. Esto vendrá bien cuando un curso tenga varios grupos, para poder identificar cuál es cada uno. Finalmente podemos indicar el número de horas complementarias que se le asigna a cada profesor del grupo, en el caso de que dicho grupo tenga FP dual (dejar a 0 en caso contrario).

### 4.3. Materias

Para asignar las materias de un curso tenemos la opción *Materias* del menú *Cursos y Materias*. Primero deberemos elegir el curso sobre el que queremos trabajar en el desplegable, y aparecerán las materias que tiene actualmente asignadas:

<div align="center">
    <img src="img_doc/admin/materias.png" width="80%">
</div>

Podemos borrar o editar los datos de cada materia con los botones de su parte derecha, y crear nuevas materias con el botón *Nueva Materia* al final del listado:

<div align="center">
    <img src="img_doc/admin/materias2.png" width="30%">
</div>

De cada materia debemos especificar: 

* Su nombre
* La cantidad de unidades ofertadas por grupo (para el caso de desdobles, por ejemplo, indicaríamos dos)
* Las horas semanales de docencia que tiene
* Las horas complementarias que le suponen al profesor que la elija
* Si las horas semanales cuentan como horas lectivas del grupo (por ejemplo, la tutoría no cuenta en los ciclos superiores)
* Si es una materia asignada por el equipo directivo (por ejemplo, tutorías, jefaturas de departamento o algunos cargos) o la puede elegir libremente el profesorado
* Si tiene programación didáctica asociada (las tutorías o los cargos no la tienen)
* Si es divisible para poderla impartir entre varios profesores (cada uno una parte de las horas)
* El tipo de materia (tutoría, inglés u otros tipos generales de materias)
* El departamento asignado
* La especialidad que puede impartir la materia (si tiene alguna restricción explícita)
* Cuántos profesores distintos hacen falta para poderla impartir (si hay varias unidades asignadas que se imparten a la vez, hará falta un profesor por unidad)
* Cuántas unidades puede escoger un mismo profesor como mucho (por ejemplo, en optativas de ESO que se imparta la misma asignatura en el mismo curso en franjas diferentes, un profesor puede elegir la misma asignatura varias veces, cada una en una franja)

Además, existe una tercera opción para cada materia, que permite editar datos específicos para cada grupo. Podemos especificar la cantidad de unidades que hay para ese grupo (en el caso de que haya desdobles para unos grupos pero no para otros), las horas a la semana, mínimo número de profesores, etc. Por defecto estos datos se copian de los que hemos puesto generales para la materia, pero con este otro formulario podemos editar y modificar los que sean especiales para algunos grupos:

<div align="center">
    <img src="img_doc/admin/materias3.png" width="60%">
</div>

> **ACTUALIZACIÓN**: con posterioridad, se añadieron cuatro campos más a la tabla de `materias`:
> * `codigo_oficial`: código oficial de la materia en el listado de módulos del ministerio
> * `nombre_oficial`: nombre oficial de la materia
> * `creditos_ects`: créditos ECTS de la materia
> * `horas_anuales`: horas totales al año de la materia

#### 4.3.1. Competencias asociadas a materias

Desde el listado de materias hay un botón para asociarles competencias profesionales, para la empleabilidad, etc.

<div align="center">
    <img src="img_doc/admin/competencias_materias.png" width="70%">
</div>

Al hacer clic se abre un formulario para ver/borrar las competencias actualmente asociadas, y una opción al final para añadir nuevas.

<div align="center">
    <img src="img_doc/admin/competencias_materias2.png" width="60%">
</div>

### 4.4. Ciclos

Para los cursos que correspondan a ciclos formativos, se tiene la opción *Ciclos* en el menú. Se muestra un listado con los ciclos disponibles, y cada uno tiene botones para poderlos borrar o editar, y al final del listado hay un botón para crear nuevos cursos.

<div align="center">
    <img src="img_doc/admin/ciclos.png" width="80%">
</div>

> Es **IMPORTANTE** recalcar que los ciclos sólo se podrán borrar si no tienen cursos asociados.

El formulario para insertar o editar los ciclos nos pedirá la información básica de los mismos: nombre, familia y nivel:

<div align="center">
    <img src="img_doc/admin/ciclos2.png" width="30%">
</div>

Además, en el listado de ciclos hay un tercer botón para asociar unidades de competencia al ciclo. Estas unidades de competencia se gestionan desde otro menú (ver el apartado de *Cualificaciones profesionales y unidades de competencia*) pero, una vez añadidas, pulsando este botón podemos elegir cuáles queremos asociar al ciclo. Desde el desplegable que aparece añadimos unidades, y pulsando en el icono de borrar de las que tengamos añadidas podemos borrarlas.

<div align="center">
    <img src="img_doc/admin/ciclos3.png" width="30%">
</div>

#### 4.4.1. Asociar cursos con ciclos

Existe un cuarto botón en la vista de ciclos (a la izquierda de todos) que permite asociar el ciclo con los cursos que lo componen.

<div align="center">
    <img src="img_doc/admin/cursos_ciclos.png" width="60%">
</div>

Aparecerá un formulario para poder añadir nuevos cursos al ciclo (indicando el orden que ocupan) y también borrar o actualizar los datos de los cursos ya asociados.

<div align="center">
    <img src="img_doc/admin/cursos_ciclos2.png" width="40%">
</div>

<a name="desideratas">

## 5. Gestión de desideratas

La gestión de desideratas desde el punto de vista del administrador se centra en dos aspectos:

* Definir escenarios de selección para cada curso académico y grupo(s) de departamento(s)
* Poder editar las desideratas realizadas por un departamento en concreto

### 5.1. Gestión de escenarios de desideratas

Un escenario de desideratas es una especie de "bolsa" que contiene las selecciones de un conjunto de departamentos para un curso en concreto. Se gestionan desde la sección *Escenarios* del menú *Desideratas*. Primero habrá que elegir desde qué departamento se quiere actuar, para ver el listado de escenarios que se han creado para el mismo (de este curso y de cursos pasados):

<div align="center">
    <img src="img_doc/admin/escenarios.png" width="80%">
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

### 5.2. Gestión de selección de profesores

Como administrador también se puede editar la selección de los profesores de un departamento, desde el menú *Desideratas > Selección*. En primer lugar deberemos elegir el departamento con el que trabajar, y el escenario:

<div align="center">
    <img src="img_doc/admin/seleccion.png" width="80%">
</div>

La sección principal se divide en 3 columnas:

**Columna de profesores**

En esta columna podremos listar todos los profesores del departamento, y también filtrar con los botones superiores por las distintas especialidades asociadas al departamento. Seleccionando un profesor en concreto se marcará de otro color, y podremos elegir materias en el panel central para ese profesor, y ver su selección actual en el panel derecho.

Además, en este panel de profesores tenemos dos iconos:

* Generar un PDF con el listado de selecciones de todos los profesores que aparecen en el listado
* Eliminar las selecciones de todos los profesores (previa confirmación)

**Columna de materias y cursos**

En la columna central podremos elegir, para el profesor seleccionado de la izquierda, qué materias va a impartir, y de qué cursos y grupos. Basta con que despleguemos las materias de los distintos grupos y vayamos eligiéndolas con su botón de `+`. Nos pedirá que elijamos cuántas horas semanales elige el profesor (normalmente todas, salvo que la pueda partir con otro profesor/a). Además, pulsando en los números a la derecha de cada materia podremos ver qué profesores la han elegido:

<div align="center">
    <img src="img_doc/admin/seleccion2.png" width="25%">
</div>

**Columna de selección**

La columna derecha mostrará la selección de materias del profesor actualmente elegido. Veremos su nombre, el listado de materias (que se pueden ordenar por prioridad arrastrando y soltando entre sí) y el total de horas lectivas acumuladas. Además, bajo estos paneles veremos diferentes iconos de herramientas:

* Borrar la materia actualmente seleccionada del panel derecho (icono del cubo de basura)
* Borrar todas las materias seleccionadas (salvo las que le haya asignado la directiva)
* Ver las estadísticas del escenario actual (conflictos encontrados, horas elegidas por especialidad, etc)
* Mostrar un PDF con su selección y preferencias horarias actuales
* Mostrar un PDF con sus preferencias horarias nada más (en otra plantilla PDF diferente)
* Generar un Excel resumen de la selección del escenario actual

### 5.3. Activar/desactivar el período de desideratas

Desde el menú *Configuracion* tenemos un botón ON/OFF para activar/desactivar el período de desideratas. Cuando está activo, los profesores pueden elegir materias en los escenarios activos. Cuando no lo está, se cierra la opción de elegir materias (una vez iniciado ya el curso, o finalizado el claustro de selección).

<div align="center">
    <img src="img_doc/admin/desideratas_on_off.png" width="15%">
</div>

También desde la sección de *Desideratas > Escenarios* podemos cerrar el candado de todos los escenarios, para que no se puedan elegir desideratas en ellos (para un departamento concreto, o departamento a departamento). Además, podemos marcar en verde cuál (o cuáles) son los escenarios en vigor para el curso actual.

<div align="center">
    <img src="img_doc/admin/desideratas_elegir_escenario.png" width="70%">
</div>

### 5.4. El "modo rueda"

Desde la sección de escenarios se puede habilitar un "modo rueda" para un escenario en concreto. Esto implica que el profesorado pierde el control en la selección de materias, y es el jefe/a de departamento (o el administrador) quien puede asignar a cada profesor sus materias, en el orden establecido. 

Si el "modo rueda" está habilitado, para cualquier profesor que no sea jefe de departamento o administrador:

* Desaparece el botón junto a cada asignatura para poderla seleccionar
* Desaparecen los botones para borrar su selección
* Se deshabilita la opción de reordenar sus preferencias

<a name="programaciones">

## 6. Gestión de programaciones didácticas

La gestión de programaciones didácticas comprende varios cometidos: gestionar los apartados que forman parte de las programaciones didácticas, definir contenidos por defecto para ciertos apartados, establecer el contenido de los apartados de una programación concreta...

### 6.1. Gestión de los apartados de la programación

La gestión de apartados de las programaciones didácticas es tarea exclusiva del usuario *admin*, desde el menú *Programaciones > Apartados*. Aparecerá un listado con los apartados actuales, y al final un botón para añadir nuevos:

<div align="center">
    <img src="img_doc/admin/programaciones_apartados.png" width="80%">
</div>

Desde el botón de *Nuevo apartado* o desde el botón de editar junto a cada apartado se abrirá un formulario para rellenar los datos de ese apartado. 

<div align="center">
    <img src="img_doc/admin/programaciones_apartados2.png" width="30%">
</div>

Tendremos que indicar su título, la categoría de cursos a los que se debe aplicar (ESO, Bachillerato, FP o Todos) y marcar algunas casillas:

* Si es un subapartado (que depende de uno principal) o no
* Si es obligatorio rellenarlo
* Si admite contenido por defecto (común a varias programaciones) o no
* El campo *tipo* indica el tipo de contenido, que puede ser un valor textual (lo más habitual) o algún tipo de consulta específica a la base de datos (por ejemplo, para extraer los resultados de aprendizaje y criterios de evaluación de una materia).

### 6.2. Gestión de los contenidos por defecto de algunos apartados

Desde el menú *Programaciones > Contenidos generales* podemos editar el contenido general de ciertos apartados de las programaciones, para un departamento determinado. Como administrador, tendremos que elegir primero el departamento y luego el apartado a editar. Enviando el formulario se guardarán los cambios.

<div align="center">
    <img src="img_doc/admin/programaciones_contenidos_defecto.png" width="80%">
</div>

Para eliminar el contenido del apartado basta con enviar el formulario con el texto vacío.

### 6.3. Gestión de los resultados de aprendizaje y criterios de evaluación

Desde el menú *Programaciones > Resultados aprendizaje y CE* podemos editar los resultados de aprendizaje asociados a una determinada materia, y los criterios de evaluación que los componen. Debemos elegir primero el departamento con el que trabajar, y en el desplegable veremos todas las materias asociadas al departamento elegido.

<div align="center">
    <img src="img_doc/admin/resultados_aprendizaje.png" width="80%">
</div>

Para cada resultado podemos editarlo o borrarlo, y también podemos crear nuevos. Debemos indicar el número de orden del resultado, su texto oficial y el porcentaje de docencia que estimamos que se asignará a la empresa (en el caso de materias dualizables):

<div align="center">
    <img src="img_doc/admin/resultados_aprendizaje2.png" width="30%">
</div>

Además, con el botón de la izquierda en cada item del listado podemos gestionar los criterios de evaluación asociados a ese resultado de aprendizaje:

<div align="center">
    <img src="img_doc/admin/criterios_evaluacion.png" width="50%">
</div>

Para cada criterio de evaluación podemos borrarlo o actualizar sus datos, con los botones que tiene al lado. Además, desde el formulario inferior podemos añadir nuevos criterios, indicando su código (letra) y su texto.

### 6.4. Gestión de las competencias de ciclos

Desde el menú *Programaciones > Competencias* gestionamos las competencias de los distintos ciclos formativos. Simplemente elegimos el ciclo a gestionar y aparecerá el listado de competencias actual.

<div align="center">
    <img src="img_doc/admin/competencias_ciclos.png" width="80%">
</div>

Podemos editar o borrar cada competencia, y reordenarlas arrastrando y soltando entre ellas. También hay un botón al final del listado para añadir una nueva competencia, completando sus datos:

<div align="center">
    <img src="img_doc/admin/competencias_ciclos2.png" width="30%">
</div>

### 6.5. Gestión de las cualificaciones profesionales y unidades de competencia

Desde el menú *Programaciones > Cualificaciones y UC* gestionaremos las cualificaciones profesionales y unidades de competencia asociadas. Al entrar podremos elegir con dos botones si queremos ocuparnos de cualificaciones profesionales o unidades de competencia, y se mostrará el listado de uno u otro.

<div align="center">
    <img src="img_doc/admin/cualificaciones_uc.png" width="80%">
</div>

En ambos casos tendremos, para cada elemento del listado, botones para borrarlo o para editar sus datos, y un botón para añadir nuevas cualificaciones o unidades. El formulario de inserción/edición es el mismo, y servirá para rellenar o cambiar los datos básicos de la cualificación o la unidad:

<div align="center">
    <img src="img_doc/admin/cualificaciones_uc2.png" width="30%">
</div>

Además, en el caso de las cualificaciones profesionales disponemos de un tercer botón (ver imagen del listado anterior) para asociarle unidades de competencia. Al pulsarlo aparecerá otro formulario para poder añadir/borrar unidades asociadas a esa cualificación. Para borrar una UC basta con pulsar el botón de borrado que aparece a su izquierda, y para añadir nuevas las seleccionamos en el desplegable y pulsamos el botón de *Añadir*. Cuando hayamos terminado podemos pulsar el botón de *Cerrar* para cerrar el formulario.

<div align="center">
    <img src="img_doc/admin/cualificaciones_uc3.png" width="30%">
</div>


### 6.6. Gestión de los contenidos de las programaciones

Desde el menú *Programaciones > Programaciones* gestionamos los contenidos de los distintos apartados de cada programación. Como administradores primero tendremos que elegir el departamento con que trabajar, y luego la materia y el apartado a editar.

<div align="center">
    <img src="img_doc/admin/programaciones.png" width="80%">
</div>

Pulsando el botón de *Guardar cambios* en la parte inferior se guardan los cambios realizados para el apartado y materia elegidos. También tenemos botones en la parte superior para: 

* Tener una vista previa en HTML de toda la programación. Se mostrarán en rojo los apartados obligatorios que aún no se han rellenado.
* Generar un fichero PDF de la programación para la materia seleccionada
* Generar un fichero PDF del apartado indicado para la materia seleccionada
* Importar datos de una programación en la actual

### 6.7. Gestión del seguimiento de las programaciones

El seguimiento de las programaciones implica llevar un control trimestral de las mismas. Para ello tenemos disponible el menú *Programaciones > Seguimiento*. Como administradores, como es habitual, tendremos que elegir el departamento con que trabajar. Además, a la hora de hacer el seguimiento tendremos que elegir el curso que editar (por defecto queda seleccionado el curso actual), la evaluación y la materia.

<div align="center">
    <img src="img_doc/admin/programaciones_seguimiento.png" width="80%">
</div>

Bajo este primer panel aparece un formulario donde podemos editar la temporalización, los resultados obtenidos y el porcentaje de aprobados para la materia seleccionada. En el caso de que haya varios grupos de esa materia, todos deberán compartir estos datos e irlos acumulando.

<div align="center">
    <img src="img_doc/admin/programaciones_seguimiento2.png" width="80%">
</div>

Con el botón *Guardar cambios* de la parte inferior guardamos toda la información de golpe. Además, en la parte superior hay 3 botones adicionales:

* El primero servirá para importar los datos de la evaluación anterior en la nueva que hayamos seleccionada (servirá siempre que no hayamos elegido la 1ª evaluación)
* El segundo servirá para importar los datos del curso anterior, para la misma evaluación. Por ejemplo, para repetir un seguimiento similar en la Evaluación Final para un nuevo curso, basándonos en lo que escribimos en el curso anterior y modificando algunos datos.
* El tercer botón sirve para tener una vista previa de cómo quedan los datos introducidos (una vez guardados).

Finalmente, como administradores, disponemos de un tercer panel bajo el anterior para rellenar información de seguimiento común a todo el departamento seleccionado, para el curso y evaluación elegidas (para todas las materias).

<div align="center">
    <img src="img_doc/admin/programaciones_seguimiento3.png" width="80%">
</div>

Como vemos, se trata de indicar el funcionamiento general del departamento durante la evaluación, indicar las actividades extraescolares que se han llevado a cabo y la temporalización general de las materias. Nuevamente, el botón *Guardar cambios* inferior servirá para guardar todos estos datos comunes de golpe (independientemente de los anteriores) y, además, disponemos de cuatro botones en la parte superior:

* Importar datos de la evaluación anterior (funcionamiento similar al del seguimiento de materias, pero para importar los datos de los contenidos comunes)
* Importar datos del curso anterior (lo mismo pero basándonos en la misma evaluación del curso anterior)
* Vista previa de los resultados comunes
* Generar un PDF completo con el seguimiento tanto de los elementos comunes del departamento como desglosado para cada curso y materia.

### 6.8. Gestión de las unidades/temas de las programaciones

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

#### 6.8.1. Contenidos por defecto de los temas/unidades

Desde las programaciones didácticas se tiene un botón para editar los contenidos generales o por defecto que pueden compartir diversos temas de las materias.

<div align="center">
    <img src="img_doc/admin/contenidos_defecto_temas.png" width="50%">
</div>

Al pulsarlo se abre otra ventana donde podemos rellenar los contenidos de los distintos apartados y guardar los cambios. Estos contenidos van asociados a cada departamento (ya que un departamento puede tener contenidos diferentes para los temas de sus materias que otro).

<div align="center">
    <img src="img_doc/admin/contenidos_defecto_temas2.png" width="80%">
</div>

### 6.9. Programaciones de aula

Desde el submenú *Programaciones de aula* en la sección de *Programaciones* accedemos a la programación de aula de cada profesor para cada materia y grupo que imparte. En el formulario deberemos elegir la materia, el grupo y el tema, y rellenar el cuadro de texto con el seguimiento y temporización que se realice para ese tema.

<div align="center">
    <img src="img_doc/prof/programaciones_aula.png" width="70%">
</div>

En el caso de administradores, encima de este formulario aparecerá primero un desplegable para elegir al profesor al que se le quiere realizar la programación de aula.

<a name="pccf">

## 7. Gestión de PCCF

Los proyectos curriculares de ciclo (PCCF) se gestionan a través de los distintos menús en la sección de *Programaciones*.

### 7.1. Apartados del PCCF

Desde el submenú *Apartados PCCF* accedemos al listado de apartados que componen el PCCF

<div align="center">
    <img src="img_doc/admin/apartados_pccf.png" width="80%">
</div>

Su gestión es muy similar a la de los apartados de las programaciones que hemos explicado en una sección anterior. Consultar esa sección para más información.

### 7.2. Contenidos por defecto del PCCF

Desde el submenú *Contenidos grales. PCCF* podemos editar el valor por defecto de ciertos contenidos del PCCF.

<div align="center">
    <img src="img_doc/admin/contenidos_defecto_pccf.png" width="80%">
</div>

Basta con elegir el apartado a modificar, editar su contenido y guardar los cambios. El funcionamiento es similar a los contenidos por defecto de las programaciones didácticas.

### 7.3. Contenidos específicos del PCCF

Para gestionar los contenidos específicos de cada PCCF se tiene el submenú *PCCF*. Hay que elegir el ciclo con el que se quiere trabajar y el apartado a rellenar, e irlos rellenando de forma similar a los apartados de las programaciones.

<div align="center">
    <img src="img_doc/admin/pccf.png" width="80%">
</div>

Se dispone también de botones para previsualizar en HTML el contenido del PCCF, obtener el PDF y también ver en PDF algún apartado en concreto.