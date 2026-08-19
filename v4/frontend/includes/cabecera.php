<?php
    // Comprobamos si hay usuario en sesión, y si no lo hay redirigimos a login
    @session_start();
    if (empty($_SESSION['idUsuario']))
        header("Location: ../backend/login.php");
    
    // Si se han establecido roles específicos para cargar la página, se comprueban también
    if(isset($roles) && !in_array($_SESSION['rol'], $roles))
        header("Location: ../backend/login.php");

    // Con este include guardamos en booleanos la activación o no de distintas 
    // secciones de la web, como las programaciones o desideratas, para habilitar
    // o deshabilitar ciertos apartados en función de eso
    include('../backend/includes/comprobar_activaciones.php');
    // Este include carga las opciones que luego se recorren para mostrar en el menú
    include('../backend/includes/config.php');
?>
<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Gestión interna IESSV</title>

        <!-- Carga de CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <!-- Estilos generales de la web (aparte de los de Bootstrap) -->
        <link rel="stylesheet" href="css/estilos.css?v=3" />
        <!-- Estilos específicos para el menú -->
        <link rel="stylesheet" href="css/menu.css?v=1" />

        <!-- Carga de librerías JavaScript -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="../backend/lib/js/tinymce/tinymce.min.js?v=7.9.1"></script>
    </head>
    <body>
   
    <div class="d-flex" id="wrapper">

        <?php
            // Menú de opciones
            include('menu.php');
            // Ventana modal para mostrar mensajes cortos
            include('modales/mensaje.php');


            // Si llega un parámetro "idDepartamento", lo guardamos en sesión para usar los contenidos de ese departamento

            if(!empty($_REQUEST['idDepartamento']))
            {
                $_SESSION['departamentoUsuario'] = $_REQUEST['idDepartamento'];
            }

            // Guardamos también si el usuario actual tiene permisos de edición generales (admins o jefes de departamento)
            $permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
            $permisosAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';
        ?>

        <!-- Funciones globales de JavaScript para los modales anteriores -->
        <script type="text/javascript" src="js/main.js?v=8"></script>

        <div id="page-content-wrapper" class="container-fluid">
