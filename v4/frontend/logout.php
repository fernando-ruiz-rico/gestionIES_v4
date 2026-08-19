<?php

    // Página de logout, borra las credenciales del usuario en la sesión

    @session_start();
    
    unset($_SESSION['idUsuario']);
    unset($_SESSION['rol']);
    unset($_SESSION['loginUsuario']);
    unset($_SESSION['nombreUsuario']);
    unset($_SESSION['especialidadUsuario']);
    unset($_SESSION['departamentoUsuario']);

    session_destroy();
    
    header("Location: login.php");
    
?>