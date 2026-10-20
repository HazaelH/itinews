<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Purga automática de métricas antiguas (una vez al mes) según la retención configurada.
Schedule::command('analytics:purge')->monthlyOn(1, '03:00');
