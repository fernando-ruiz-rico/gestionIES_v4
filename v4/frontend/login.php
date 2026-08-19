<?php

    // Página de login, con una estructura ligeramente distinta al resto de páginas principales

    @session_start();
    
    if (!empty($_REQUEST['password']))
    {
        include('includes/database.php');
        // Miramos primero si el login es de admin
        $resultAdm = mysqli_query($db, "SELECT * FROM config WHERE clave='admin' AND 'admin' = '" . $_REQUEST['login'] . "' AND valor = '" . md5($_REQUEST['password']) . "'");
        if (mysqli_num_rows($resultAdm) > 0)
        {
            // Credenciales para admin
            $_SESSION['idUsuario'] = 'admin';
            $_SESSION['rol'] = 'admin';
            $_SESSION['loginUsuario'] = 'admin';
        } else {
            // Permitimos entrar a profesores con credenciales correctas y actualmente activos
            $resultUsu = mysqli_query($db, "SELECT * FROM profesores WHERE usuario='" . $_REQUEST['login'] . "' AND clave = '" . md5($_REQUEST['password']) . "' AND activo = 1");
            if (mysqli_num_rows($resultUsu) > 0)
            {
                // Credenciales para profesor
                $fila = mysqli_fetch_assoc($resultUsu);
                $_SESSION['idUsuario'] = $fila['id'];
                $_SESSION['nombreUsuario'] = $fila['nombre'];
                $_SESSION['loginUsuario'] = $fila['usuario'];
                if (!empty($fila['idEspecialidad']))
                    $_SESSION['especialidadUsuario'] = $fila['idEspecialidad'];
                if (!empty($fila['idDepartamento']))
                    $_SESSION['departamentoUsuario'] = $fila['idDepartamento'];
                if ($fila['jefe_departamento'])
                    $_SESSION['rol'] = 'jefeDepartamento';
                else
                    $_SESSION['rol'] = 'profesor';
            }
            mysqli_free_result($resultUsu);
        }
        mysqli_free_result($resultAdm);
        include('includes/database2.php');
        @header("Location: index.php");
    }
?>

<!DOCTYPE html>

<!-- A diferencia de la mayoría de páginas, el login no incluye la "cabecera.php" general, para no
     mostrar el menú de operaciones. Sólo los estilos básicos y el formulario para loguearse -->

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">                
        <title>Gestión interna IESSV</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="css/estilos.css?v=3" />
    </head>
    <body>
        <div id="formlogin" class="muyoscuro">
            <form action="" method="post">
                <label for="login" class="cabeceraformlogin">
                    Introduce tu nombre de usuario
                </label>
                <div class="campoformlogin claro">
                    <input type="text" id="login" name="login" class="form-control" />
                </div>
                <label for="password" class="cabeceraformlogin">
                    Introduce tu clave
                </label>
                <div class="campoformlogin claro">
                    <input type="password" id="password" name="password" class="form-control" />
                </div>
                <div class="cabeceraformlogin">
                    <input type="submit" value="Entrar" class="btn btn-light" />
                </div>
            </form>
        </div>
        
    </body>
</html>