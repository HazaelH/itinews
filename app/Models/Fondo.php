<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fondo extends Model
{
    protected $fillable = [
        'titulo',
        'origen',
        'archivo',
        'revista_id',
        'ubicacion',
        'tipo_vigencia',
        'recurrente',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'orden',
    ];

    protected $casts = [
        'recurrente' => 'boolean',
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'orden' => 'integer',
    ];

    protected $attributes = [
        'origen' => 'archivo',
        'ubicacion' => 'ambos',
        'tipo_vigencia' => 'siempre',
        'recurrente' => true,
        'activo' => true,
        'orden' => 0,
    ];

    public function revista(): BelongsTo
    {
        return $this->belongsTo(Revista::class);
    }

    /**
     * Solo fondos habilitados.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * ¿Aplica a la ubicación indicada (inicio|revistas)?
     */
    public function aplicaUbicacion(string $ubicacion): bool
    {
        return $this->ubicacion === 'ambos' || $this->ubicacion === $ubicacion;
    }

    /**
     * Prioridad de vigencia: conmemorativa > rango > siempre.
     */
    public function prioridad(): int
    {
        return match ($this->tipo_vigencia) {
            'conmemorativa' => 2,
            'rango' => 1,
            default => 0,
        };
    }

    /**
     * ¿La ventana configurada contiene la fecha dada?
     */
    public function estaVigenteHoy(?CarbonInterface $hoy = null): bool
    {
        $hoy = $hoy ?: now();

        if ($this->tipo_vigencia === 'siempre') {
            return true;
        }

        if (! $this->fecha_inicio || ! $this->fecha_fin) {
            return false;
        }

        if ($this->recurrente) {
            return self::contieneMesDia($hoy, $this->fecha_inicio, $this->fecha_fin);
        }

        return $hoy->toDateString() >= $this->fecha_inicio->toDateString()
            && $hoy->toDateString() <= $this->fecha_fin->toDateString();
    }

    /**
     * Compara por (mes, día) permitiendo rangos que cruzan el fin de año
     * (p. ej. 21 dic → 20 ene).
     */
    public static function contieneMesDia(CarbonInterface $hoy, CarbonInterface $inicio, CarbonInterface $fin): bool
    {
        $h = $hoy->month * 100 + $hoy->day;
        $a = $inicio->month * 100 + $inicio->day;
        $b = $fin->month * 100 + $fin->day;

        if ($a <= $b) {
            return $h >= $a && $h <= $b;
        }

        return $h >= $a || $h <= $b;
    }

    /**
     * URL pública de la imagen del fondo (o null si no hay imagen utilizable).
     */
    public function url(): ?string
    {
        if ($this->origen === 'portada') {
            return $this->revista?->urlPortada();
        }

        if (! $this->archivo) {
            return null;
        }

        return asset('storage/' . preg_replace('#/+#', '/', ltrim($this->archivo, '/')));
    }

    /**
     * Texto legible de la vigencia.
     */
    public function vigenciaTexto(): string
    {
        if ($this->tipo_vigencia === 'siempre') {
            return 'Siempre activo';
        }

        $inicio = $this->fecha_inicio?->format('d/m');
        $fin = $this->fecha_fin?->format('d/m');

        if (! $inicio || ! $fin) {
            return 'Sin fechas';
        }

        $base = $inicio === $fin ? $inicio : $inicio.' → '.$fin;

        return $base . ($this->recurrente ? ' (cada año)' : '');
    }
}
