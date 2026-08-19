<?php
/*
Página con las opciones de configuración de la aplicación. Accede a la tabla 'config' de la base de
datos para establecer los valores de los distintos parámetros de configuración:

- Contraseña del usuario "admin" (se guarda encriptada)
- Si las desideratas están activas o no
- Si las programaciones didácticas están activas o no
*/

$roles = ['admin'];
include('includes/cabecera.php');
include('includes/database.php');

// Cambiar password de administrador

if (!empty($_REQUEST['cambiarpassword']))
{
    $antiguo = $_REQUEST['antiguo'];
    $nuevo = $_REQUEST['nuevo'];
    $repetirNuevo = $_REQUEST['repetirnuevo'];
    $error = FALSE;
    
    if ($nuevo != $repetirNuevo)
    {
        $error = TRUE;
    } else {
        mysqli_query($db, "UPDATE config SET valor = '" . md5($nuevo) . "' WHERE clave = 'admin' AND valor = '" . md5($antiguo) . "'");
        $result = mysqli_affected_rows($db);
        if($result == 0)
            $error = TRUE;
    }
    
// Activar / Desactivar periodos (desideratas, programaciones...)
    
} else if (!empty($_REQUEST['activardesideratas']) || !empty($_REQUEST['activarprogramaciones'])) {
    $valor = $_REQUEST['valor'];
    $valor = $valor=='OFF'?'ON':'OFF';
    if (!empty($_REQUEST['activardesideratas']))
        mysqli_query($db, "UPDATE config SET valor = '" . $valor . "' WHERE clave = 'desideratas'");
    else
        mysqli_query($db, "UPDATE config SET valor = '" . $valor . "' WHERE clave = 'programaciones'");
}

// Guardamos el estado actual de las activaciones para mostrarlo luego en la página

$result = mysqli_query($db, "SELECT * FROM config WHERE clave='programaciones'");
$programaciones = mysqli_fetch_assoc($result)['valor'];
mysqli_free_result($result);

$result = mysqli_query($db, "SELECT * FROM config WHERE clave='desideratas'");
$desideratas = mysqli_fetch_assoc($result)['valor'];
mysqli_free_result($result);

include('includes/database2.php');

if($_SESSION['rol'] == 'admin')
{
?>

<div class="panelcentral">

    <h1>Opciones de configuración</h1>

    <div class="row">
        <div class="col-md-6">
            <fieldset>
                <legend>Password administrador</legend>
                <div id="resultado">
                    <?php
                        if (isset($error))
                        {
                                if ($error)
                                {
                                    echo '<p class="alert alert-danger">Error al realizar la operación indicada</p>';
                                }
                                else
                                {
                                    echo '<p class="alert alert-success">Operación realizada correctamente</p>';
                                }
                        }
                    ?>
                </div>
                <form action="" id="formconfig" name="formconf" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="cambiarpassword" value="si" />
                    <input class="form-control" type="password" placeholder="Password antiguo" name="antiguo" required /><br />
                    <input class="form-control" type="password" placeholder="Nuevo password" name="nuevo" required /><br />
                    <input class="form-control" type="password" placeholder="Repetir nuevo password" name="repetirnuevo" required /><br />
                    <button class="btn btn-light" type="submit">Enviar</button><br />
                </form>
            </fieldset>
        </div>
        <div class="col-md-6">
            <fieldset>
                <legend>Activaciones</legend>
                <label for="programaciones">Programaciones activadas: </label>
                <?php
                    if ($programaciones == 'ON')
                        echo '<button id="btnprogramaciones" class="btn btn-success">ON</button>';
                    else
                        echo '<button id="btnprogramaciones" class="btn btn-danger">OFF</button>';
                ?>
                <br /><br />
                <label for="desideratas">Desideratas activadas: </label>
                <?php
                    if ($desideratas == 'ON')
                        echo '<button id="btndesideratas" class="btn btn-success">ON</button>';
                    else
                        echo '<button id="btndesideratas" class="btn btn-danger">OFF</button>';
                ?>
                <br />
            </fieldset>
        </div>
    </div>
    
</div>

<script type="text/javascript" src="js/configuracion.js"></script>

<?php
}
include('includes/pie.php');
?>