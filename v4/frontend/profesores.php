<?php
    // Vista principal para la gestión de profesores
    $roles = ['admin'];
    include('includes/cabecera.php');
?>

<div class="panelcentral">

    <h1>Profesores por departamento</h1>

    <?php
        // Desplegable para elegir el departamento sobre el que trabajar
        include('includes/seleccion_departamento.php');
    ?>

    <br />
    <p><em>Arrastra los profesores para ordenarlos entre sí. Haz clic en el icono del lápiz para editar los datos de cada profesor, y en el icono de Nuevo al final para añadir nuevos. Puedes eliminar profesores con el icono de borrar junto a cada apartado. También puedes elegir al jefe de departamento haciendo clic en el icono de la medalla (aparece en verde el icono para el actual jefe de departamento)</em></p>
    <!-- En este div se carga por AJAX el listado de especialidades -->
    <div id="listaprofesores"></div>
    <!-- Botón para abrir el diálogo modal para crear nuevos profesores -->
    <div style="text-align: center"><button class="btn btn-light" onclick="nuevoProfesor()"><i class="bi bi-plus"></i> Nuevo Profesor</button></div>
</div>

<!-- Aquí no se incluye ningún diálogo modal porque ya está incluido desde la cabecera, se llama
     modales/profesor.php y sirve para editar datos del profesor seleccionado tanto desde aquí
     como desde el Perfil del profesor que ha accedido a la aplicación -->

<!-- Código JavaScript auxiliar para comunicar con el servidor y pedir datos sobre especialidades -->
<script src="js/profesores.js?v=1"></script>	
<?php
if(isset($_SESSION['departamentoUsuario']))
{
?>
    <script type="text/javascript">
        seleccionarDepartamento();
    </script>
<?php
}
?>

<?php
    include('includes/pie.php');
?>