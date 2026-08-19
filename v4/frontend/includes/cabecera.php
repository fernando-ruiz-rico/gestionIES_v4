<?php
/**
 * Cabecera común para todas las páginas del frontend
 * Incluye sesión, menú y estilos
 */

// Comprobamos si hay usuario en sesión, y si no lo hay redirigimos a login
@session_start();
if (empty($_SESSION['idUsuario']))
    header("Location: ../backend/login.php");

// Si se han establecido roles específicos para cargar la página, se comprueban también
if(isset($roles) && !in_array($_SESSION['rol'], $roles))
    header("Location: ../backend/login.php");

// Con este include guardamos en booleanos la activación o no de distintas 
// secciones de la web
include('../backend/includes/comprobar_activaciones.php');
// Este include carga las opciones que luego se recorren para mostrar en el menú
include('../backend/includes/config.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestión interna IESSV</title>

    <!-- Bootstrap 5.3.8 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <!-- Bootstrap Icons 1.13.1 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
    
    <!-- Estilos mínimos personalizados -->
    <link rel="stylesheet" href="css/estilos.css" />
</head>
<body>

<div class="d-flex" id="wrapper">

    <?php
        // Menú de opciones
        include('menu.php');
        // Ventana modal para mostrar mensajes cortos
        include('modales/mensaje.php');

        // Si llega un parámetro "idDepartamento", lo guardamos en sesión
        if(!empty($_REQUEST['idDepartamento']))
        {
            $_SESSION['departamentoUsuario'] = $_REQUEST['idDepartamento'];
        }

        // Guardamos también si el usuario actual tiene permisos de edición generales
        $permisos = isset($_SESSION['rol']) && ($_SESSION['rol'] == 'jefeDepartamento' || $_SESSION['rol'] == 'admin');
        $permisosAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';
    ?>

    <div id="page-content-wrapper" class="container-fluid">
