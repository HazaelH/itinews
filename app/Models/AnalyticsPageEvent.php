<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsPageEvent extends Model
{
    protected $table = 'analytics_page_events';

    protected $fillable = [
        'visit_id',
        'revista_id',
        'page_number',
        'viewed_at',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'viewed_at' => 'datetime',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(AnalyticsVisit::class, 'visit_id');
    }

    public function revista(): BelongsTo
    {
        return $this->belongsTo(Revista::class);
    }
}
