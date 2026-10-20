<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FondoPreset extends Model
{
    protected $table = 'fondo_presets';

    protected $fillable = [
        'tipo',
        'nombre',
        'dia',
        'desde',
        'hasta',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    /**
     * Presets por defecto para conmemorativas y rangos (estaciones y meses).
     */
    public static function defaults(): array
    {
        return [
            // Conmemorativas
            ['tipo' => 'conmemorativa', 'nombre' => 'Año Nuevo', 'dia' => '01-01', 'orden' => 1],
            ['tipo' => 'conmemorativa', 'nombre' => 'San Valentín', 'dia' => '02-14', 'orden' => 2],
            ['tipo' => 'conmemorativa', 'nombre' => 'Día de la Madre', 'dia' => '05-10', 'orden' => 3],
            ['tipo' => 'conmemorativa', 'nombre' => 'Día del Maestro', 'dia' => '05-15', 'orden' => 4],
            ['tipo' => 'conmemorativa', 'nombre' => 'Día del Ingeniero', 'dia' => '07-01', 'orden' => 5],
            ['tipo' => 'conmemorativa', 'nombre' => 'Día del Programador', 'dia' => '09-13', 'orden' => 6],
            ['tipo' => 'conmemorativa', 'nombre' => 'Fiestas Patrias', 'dia' => '09-15', 'orden' => 7],
            ['tipo' => 'conmemorativa', 'nombre' => 'Día de Muertos', 'dia' => '11-01', 'orden' => 8],
            ['tipo' => 'conmemorativa', 'nombre' => 'Navidad', 'dia' => '12-25', 'orden' => 9],
            ['tipo' => 'conmemorativa', 'nombre' => 'Fin de Año', 'dia' => '12-31', 'orden' => 10],

            // Rangos
            ['tipo' => 'rango', 'nombre' => 'Primavera', 'desde' => '03-20', 'hasta' => '06-20', 'orden' => 1],
            ['tipo' => 'rango', 'nombre' => 'Verano', 'desde' => '06-21', 'hasta' => '09-22', 'orden' => 2],
            ['tipo' => 'rango', 'nombre' => 'Otoño', 'desde' => '09-23', 'hasta' => '12-20', 'orden' => 3],
            ['tipo' => 'rango', 'nombre' => 'Invierno', 'desde' => '12-21', 'hasta' => '03-19', 'orden' => 4],
            ['tipo' => 'rango', 'nombre' => 'Febrero', 'desde' => '02-01', 'hasta' => '02-28', 'orden' => 5],
            ['tipo' => 'rango', 'nombre' => 'Mayo', 'desde' => '05-01', 'hasta' => '05-31', 'orden' => 6],
            ['tipo' => 'rango', 'nombre' => 'Septiembre', 'desde' => '09-01', 'hasta' => '09-30', 'orden' => 7],
            ['tipo' => 'rango', 'nombre' => 'Octubre', 'desde' => '10-01', 'hasta' => '10-31', 'orden' => 8],
            ['tipo' => 'rango', 'nombre' => 'Noviembre', 'desde' => '11-01', 'hasta' => '11-30', 'orden' => 9],
            ['tipo' => 'rango', 'nombre' => 'Diciembre', 'desde' => '12-01', 'hasta' => '12-31', 'orden' => 10],
        ];
    }

    /**
     * Asegura que existan los datos predeterminados.
     */
    public static function asegurarDefaults(): void
    {
        if (self::query()->count() === 0) {
            foreach (self::defaults() as $def) {
                self::create($def);
            }
        }
    }
}
