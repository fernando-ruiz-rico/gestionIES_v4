<?php
    
    /* Este fichero comprueba si los parámetros de configuración de
     * 'programaciones' y 'desideratas' están activos, y lo guarda en
     * dos variables booleanas que luego podemos utilizar en distintas
     * partes del código para mostrar/ocultar elementos relativos a estos
     * apartados, según si queremos que se puedan editar o no
     * 
     * Por ejemplo, si ya ha pasado el plazo para hacer las programaciones,
     * el parámetro correspondiente de la base de datos debe establecerse
     * a "OFF", y en ese caso los usuarios no deben poder editar sus
     * programaciones desde la plataforma (sólo consultarlas).
     */ 

    include('database.php');
        
    // Programaciones
    $result = mysqli_query($db, "SELECT valor FROM config WHERE clave='programaciones'");
    $fila = mysqli_fetch_assoc($result);
    $programacionesActivadas = $fila['valor'] == 'ON'? TRUE : FALSE;
    mysqli_free_result($result);

    // Desideratas
    $result = mysqli_query($db, "SELECT valor FROM config WHERE clave='desideratas'");
    $fila = mysqli_fetch_assoc($result);
    $desideratasActivadas = $fila['valor'] == 'ON'? TRUE : FALSE;
    mysqli_free_result($result);
    
    include('database2.php');
    
?>
