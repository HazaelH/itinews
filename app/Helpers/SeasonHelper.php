<?php

namespace App\Helpers;

use Carbon\Carbon;

class SeasonHelper
{
    public static function getCurrentImage()
    {
        $now = Carbon::now();
        
        $month = $now->month;
        $day = $now->day;

        // 1. Validaciones de Días Festivos (Tienen la mayor prioridad)
        if ($now->dayOfYear === 256) {
            return 'programador.jpg';
        }

        if ($month == 9 && ($day >= 10 && $day <= 17)) {
            return 'fiestas_patrias.jpg';
        }

        if (($month == 10 && $day >= 28) || ($month == 11 && $day <= 3)) {
            return 'dia_muertos.jpg';
        }

        if ($month == 2 && ($day >= 10 && $day <= 16)) {
            return 'san_valentin.jpg';
        }

        if ($month == 5 && ($day >= 20 && $day <= 27)) {
            return 'estudiante.jpg';
        }
        
        // 2. Ventanas para las Temporadas Climáticas (Ejemplo: 30 días de duración)
        
        // Primaveral: 21 de Marzo al 20 de Abril
        if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 20)) {
            return 'primavera.jpg';
        }

        // Estival: 21 de Junio al 20 de Julio
        if (($month == 6 && $day >= 21) || ($month == 7 && $day <= 20)) {
            return 'verano.jpg';
        }

        // Otoñal: 23 de Septiembre al 22 de Octubre
        if (($month == 9 && $day >= 23) || ($month == 10 && $day <= 22)) {
            return 'otono.jpg';
        }

        // Invernal: 21 de Diciembre al 20 de Enero (Maneja el cruce de fin de año perfectamente)
        if (($month == 12 && $day >= 21) || ($month == 1 && $day <= 20)) {
            return 'invierno.jpg';
        }

        // 3. Recurso Visual Estándar (Fallback)
        // Se ejecutará en cualquier fecha que no coincida con los rangos anteriores.
        return 'inicio.jpg';
    }
}