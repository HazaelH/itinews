<?php

namespace App\Http\Controllers;

use App\Models\Revista;
use App\Models\Pagina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\ProcesarPdfJob;

class RevistaController extends Controller
{
    public function create()
    {
        return view('revistas.crear');
    }

    public function store(Request $request)
    {
        // 1. Quitamos 'ruta_portada' de la validación
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_publicacion' => 'required|date',
            // 'ruta_portada' => 'required|image...', // ELIMINADO
            'archivo_pdf' => 'required|file|mimes:pdf|max:50000',
        ]);

        try {
            $nombreCarpeta = $this->crearNombreCarpeta($request->titulo);
            
            // 2. Ya no procesamos la portada aquí
            
            $rutaPdf = $this->guardarArchivoSeguro($request->file('archivo_pdf'), "pdfs/{$nombreCarpeta}");

            $revista = Revista::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'fecha_publicacion' => $request->fecha_publicacion,
                'ruta_portada' => null,
                'ruta_pdf' => $rutaPdf,
                'estado_procesamiento' => 'pendiente',
                'progreso' => 0,
                'total_paginas' => 0,
                'activo' => $request->has('activo') ? $request->boolean('activo') : true,
            ]);

            ProcesarPdfJob::dispatch($revista, $nombreCarpeta);
            $this->asegurarEjecucionQueue();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'revista_id' => $revista->id,
                    'redirect_url' => route('revistas.show', $revista),
                    'total_paginas' => $revista->total_paginas,
                    'mensaje' => 'Revista subida con éxito. Procesando páginas...',
                ]);
            }

            return redirect()->route('revista.procesando', $revista->id)
                ->with('success', 'Revista subida correctamente. La portada se generará automáticamente.');

        } catch (\Throwable $e) {
            Log::error('Error procesando revista: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $mensajeUsuario = 'Ocurrió un error inesperado al procesar la revista. Por favor, verifique el archivo e intente nuevamente.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $mensajeUsuario
                ], 500);
            }

            return back()->with('error', $mensajeUsuario)
                ->withInput();
        }
    }

    private function crearNombreCarpeta($titulo)
    {
        $nombre = strtolower(trim($titulo));
        $nombre = preg_replace('/[^a-z0-9\-]/', '-', $nombre);
        $nombre = preg_replace('/-+/', '-', $nombre);
        $nombre = trim($nombre, '-');
        
        $timestamp = time();
        
        return "{$nombre}-{$timestamp}";
    }

    /**
     * Guarda un archivo subido de forma segura compatible con Windows (Laragon/Apache)
     * y Linux en producción, evitando el error "Path must not be empty" cuando
     * SplFileInfo::getRealPath() retorna false sobre archivos temporales en Windows.
     */
    protected function guardarArchivoSeguro($file, string $directorioRelativo): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $nombreArchivo = $file->hashName() ?: (\Illuminate\Support\Str::random(40) . '.' . $extension);
        $directorioLimpio = trim($directorioRelativo, '/');
        $rutaRelativa = $directorioLimpio . '/' . $nombreArchivo;

        $disco = Storage::disk('public');
        $origen = $file->getRealPath() ?: $file->getPathname();

        $stream = @fopen($origen, 'r');
        if ($stream !== false) {
            $disco->put($rutaRelativa, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        } else {
            // Si el stream no se pudo abrir directamente por bloqueo de Windows, mover con Symfony UploadedFile
            $directorioDestino = $disco->path($directorioLimpio);
            if (!is_dir($directorioDestino)) {
                @mkdir($directorioDestino, 0755, true);
            }
            $file->move($directorioDestino, $nombreArchivo);
        }

        return $rutaRelativa;
    }

    public function index()
    {
        $revistas = Revista::activas()->recientes()->paginate(12);
        return view('revistas.revistas', ['revistas' => $revistas]);
    }

    public function show(Revista $revista)
    {
        // Las revistas ocultas solo pueden previsualizarse por admin/editor.
        if (! $revista->estaActiva() && ! $this->puedePrevisualizar()) {
            abort(404);
        }

        $paginas = $revista->paginas()->orderBy('numero_pagina', 'asc')->get();
        return view('revistas.show', [
            'revista' => $revista,
            'paginas' => $paginas,
            'oculta' => ! $revista->estaActiva(),
        ]);
    }

    /**
     * ¿El usuario autenticado puede ver revistas ocultas?
     */
    private function puedePrevisualizar(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->role, ['admin', 'editor'], true);
    }

    public function edit(Revista $revista)
    {
        return view('revistas.editar', compact('revista'));
    }

    public function update(Request $request, Revista $revista)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_publicacion' => 'required|date',
            'ruta_portada' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $datosActualizacion = [
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'fecha_publicacion' => $request->fecha_publicacion,
            ];

            // Solo actualizamos la visibilidad si el formulario la envía.
            if ($request->has('activo')) {
                $datosActualizacion['activo'] = $request->boolean('activo');
            }

            if ($request->hasFile('ruta_portada')) {
                if ($revista->ruta_portada && Storage::exists('public/' . $revista->ruta_portada)) {
                    Storage::delete('public/' . $revista->ruta_portada);
                }

                $rutaPortada = $this->guardarArchivoSeguro($request->file('ruta_portada'), 'portadas');
                $datosActualizacion['ruta_portada'] = $rutaPortada;
            }

            $revista->update($datosActualizacion);

            return redirect()->route('revistas.show', $revista)
                ->with('success', 'Revista actualizada exitosamente.');

        } catch (\Throwable $e) {
            Log::error('Error actualizando revista: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Ocurrió un error al actualizar la revista. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    public function destroy(Revista $revista)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para eliminar revistas.');
        }

        try {
            if ($revista->ruta_portada && Storage::exists('public/' . $revista->ruta_portada)) {
                Storage::delete('public/' . $revista->ruta_portada);
            }

            if ($revista->ruta_pdf && Storage::exists('public/' . $revista->ruta_pdf)) {
                Storage::delete('public/' . $revista->ruta_pdf);
            }

            $paginas = $revista->paginas;
            foreach ($paginas as $pagina) {
                if ($pagina->ruta_imagen && Storage::exists('public/' . $pagina->ruta_imagen)) {
                    Storage::delete('public/' . $pagina->ruta_imagen);
                }
            }

            $revista->paginas()->delete();
            $revista->delete();

            return redirect()->route('dashboard')
                ->with('success', 'Revista eliminada exitosamente.');

        } catch (\Throwable $e) {
            Log::error('Error eliminando revista: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Ocurrió un error al eliminar la revista. Por favor, intente nuevamente.');
        }
    }

    public function toggleActivo(Revista $revista)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para cambiar la visibilidad de revistas.');
        }

        $revista->update(['activo' => ! $revista->estaActiva()]);

        $mensaje = $revista->estaActiva()
            ? 'La revista ahora es visible en el portal.'
            : 'La revista se ocultó del portal (puedes volver a activarla cuando quieras).';

        return back()->with('success', $mensaje);
    }

    public function reprocesar(Revista $revista)
    {
        if (Auth::user()->role !== 'admin') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permisos para reprocesar revistas.'
                ], 403);
            }
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para reprocesar revistas.');
        }

        try {
            // 1. Limpiar imágenes anteriores
            $paginas = $revista->paginas;
            foreach ($paginas as $pagina) {
                if ($pagina->ruta_imagen && Storage::exists('public/' . $pagina->ruta_imagen)) {
                    Storage::delete('public/' . $pagina->ruta_imagen);
                }
            }
            $revista->paginas()->delete();

            // 2. Reiniciar estado en BD
            $revista->update([
                'estado_procesamiento' => 'procesando',
                'progreso' => 0,
                'total_paginas' => 0
            ]);

            // 3. Lanzar el trabajo en segundo plano
            ProcesarPdfJob::dispatch($revista);
            $this->asegurarEjecucionQueue();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'revista_id' => $revista->id,
                    'redirect_url' => route('revistas.show', $revista),
                    'mensaje' => 'Reprocesamiento iniciado con éxito.'
                ]);
            }

            // 4. Redirigir a la vista de progreso
            return redirect()->route('revista.procesando', $revista) 
                ->with('info', 'El PDF se está reprocesando...');

        } catch (\Throwable $e) {
            Log::error('Error reprocesando revista: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ocurrió un error al reprocesar el PDF. Por favor, intente nuevamente.'
                ], 500);
            }

            return back()->with('error', 'Ocurrió un error al reprocesar el PDF. Por favor, intente nuevamente.');
        }
    }

    /**
     * Asegura la ejecución del queue worker si se usa el driver de base de datos.
     */
    private function asegurarEjecucionQueue(): void
    {
        if (config('queue.default') !== 'database') {
            return;
        }

        try {
            $artisan = base_path('artisan');

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                if (function_exists('popen') && function_exists('pclose')) {
                    @pclose(@popen("start /B php \"{$artisan}\" queue:work --stop-when-empty > NUL 2>&1", "r"));
                    return;
                }
            } else {
                if (function_exists('exec')) {
                    @exec("php \"{$artisan}\" queue:work --stop-when-empty > /dev/null 2>&1 &");
                    return;
                }
                if (function_exists('shell_exec')) {
                    @shell_exec("php \"{$artisan}\" queue:work --stop-when-empty > /dev/null 2>&1 &");
                    return;
                }
            }

            // Fallback en entornos donde exec/popen están deshabilitados (ej. hosting compartido):
            // Procesar el trabajo pendiente de la cola de forma síncrona mediante Artisan.
            @set_time_limit(300);
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-time' => 120,
            ]);
        } catch (\Throwable $e) {
            Log::warning("No se pudo iniciar queue:work automáticamente: " . $e->getMessage());
        }
    }

    public function procesando(Revista $revista)
    {
        return view('revistas.progreso', compact('revista'));
    }

    public function estado(Revista $revista)
    {
        return response()->json([
            'estado' => $revista->estado_procesamiento,
            'progreso' => $revista->progreso,
            'total_paginas' => $revista->total_paginas,
            'paginas_procesadas' => $revista->paginas()->count(),
            'completado' => $revista->estaProcesada(),
            'error' => $revista->tieneError()
        ]);
    }

    public function progreso(Revista $revista)
    {
        return response()->json([
            'estado' => $revista->estado_procesamiento,
            'progreso' => $revista->progreso,
            'total_paginas' => $revista->total_paginas,
            'paginas_procesadas' => $revista->paginas()->count(),
            'completado' => $revista->estaProcesada(),
            'error' => $revista->tieneError(),
            'progreso_porcentaje' => $revista->progreso_porcentaje
        ]);
    }
}