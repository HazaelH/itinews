<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsPageEvent;
use App\Models\AnalyticsVisit;
use App\Models\Favorito;
use App\Models\Revista;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Panel de métricas y estadísticas del portal (solo administradores).
 */
class MetricasController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
    }

    public function index(): View
    {
        return view('admin.metricas.index');
    }

    /**
     * Devuelve, en JSON, todos los conjuntos de datos que consumen las gráficas.
     */
    public function data(Request $request): JsonResponse
    {
        $dias = (int) $request->integer('dias', 30);
        $dias = max(1, min(365, $dias));

        $desde = now()->subDays($dias - 1)->startOfDay();
        $hasta = now()->endOfDay();
        $rango = [$desde, $hasta];

        // Constructores "frescos" por cada consulta (evita reusar query builders).
        $revista = fn () => AnalyticsVisit::query()->notBot()->revistas()->whereBetween('created_at', $rango);
        $portal = fn () => AnalyticsVisit::query()->notBot()->portal()->whereBetween('created_at', $rango);
        $todas = fn () => AnalyticsVisit::query()->notBot()->whereBetween('created_at', $rango);

        // ------------------------------------------------------------------
        // KPIs
        // ------------------------------------------------------------------
        $visitasRevista = $revista()->count();
        $visitasPortal = $portal()->count();
        $visitantesUnicos = $todas()->distinct()->count('visitor_token');

        $recurrentes = $todas()
            ->select('visitor_token')
            ->groupBy('visitor_token')
            ->havingRaw('COUNT(*) >= 2')
            ->get()
            ->count();

        $tiempoPromedio = (int) round((float) ($revista()->where('duration_seconds', '>', 0)->avg('duration_seconds') ?? 0));
        $paginasPromedio = round((float) ($revista()->avg('pages_viewed') ?? 0), 1);

        $tasaFinalizacion = round((float) ($revista()
            ->where('total_pages', '>', 0)
            ->selectRaw('AVG(LEAST(pages_viewed / GREATEST(total_pages, 1), 1)) * 100 as t')
            ->value('t') ?? 0), 1);

        $kpis = [
            'visitas_revista' => $visitasRevista,
            'visitas_portal' => $visitasPortal,
            'visitantes_unicos' => $visitantesUnicos,
            'visitantes_recurrentes' => $recurrentes,
            'tiempo_promedio' => $tiempoPromedio,
            'paginas_promedio' => $paginasPromedio,
            'tasa_finalizacion' => $tasaFinalizacion,
        ];

        // ------------------------------------------------------------------
        // Revistas más abiertas
        // ------------------------------------------------------------------
        $revistasMasVistas = $revista()
            ->with('revista:id,titulo,ruta_portada')
            ->select('revista_id', DB::raw('COUNT(*) as total'))
            ->groupBy('revista_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->revista_id,
                'titulo' => $row->revista?->titulo ?? ('Revista #' . $row->revista_id),
                'portada' => $row->revista?->ruta_portada,
                'total' => (int) $row->total,
            ])
            ->values();

        // ------------------------------------------------------------------
        // Dispositivos / SO / navegadores
        // ------------------------------------------------------------------
        $dispositivos = $todas()
            ->select('device_type', DB::raw('COUNT(*) as total'))
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => $row->device_type ?: 'unknown', 'total' => (int) $row->total]);

        $sistemas = $todas()
            ->select('os', DB::raw('COUNT(*) as total'))
            ->groupBy('os')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => ['label' => $row->os ?: 'Desconocido', 'total' => (int) $row->total]);

        $navegadores = $todas()
            ->select('browser', DB::raw('COUNT(*) as total'))
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => ['label' => $row->browser ?: 'Desconocido', 'total' => (int) $row->total]);

        // ------------------------------------------------------------------
        // Series de tiempo
        // ------------------------------------------------------------------
        $tiempoPorDia = $this->serieDiaria(
            $revista()->selectRaw('DATE(created_at) as dia, AVG(duration_seconds) as valor')->groupBy('dia')->get(),
            $desde,
            $hasta
        );

        $visitasPorDia = $this->serieDiaria(
            $portal()->selectRaw('DATE(created_at) as dia, COUNT(*) as valor')->groupBy('dia')->get(),
            $desde,
            $hasta
        );

        // ------------------------------------------------------------------
        // Histograma de páginas leídas por sesión
        // ------------------------------------------------------------------
        $paginas = $revista()->pluck('pages_viewed')->map(fn ($v) => (int) $v)->all();
        $buckets = [
            ['label' => '0', 'min' => 0, 'max' => 0],
            ['label' => '1-2', 'min' => 1, 'max' => 2],
            ['label' => '3-5', 'min' => 3, 'max' => 5],
            ['label' => '6-10', 'min' => 6, 'max' => 10],
            ['label' => '11-20', 'min' => 11, 'max' => 20],
            ['label' => '21-40', 'min' => 21, 'max' => 40],
            ['label' => '40+', 'min' => 41, 'max' => PHP_INT_MAX],
        ];
        $histogramaPaginas = array_map(function ($bucket) use ($paginas) {
            $total = 0;
            foreach ($paginas as $valor) {
                if ($valor >= $bucket['min'] && $valor <= $bucket['max']) {
                    $total++;
                }
            }
            return ['label' => $bucket['label'], 'total' => $total];
        }, $buckets);

        // ------------------------------------------------------------------
        // Horas pico (todos los eventos, últimos X días)
        // ------------------------------------------------------------------
        $horas = $todas()
            ->selectRaw('HOUR(created_at) as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->get()
            ->keyBy('hora');

        $horasPico = [];
        for ($h = 0; $h < 24; $h++) {
            $horasPico[] = [
                'label' => str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00',
                'total' => (int) ($horas[$h]->total ?? 0),
            ];
        }

        // ------------------------------------------------------------------
        // Revistas en favoritos
        // ------------------------------------------------------------------
        $favoritos = Favorito::query()
            ->with('revista:id,titulo,ruta_portada')
            ->select('revista_id', DB::raw('COUNT(*) as total'))
            ->groupBy('revista_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->revista_id,
                'titulo' => $row->revista?->titulo ?? ('Revista #' . $row->revista_id),
                'portada' => $row->revista?->ruta_portada,
                'total' => (int) $row->total,
            ])
            ->values();

        // ------------------------------------------------------------------
        // Páginas más leídas: agrupadas por revista (portada + páginas top)
        // ------------------------------------------------------------------
        $conteoPaginas = AnalyticsPageEvent::query()
            ->whereBetween('created_at', $rango)
            ->whereNotNull('revista_id')
            ->select('revista_id', 'page_number', DB::raw('COUNT(*) as total'))
            ->groupBy('revista_id', 'page_number')
            ->get();

        $revistasCatalogo = Revista::query()
            ->whereIn('id', $conteoPaginas->pluck('revista_id')->unique()->all())
            ->get(['id', 'titulo', 'ruta_portada', 'total_paginas'])
            ->keyBy('id');

        $paginasMasVistas = $conteoPaginas
            ->groupBy('revista_id')
            ->map(function ($items, $revistaId) use ($revistasCatalogo) {
                $revista = $revistasCatalogo->get($revistaId);

                return [
                    'revista_id' => (int) $revistaId,
                    'revista_titulo' => $revista?->titulo ?? ('Revista #' . $revistaId),
                    'portada' => $revista?->ruta_portada,
                    'total_paginas' => (int) ($revista?->total_paginas ?? 0),
                    'total' => (int) $items->sum('total'),
                    'paginas' => $items
                        ->sortByDesc('total')
                        ->take(8)
                        ->map(fn ($row) => ['pagina' => (int) $row->page_number, 'total' => (int) $row->total])
                        ->values()
                        ->all(),
                ];
            })
            ->sortByDesc('total')
            ->take(6)
            ->values();

        return response()->json([
            'rango' => [
                'dias' => $dias,
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
            'kpis' => $kpis,
            'revistas_mas_vistas' => $revistasMasVistas,
            'dispositivos' => $dispositivos,
            'sistemas' => $sistemas,
            'navegadores' => $navegadores,
            'tiempo_por_dia' => $tiempoPorDia,
            'visitas_por_dia' => $visitasPorDia,
            'histograma_paginas' => $histogramaPaginas,
            'horas_pico' => $horasPico,
            'favoritos' => $favoritos,
            'paginas_mas_vistas' => $paginasMasVistas,
        ]);
    }

    /**
     * Construye una serie diaria continua (rellena con 0 los días sin datos).
     *
     * @param  \Illuminate\Support\Collection  $rows  filas con propiedades `dia` y `valor`
     * @return array{labels: array<int,string>, valores: array<int,float>}
     */
    private function serieDiaria($rows, Carbon $desde, Carbon $hasta): array
    {
        $mapa = [];
        foreach ($rows as $row) {
            $mapa[$row->dia] = (float) $row->valor;
        }

        $labels = [];
        $valores = [];
        $cursor = $desde->copy();

        while ($cursor->lte($hasta)) {
            $clave = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('d/m');
            $valores[] = round($mapa[$clave] ?? 0, 1);
            $cursor->addDay();
        }

        return ['labels' => $labels, 'valores' => $valores];
    }
}
