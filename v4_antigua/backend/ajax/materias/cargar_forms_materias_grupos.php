<?php

// Carga un formulario por cada grupo de una materia y curso indicados, para editar
// la información particular de esa materia para ese curso

if (!empty($_REQUEST['idCurso']) && !empty($_REQUEST['idMateria']))
{
    include('../../includes/database.php');
    
    $result = mysqli_query($db, "SELECT cursos.nombre as nomCurso, materias.nombre AS nomMateria FROM cursos, materias WHERE cursos.id = materias.idCurso and cursos.id = " . $_REQUEST['idCurso'] . " AND materias.id = " . $_REQUEST['idMateria']);
    $fila = mysqli_fetch_assoc($result);
    $nomMateria = $fila['nomMateria'];
    $nomCurso = $fila['nomCurso'];
    mysqli_free_result($result);

    echo '<h4>' . $nomCurso . ' - ' . $nomMateria . '</h4>';
?>
    <!-- Botón para cargar en los formularios los datos referencia de la materia -->
    <div style="text-align:center">
        <button class="btn btn-light" type="button" onclick="importarDatos(<?=$_REQUEST['idMateria']?>, <?=$_REQUEST['idCurso']?>)">Importar datos generales de materia</button>
    </div>

<?php
    $result = mysqli_query($db, "SELECT * FROM grupos WHERE idCurso = " . $_REQUEST['idCurso'] . " ORDER BY orden");
    while($fila = mysqli_fetch_assoc($result))
    {
        echo '<h5 style="border:1px solid black">' . $nomCurso .  ' ' . $fila['nombre'] . '</h6>';
        $idGrupo = $fila['id'];
        $cantidad = "";
        $horas = "";
        $horasComplementarias = "";
        $minProfesores = "";
        $maxGruposProf = "";
        if($_REQUEST['importar'] == 1)
        {
            $resultGeneral = mysqli_query($db, "SELECT * FROM materias WHERE id = " . $_REQUEST['idMateria']);
            $filaGeneral = mysqli_fetch_assoc($resultGeneral);
            $cantidad = $filaGeneral['cantidad'];
            $horas = $filaGeneral['horas'];
            $horasComplementarias = $filaGeneral['horas_complementarias'];
            $minProfesores = $filaGeneral['min_num_profesores'];
            $maxGruposProf = $filaGeneral['max_grupos_profesor'];            
            mysqli_free_result($resultGeneral);
        }
        else
        {
            $existeGrupo = mysqli_query($db, "SELECT * FROM materias_grupos WHERE idMateria = " . $_REQUEST['idMateria'] . ' AND idGrupo = ' . $fila['id']);
            if($fila2 = mysqli_fetch_assoc($existeGrupo))
            {
                $cantidad = $fila2['cantidad'];
                $horas = $fila2['horas'];
                $horasComplementarias = $fila2['horas_complementarias'];
                $minProfesores = $fila2['min_num_profesores'];
                $maxGruposProf = $fila2['max_grupos_profesor'];            
            }
            mysqli_free_result($existeGrupo);
        }
?>
    <form class="subformulario" id="formmatgr<?=$idGrupo?>" name="formmatgr<?=$idGrupo?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="idMateria" id="idMateria<?=$idGrupo?>" value="<?=$_REQUEST['idMateria']?>">
        <input type="hidden" name="idGrupo" id="idGrupo<?=$idGrupo?>" value="<?=$idGrupo?>">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label" for="cantidad">Cantidad de unidades por grupo</label>
                    <input class="form-control" type="number" name="cantidad" id="cantidad<?=$idGrupo?>" value="<?=$cantidad?>" required>
                </div>
                <div class="form-group">
                    <label class="control-label" for="horas">Horas / semana</label>
                    <input class="form-control" type="number" name="horas" id="horas<?=$idGrupo?>" value="<?=$horas?>" required>
                </div>
                <div class="form-group">
                    <label class="control-label" for="horasComplementarias">Horas complementarias / semana</label>
                    <input class="form-control" type="number" name="horasComplementarias" id="horasComplementarias<?=$idGrupo?>" value="<?=$horasComplementarias?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label" for="minNumProfesores">Mín. profesores (0 para no limitar)</label>
                    <input class="form-control" type="number" name="minNumProfesores" id="minNumProfesores<?=$idGrupo?>" value="<?=$minProfesores?>" required>
                </div>
                <div class="form-group">
                    <label class="control-label" for="maxGruposProfesor">Máx. grupos por profesor (0 para no limitar)</label>
                    <input class="form-control" type="number" name="maxGruposProfesor" id="maxGruposProfesor<?=$idGrupo?>" value="<?=$maxGruposProf?>" required>
                </div>
                <div class="form-group" style="text-align:center">
                    <button class="btn btn-success" type="submit">Guardar</button>
                </div>
            </div>
        </div>
    </form>

<?php
    }
    mysqli_free_result($result);
    include('../../includes/database2.php');
}

?>

<script type="text/javascript">
// Evento "submit" en cada subformulario para editar las características de la materia en cada grupo
dom(".subformulario").on("submit", function(e)
{
    e.preventDefault();
    var formData = new FormData(this);
    http.ajax({
        url: "ajax/materias/insertar_materia_grupo.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res){
        if(res == 'si')
            alert("Error actualizando datos");
        else
            alert("Datos actualizados");
    });
});
</script>