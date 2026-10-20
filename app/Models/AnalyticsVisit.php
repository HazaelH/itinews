<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AnalyticsVisit extends Model
{
    protected $table = 'analytics_visits';

    protected $fillable = [
        'visit_token',
        'visitor_token',
        'tipo',
        'revista_id',
        'user_id',
        'device_type',
        'os',
        'browser',
        'browser_version',
        'is_bot',
        'ip_hash',
        'user_agent',
        'referrer',
        'path',
        'pages_viewed',
        'total_pages',
        'duration_seconds',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'pages_viewed' => 'integer',
        'total_pages' => 'integer',
        'duration_seconds' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->visit_token)) {
                $model->visit_token = (string) Str::uuid();
            }
        });
    }

    public function revista(): BelongsTo
    {
        return $this->belongsTo(Revista::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pageEvents(): HasMany
    {
        return $this->hasMany(AnalyticsPageEvent::class, 'visit_id');
    }

    public function scopeRevistas($query)
    {
        return $query->where('tipo', 'revista');
    }

    public function scopePortal($query)
    {
        return $query->where('tipo', 'portal');
    }

    public function scopeNotBot($query)
    {
        return $query->where('is_bot', false);
    }

    /**
     * Porcentaje de la revista efectivamente leído (0-100).
     */
    public function getPorcentajeLecturaAttribute(): ?float
    {
        if (!$this->total_pages) {
            return null;
        }

        return round(min(100, ($this->pages_viewed / max(1, $this->total_pages)) * 100), 1);
    }
}
