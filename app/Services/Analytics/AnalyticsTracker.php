<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsPageEvent;
use App\Models\AnalyticsVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Registra las métricas de visitas anónimas del portal y del visor.
 *
 * Toda la información es anónima: se identifica al visitante con un token
 * aleatorio (cookie/localStorage) y la IP se guarda únicamente como hash.
 */
class AnalyticsTracker
{
    public function __construct(private readonly DeviceDetectorService $detector)
    {
    }

    public function enabled(): bool
    {
        return (bool) config('analytics.enabled', true);
    }

    /**
     * Hash irreversible de la IP (nunca se guarda la IP en claro).
     */
    public function hashIp(?string $ip): ?string
    {
        $ip = trim((string) $ip);

        if ($ip === '') {
            return null;
        }

        return hash('sha256', $ip . '|' . (string) config('analytics.salt'));
    }

    /**
     * Abre una visita (portal o revista).
     */
    public function startVisit(Request $request, string $tipo, string $visitorToken, ?int $revistaId = null, ?int $totalPages = null): ?AnalyticsVisit
    {
        if (!$this->enabled()) {
            return null;
        }

        $info = $this->detector->parse($request->userAgent());

        return AnalyticsVisit::create([
            'visitor_token' => $visitorToken,
            'tipo' => $tipo === 'portal' ? 'portal' : 'revista',
            'revista_id' => $revistaId,
            'user_id' => $request->user()?->id,
            'device_type' => $info['device_type'],
            'os' => $info['os'],
            'browser' => $info['browser'],
            'browser_version' => $info['browser_version'],
            'is_bot' => $info['is_bot'],
            'ip_hash' => $this->hashIp($request->ip()),
            'user_agent' => $this->truncate($request->userAgent(), 512),
            'referrer' => $this->truncate($request->headers->get('referer'), 512),
            'path' => $this->truncate($request->input('path') ?: $request->headers->get('referer'), 255),
            'total_pages' => $totalPages,
            'started_at' => now(),
        ]);
    }

    /**
     * Registra un cambio de página dentro de una visita.
     */
    public function trackPage(AnalyticsVisit $visit, int $pageNumber): void
    {
        if (!$this->enabled() || $pageNumber < 1) {
            return;
        }

        // Evita registrar la misma página dos veces seguidas.
        $ultimo = $visit->pageEvents()->orderByDesc('id')->first();
        if ($ultimo && (int) $ultimo->page_number === $pageNumber) {
            return;
        }

        AnalyticsPageEvent::create([
            'visit_id' => $visit->id,
            'revista_id' => $visit->revista_id,
            'page_number' => $pageNumber,
            'viewed_at' => now(),
        ]);

        $visit->pages_viewed = $visit->pageEvents()->distinct('page_number')->count('page_number');
        $visit->save();
    }

    /**
     * Cierra (o actualiza) una visita con las métricas finales.
     * Se usa `max()` para que un cierre parcial nunca reduzca lo ya medido.
     */
    public function closeVisit(AnalyticsVisit $visit, int $durationSeconds, ?int $pagesViewed = null, ?int $totalPages = null): void
    {
        if (!$this->enabled()) {
            return;
        }

        $visit->duration_seconds = max((int) $visit->duration_seconds, max(0, $durationSeconds));

        if ($pagesViewed !== null) {
            $visit->pages_viewed = max((int) $visit->pages_viewed, max(0, $pagesViewed));
        }

        if ($totalPages !== null && $totalPages > 0) {
            $visit->total_pages = $totalPages;
        }

        $visit->ended_at = now();
        $visit->save();
    }

    private function truncate(?string $value, int $length): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_substr($value, 0, $length);
    }
}
