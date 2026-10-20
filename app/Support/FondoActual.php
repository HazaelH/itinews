<?php

namespace App\Support;

use App\Helpers\SeasonHelper;
use App\Models\Fondo;
use App\Models\Revista;
use Illuminate\Support\Facades\Cache;

/**
 * Resuelve las imágenes que se muestran en el carrusel de fondos del portal.
 *
 * Composición (confirmada con el cliente):
 *   1. Portadas de revistas activas como BASE (igual que el carrusel del login).
 *   2. Encima, los fondos configurados en el panel cuya vigencia coincide con hoy.
 *   3. Si no hay nada, respaldo con la imagen estacional de SeasonHelper.
 */
class FondoActual
{
    /**
     * Imágenes del carrusel para una ubicación (inicio|revistas).
     *
     * @return array<int, string>
     */
    public static function paraUbicacion(string $ubicacion): array
    {
        $hoy = now();
        $key = self::cacheKey($ubicacion, $hoy->toDateString());

        return Cache::remember($key, $hoy->copy()->endOfDay(), function () use ($ubicacion, $hoy) {
            // 1. Fondos configurados vigentes hoy para esta ubicación.
            // Si el administrador configuró fondos activos para esta sección, se muestran ÚNICAMENTE esos fondos.
            $vigentes = Fondo::query()
                ->activos()
                ->with('revista')
                ->get()
                ->filter(fn (Fondo $f) => $f->aplicaUbicacion($ubicacion) && $f->estaVigenteHoy($hoy))
                ->sort(function (Fondo $a, Fondo $b) {
                    return ($b->prioridad() <=> $a->prioridad())
                        ?: ($a->orden <=> $b->orden)
                        ?: ($a->id <=> $b->id);
                })
                ->map(fn (Fondo $f) => $f->url())
                ->filter()
                ->values()
                ->all();

            if (! empty($vigentes)) {
                return array_values(array_unique($vigentes));
            }

            // 2. Base / Respaldo: si no hay fondos vigentes personalizados, usar portadas de revistas activas.
            $portadas = Revista::query()
                ->activas()
                ->whereNotNull('ruta_portada')
                ->recientes()
                ->take(10)
                ->pluck('ruta_portada')
                ->map(fn ($ruta) => Revista::urlDePortada($ruta))
                ->filter()
                ->values()
                ->all();

            if (! empty($portadas)) {
                return array_values(array_unique($portadas));
            }

            // 3. Respaldo estacional si no hay fondos ni portadas con imagen.
            return [asset('img/' . SeasonHelper::getCurrentImage())];
        });
    }

    /**
     * Invalida la caché del carrusel (crear/editar/eliminar/activar fondos).
     */
    public static function olvidar(): void
    {
        $hoy = now()->toDateString();

        foreach (['inicio', 'revistas'] as $ubicacion) {
            Cache::forget(self::cacheKey($ubicacion, $hoy));
        }
    }

    private static function cacheKey(string $ubicacion, string $dia): string
    {
        return 'fondos.carrusel.' . $ubicacion . '.' . $dia;
    }
}
