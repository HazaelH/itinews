<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Analíticas del portal
    |--------------------------------------------------------------------------
    |
    | Interruptor global del sistema de métricas. Si está en false, los
    | endpoints de ingesta no registran nada (útil para entornos de prueba).
    |
    */

    'enabled' => env('ANALYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Retención de datos (meses)
    |--------------------------------------------------------------------------
    |
    | Meses que se conservan las visitas antes de que el comando
    | `analytics:purge` las elimine. Usa 0 para conservarlas indefinidamente.
    |
    */

    'retention_months' => (int) env('ANALYTICS_RETENTION_MONTHS', 12),

    /*
    |--------------------------------------------------------------------------
    | Sal (salt) para el hash de IP
    |--------------------------------------------------------------------------
    |
    | Nunca almacenamos la IP en claro. Se guarda un hash SHA-256 de la IP
    | combinada con esta sal para poder contar visitantes únicos sin guardar
    | datos personales.
    |
    */

    'salt' => env('ANALYTICS_SALT', env('APP_KEY', 'itinews-analytics')),

    /*
    |--------------------------------------------------------------------------
    | Cookie de visitante
    |--------------------------------------------------------------------------
    |
    | Nombre y duración (días) de la cookie de primer nivel que identifica a un
    | visitante anónimo y recurrente.
    |
    */

    'cookie_name' => 'itin_visitor',

    'cookie_lifetime_days' => 365,

];
