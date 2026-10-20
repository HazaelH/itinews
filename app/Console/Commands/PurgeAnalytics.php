<?php

namespace App\Console\Commands;

use App\Models\AnalyticsVisit;
use Illuminate\Console\Command;

class PurgeAnalytics extends Command
{
    /**
     * @var string
     */
    protected $signature = 'analytics:purge {--months= : Meses a conservar (0 = indefinido)}';

    /**
     * @var string
     */
    protected $description = 'Elimina las visitas de métricas más antiguas que el periodo de retención.';

    public function handle(): int
    {
        $months = $this->option('months');

        if ($months === null) {
            $months = (int) config('analytics.retention_months', 12);
        }

        $months = (int) $months;

        if ($months <= 0) {
            $this->info('Retención indefinida configurada (0 meses). No se elimina nada.');
            return self::SUCCESS;
        }

        $limite = now()->subMonths($months);

        $eliminadas = AnalyticsVisit::where('created_at', '<', $limite)->delete();

        $this->info(sprintf(
            'Se eliminaron %d visitas anteriores a %s (retención: %d meses).',
            $eliminadas,
            $limite->toDateString(),
            $months
        ));

        return self::SUCCESS;
    }
}
