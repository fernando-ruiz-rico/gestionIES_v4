<?php
// ============================================================================
// Funciones y clase compartidas de los PDF de horarios (desideratas y
// preferencias)
// ============================================================================
//
// pdf_desiderata.php y pdf_preferencias.php usaban copias idénticas de xDia()
// y de la clase MiPDF (FPDI), con un único matiz: el fichero de plantilla.
// Aquí van las piezas comunes; la plantilla se indica en la propiedad pública
// $plantilla antes del primer addPage().
//
// NOTA: la función yHora() NO se comparte porque en cada endpoint tiene una
// implementación distinta (una consulta la tabla "horas" y la otra es un
// mapa fijo), y por tanto no son la misma función.
//
// Debe cargarse DESPUÉS de lib/php/fpdi/fpdi.php (la clase extiende FPDI).

// Devuelve un código numérico asociado al día de la semana (Lunes => 0, Viernes => 4)
function xDia($dia)
{
    if ($dia == 'L') return 0;
    elseif ($dia == 'M') return 1;
    elseif ($dia == 'X') return 2;
    elseif ($dia == 'J') return 3;
    else return 4;
}

// Base de los PDF de plantilla (FPDI). El fichero de plantilla se pasa en la
// propiedad pública $plantilla antes del primer addPage(); Header() lo carga
// una sola vez (la primera vez que se dibuja una página).
class MiPDF extends FPDI
{
    var $_tplIdx;
    var $plantilla;

    // Cabecera de las páginas
    public function Header()
    {
        if (is_null($this->_tplIdx))
        {
            $this->setSourceFile($this->plantilla);
            $this->_tplIdx = $this->importPage(1);
        }
        $this->useTemplate($this->_tplIdx);
    }

    // Pie de las páginas
    public function Footer()
    {
    }
}
