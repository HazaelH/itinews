<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Revista extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_publicacion',
        'ruta_portada',
        'ruta_pdf',
        'estado_procesamiento',
        'progreso',
        'total_paginas',
        'activo',
        'destacada'
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'activo' => 'boolean',
        'destacada' => 'boolean',
    ];

    protected $attributes = [
        'estado_procesamiento' => 'pendiente',
        'progreso' => 0,
        'total_paginas' => 0,
        'activo' => true,
        'destacada' => false,
    ];

    public function paginas(): HasMany
    {
        return $this->hasMany(Pagina::class);
    }

    /**
     * Solo revistas visibles en el portal.
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Solo revistas marcadas como destacadas.
     */
    public function scopeDestacadas($query)
    {
        return $query->where('destacada', true);
    }

    /**
     * Orden cronológico de publicación: más recientes primero.
     * En caso de empate en la fecha de publicación, desempata por creación más reciente.
     */
    public function scopeRecientes($query)
    {
        return $query->orderBy('fecha_publicacion', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function estaActiva(): bool
    {
        // Las revistas existentes sin el campo definido se consideran activas.
        return (bool) ($this->activo ?? true);
    }

    public function esDestacada(): bool
    {
        return (bool) ($this->destacada ?? false);
    }

    /**
     * URL pública de una portada a partir de su ruta relativa.
     * Normaliza barras duplicadas (algunas rutas guardadas traen "//").
     */
    public static function urlDePortada(?string $ruta): ?string
    {
        if (! $ruta) {
            return null;
        }

        $limpia = preg_replace('#/+#', '/', 'storage/' . ltrim($ruta, '/'));

        return asset($limpia);
    }

    /**
     * URL pública de la portada de esta revista (o null si no tiene).
     */
    public function urlPortada(): ?string
    {
        return self::urlDePortada($this->ruta_portada);
    }

    public function estaProcesada()
    {
        return $this->estado_procesamiento === 'completado';
    }

    public function tieneError()
    {
        return $this->estado_procesamiento === 'error';
    }

    public function estaProcesando()
    {
        return $this->estado_procesamiento === 'procesando';
    }

    public function getProgresoPorcentajeAttribute()
    {
        return round($this->progreso);
    }
}