<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fondo;
use App\Models\FondoPreset;
use App\Models\Revista;
use App\Support\FondoActual;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FondoController extends Controller
{
    /**
     * Listado de fondos del portal.
     */
    public function index(): View|RedirectResponse
    {
        if ($redir = $this->autorizar()) {
            return $redir;
        }

        $ordenUbicacion = ['inicio' => 0, 'revistas' => 1, 'ambos' => 2];

        $fondos = Fondo::with('revista')
            ->get()
            ->sort(function (Fondo $a, Fondo $b) use ($ordenUbicacion) {
                return ($b->activo <=> $a->activo)
                    ?: (($ordenUbicacion[$a->ubicacion] ?? 9) <=> ($ordenUbicacion[$b->ubicacion] ?? 9))
                    ?: ($a->orden <=> $b->orden)
                    ?: ($a->id <=> $b->id);
            })
            ->values();

        return view('admin.fondos.index', compact('fondos'));
    }

    /**
     * Formulario de creación.
     */
    public function create(): View|RedirectResponse
    {
        if ($redir = $this->autorizar()) {
            return $redir;
        }

        FondoPreset::asegurarDefaults();

        return view('admin.fondos.create', [
            'fondo' => new Fondo(),
            'revistas' => $this->revistasDisponibles(),
            'presetsConm' => FondoPreset::where('tipo', 'conmemorativa')->orderBy('orden')->orderBy('id')->get(),
            'presetsRango' => FondoPreset::where('tipo', 'rango')->orderBy('orden')->orderBy('id')->get(),
            'fondosExistentes' => Fondo::activos()->get(['id', 'titulo', 'ubicacion', 'tipo_vigencia', 'fecha_inicio', 'fecha_fin', 'orden']),
        ]);
    }

    /**
     * Guarda un fondo nuevo.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($redir = $this->autorizar()) {
            return $redir;
        }

        $datos = $this->validar($request);

        if ($request->hasFile('archivo')) {
            $datos['archivo'] = $this->guardarArchivo($request);
        }

        $datos = $this->normalizar($datos, $request);

        Fondo::create($datos);
        FondoActual::olvidar();

        return redirect()->route('admin.fondos.index')
            ->with('success', 'Fondo creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Fondo $fondo): View|RedirectResponse
    {
        if ($redir = $this->autorizar()) {
            return $redir;
        }

        FondoPreset::asegurarDefaults();

        return view('admin.fondos.edit', [
            'fondo' => $fondo,
            'revistas' => $this->revistasDisponibles(),
            'presetsConm' => FondoPreset::where('tipo', 'conmemorativa')->orderBy('orden')->orderBy('id')->get(),
            'presetsRango' => FondoPreset::where('tipo', 'rango')->orderBy('orden')->orderBy('id')->get(),
            'fondosExistentes' => Fondo::activos()->where('id', '!=', $fondo->id)->get(['id', 'titulo', 'ubicacion', 'tipo_vigencia', 'fecha_inicio', 'fecha_fin', 'orden']),
        ]);
    }

    /**
     * Actualiza un fondo.
     */
    public function update(Request $request, Fondo $fondo): RedirectResponse
    {
        if ($redir = $this->autorizar()) {
            return $redir;
        }

        $datos = $this->validar($request, $fondo);

        if ($request->hasFile('archivo')) {
            $nuevo = $this->guardarArchivo($request);
            if ($fondo->archivo && $fondo->archivo !== $nuevo) {
                Storage::disk('public')->delete($fondo->archivo);
            }
            $datos['archivo'] = $nuevo;
        } else {
            unset($datos['archivo']);
        }

        $datos = $this->normalizar($datos, $request, $fondo);

        // Si el fondo pasa a usar una portada, eliminamos la imagen subida previa.
        if ($datos['origen'] === 'portada' && $fondo->archivo) {
            Storage::disk('public')->delete($fondo->archivo);
            $datos['archivo'] = null;
        }

        $fondo->update($datos);
        FondoActual::olvidar();

        return redirect()->route('admin.fondos.index')
            ->with('success', 'Fondo actualizado correctamente.');
    }

    /**
     * Elimina un fondo y su archivo.
     */
    public function destroy(Fondo $fondo): RedirectResponse
    {
        if ($redir = $this->autorizar()) {
            return $redir;
        }

        if ($fondo->archivo) {
            Storage::disk('public')->delete($fondo->archivo);
        }

        $fondo->delete();
        FondoActual::olvidar();

        return back()->with('success', 'Fondo eliminado correctamente.');
    }

    /**
     * Activa o desactiva un fondo.
     */
    public function toggle(Fondo $fondo): RedirectResponse
    {
        if ($redir = $this->autorizar()) {
            return $redir;
        }

        $fondo->update(['activo' => ! $fondo->activo]);
        FondoActual::olvidar();

        return back()->with('success', $fondo->activo
            ? 'Fondo activado.'
            : 'Fondo desactivado.');
    }

    /**
     * Reglas de validación compartidas.
     */
    private function validar(Request $request, ?Fondo $fondo = null): array
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'origen' => ['required', 'in:archivo,portada'],
            'ubicacion' => ['required', 'in:inicio,revistas,ambos'],
            'tipo_vigencia' => ['required', 'in:siempre,rango,conmemorativa'],
            'recurrente' => ['nullable', 'boolean'],
            'fecha_inicio' => ['nullable', 'date', 'required_if:tipo_vigencia,rango,conmemorativa'],
            'fecha_fin' => ['nullable', 'date', 'required_if:tipo_vigencia,rango', 'after_or_equal:fecha_inicio'],
            'revista_id' => ['nullable', 'required_if:origen,portada', 'exists:revistas,id'],
            'archivo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'activo' => ['nullable', 'boolean'],
        ], [
            'fecha_inicio.required_if' => 'Indica la fecha de inicio del rango.',
            'fecha_fin.required_if' => 'Indica la fecha de fin del rango.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la de inicio.',
            'revista_id.required_if' => 'Selecciona la revista cuya portada usarás.',
            'archivo.image' => 'El archivo debe ser una imagen (JPG, PNG o WEBP).',
            'archivo.mimes' => 'Formato no permitido. Usa JPG, PNG o WEBP.',
            'archivo.max' => 'La imagen no debe pesar más de 8 MB.',
        ]);

        // Un fondo de tipo "archivo" necesita una imagen (nueva o existente).
        if ($datos['origen'] === 'archivo') {
            $tienePrevia = $fondo && $fondo->archivo;
            if (! $request->hasFile('archivo') && ! $tienePrevia) {
                throw ValidationException::withMessages([
                    'archivo' => 'Sube una imagen para el fondo.',
                ]);
            }
        }

        return $datos;
    }

    /**
     * Normaliza los datos según el origen y el tipo de vigencia elegidos:
     *  - siempre       -> sin fechas.
     *  - conmemorativa -> un solo día (fin = inicio).
     *  - archivo       -> sin revista asociada.
     *
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function normalizar(array $datos, Request $request, ?Fondo $fondo = null): array
    {
        $datos['recurrente'] = $request->boolean('recurrente');
        $datos['activo'] = $request->boolean('activo');
        $datos['orden'] = (int) $request->input('orden', 0);

        if ($datos['tipo_vigencia'] === 'siempre') {
            $datos['fecha_inicio'] = null;
            $datos['fecha_fin'] = null;
            $datos['recurrente'] = false;
        }

        if ($datos['tipo_vigencia'] === 'conmemorativa') {
            // Un solo día: la conmemoración se guarda como inicio = fin.
            $datos['fecha_fin'] = $datos['fecha_inicio'] ?? null;
        }

        if ($datos['origen'] === 'archivo') {
            $datos['revista_id'] = null;
        }

        return $datos;
    }

    /**
     * Guarda la imagen subida en el disco público (carpeta fondos/).
     */
    private function guardarArchivo(Request $request): string
    {
        $file = $request->file('archivo');
        $nombre = $file->hashName();
        $ruta = 'fondos/'.$nombre;

        // En algunos entornos Windows (Laragon/Apache) realpath() del archivo
        // temporal devuelve false aunque el archivo exista, lo que hace fallar
        // a UploadedFile::store(). Usamos getPathname() como respaldo y, si el
        // stream tampoco se puede abrir, movemos el archivo directamente.
        $origen = $file->getRealPath() ?: $file->getPathname();
        $stream = @fopen($origen, 'r');

        if ($stream === false) {
            $file->move(Storage::disk('public')->path('fondos'), $nombre);
        } else {
            Storage::disk('public')->put($ruta, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $this->optimizarImagen($ruta);

        return $ruta;
    }

    /**
     * Reduce imágenes muy grandes con GD (best-effort: si falla, se conserva el original).
     */
    private function optimizarImagen(string $rutaRelativa): void
    {
        try {
            $abs = Storage::disk('public')->path($rutaRelativa);
            if (! is_file($abs)) {
                return;
            }

            $info = @getimagesize($abs);
            if (! $info) {
                return;
            }

            [$w, $h] = $info;
            $max = 2560;
            if ($w <= $max && $h <= $max) {
                return;
            }

            $mime = $info['mime'] ?? '';
            $src = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($abs),
                'image/png' => @imagecreatefrompng($abs),
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($abs) : null,
                default => null,
            };

            if (! $src) {
                return;
            }

            $ratio = min($max / $w, $max / $h);
            $nw = max(1, (int) round($w * $ratio));
            $nh = max(1, (int) round($h * $ratio));

            $dst = imagecreatetruecolor($nw, $nh);

            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

            match ($mime) {
                'image/jpeg' => imagejpeg($dst, $abs, 85),
                'image/png' => imagepng($dst, $abs, 7),
                'image/webp' => imagewebp($dst, $abs, 85),
                default => null,
            };

            imagedestroy($src);
            imagedestroy($dst);
        } catch (\Throwable $e) {
            // Best-effort: dejamos el archivo original si algo falla.
        }
    }

    /**
     * Revistas activas con portada disponible.
     */
    private function revistasDisponibles()
    {
        return Revista::activas()
            ->whereNotNull('ruta_portada')
            ->recientes()
            ->get(['id', 'titulo', 'ruta_portada']);
    }

    /**
     * Solo administradores.
     */
    private function autorizar(): ?RedirectResponse
    {
        if (Auth::user()?->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para gestionar los fondos del portal.');
        }

        return null;
    }

    /**
     * Guarda o actualiza un preset predeterminado (fechas conmemorativas o rangos).
     */
    public function guardarPreset(Request $request): JsonResponse
    {
        if (Auth::user()?->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'exists:fondo_presets,id'],
            'tipo' => ['required', 'in:conmemorativa,rango'],
            'nombre' => ['required', 'string', 'max:100'],
            'dia' => ['nullable', 'string', 'max:10'],
            'desde' => ['nullable', 'string', 'max:10'],
            'hasta' => ['nullable', 'string', 'max:10'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        if (! empty($validated['id'])) {
            $preset = FondoPreset::findOrFail($validated['id']);
            $preset->update($validated);
        } else {
            $preset = FondoPreset::create($validated);
        }

        return response()->json([
            'success' => true,
            'preset' => $preset,
            'presets' => FondoPreset::where('tipo', $preset->tipo)->orderBy('orden')->orderBy('id')->get(),
        ]);
    }

    /**
     * Elimina un preset predeterminado.
     */
    public function eliminarPreset(FondoPreset $preset): JsonResponse
    {
        if (Auth::user()?->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $tipo = $preset->tipo;
        $preset->delete();

        return response()->json([
            'success' => true,
            'presets' => FondoPreset::where('tipo', $tipo)->orderBy('orden')->orderBy('id')->get(),
        ]);
    }
}
