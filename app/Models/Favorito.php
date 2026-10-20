<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorito extends Model
{
    protected $table = 'favoritos';

    protected $fillable = [
        'user_id',
        'revista_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revista(): BelongsTo
    {
        return $this->belongsTo(Revista::class);
    }
}
