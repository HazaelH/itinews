<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsVisit;
use App\Models\Revista;
use App\Services\Analytics\AnalyticsTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints públicos de ingesta de métricas.
 *
 * No requieren autenticación (los visitantes del portal son anónimos) y viven
 * en el grupo `api`, por lo que no usan sesión ni CSRF. Están limitados por
 * throttle para evitar abusos.
 */
class MetricasController extends Controller
{
    public function __construct(private readonly AnalyticsTracker $tracker)
    {
    }

    /**
     * Apertura del portal.
     */
    public function portal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visitor_token' => ['required', 'string', 'max:64'],
            'path' => ['nullable', 'string', 'max:255'],
        ]);

        $visit = $this->tracker->startVisit($request, 'portal', $data['visitor_token']);

        return response()->json([
            'enabled' => $this->tracker->enabled(),
            'visit_token' => $visit?->visit_token,
        ], 201);
    }

    /**
     * Apertura de una revista en el visor.
     */
    public function revista(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visitor_token' => ['required', 'string', 'max:64'],
            'revista_id' => ['required', 'integer', 'exists:revistas,id'],
        ]);

        $revista = Revista::find($data['revista_id']);
        $totalPaginas = $revista?->total_pages ?: $revista?->paginas()->count();

        $visit = $this->tracker->startVisit(
            $request,
            'revista',
            $data['visitor_token'],
            (int) $data['revista_id'],
            $totalPaginas ?: null,
        );

        return response()->json([
            'enabled' => $this->tracker->enabled(),
            'visit_token' => $visit?->visit_token,
        ], 201);
    }

    /**
     * Cambio de página.
     */
    public function pagina(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visit_token' => ['required', 'string', 'max:64'],
            'page_number' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $visit = $this->findVisit($data['visit_token']);

        if ($visit) {
            $this->tracker->trackPage($visit, (int) $data['page_number']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Cierre de la visita (se invoca vía navigator.sendBeacon).
     */
    public function cerrar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visit_token' => ['required', 'string', 'max:64'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'pages_viewed' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'total_pages' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $visit = $this->findVisit($data['visit_token']);

        if ($visit) {
            $this->tracker->closeVisit(
                $visit,
                (int) ($data['duration_seconds'] ?? 0),
                isset($data['pages_viewed']) ? (int) $data['pages_viewed'] : null,
                isset($data['total_pages']) ? (int) $data['total_pages'] : null,
            );
        }

        return response()->json(['ok' => true]);
    }

    private function findVisit(string $visitToken): ?AnalyticsVisit
    {
        return AnalyticsVisit::where('visit_token', $visitToken)->first();
    }
}
