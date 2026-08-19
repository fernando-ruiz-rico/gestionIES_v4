<?php
    // -------------------------------
    // Genera el contenido HTML para el apartado de "Resultados de Aprendizaje de Formación en Empresa"
    // -------------------------------
    function generarContenidoResultadosAprendizaje($idMateria, $horasEmpresa)
    {
        $sql = "SELECT * FROM resultados_aprendizaje WHERE idMateria = $idMateria ORDER BY orden";
        $resultados = consultarBaseDeDatos($sql);

        if (empty($resultados)) {
            return array('existe' => false, 'texto' => '');
        }

        $html = "Horas destinadas a la empresa: {$horasEmpresa}<br><br>";

        $html .= "<table border=\"1\" cellpadding=\"5\">
                    <thead>
                        <tr>
                            <th align=\"center\" width=\"75%\" colspan=\"2\">Resultados de aprendizaje</th>
                            <th align=\"center\" width=\"12%\">Empresa</th>
                            <th align=\"center\" width=\"13%\">Centro educativo</th>
                        </tr>
                    </thead>";

        $html .= '<tbody>';
        foreach ($resultados as $ra) {
            $raNumero = 'RA' . $ra['orden'];
            $porcEmpresa = (int)$ra['porcentaje_empresa'];
            $porcCentro = 100 - $porcEmpresa;
            $html .= "<tr nobr=\"true\">
                        <td align=\"center\" width=\"10%\">{$raNumero}</td>
                        <td width=\"65%\">{$ra['texto']}</td>
                        <td align=\"center\" width=\"12%\">{$porcEmpresa}%</td>
                        <td align=\"center\" width=\"13%\">{$porcCentro}%</td>
                    </tr>";
        }
        $html .= '</tbody></table>';

        return  $html;
    }
?>