<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagina extends Model
{
    use HasFactory;

    protected $fillable = [
        'revista_id',
        'numero_pagina',
        'ruta_imagen',
    ];

    public function revista(): BelongsTo
    {
        return $this->belongsTo(Revista::class);
    }
}