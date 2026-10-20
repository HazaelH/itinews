<?php

namespace App\Jobs;

use App\Models\Revista;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcesarPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $revista;
    public $nombreCarpeta;
    public $timeout = 600;

    public function __construct(Revista $revista, $nombreCarpeta = null)
    {
        $this->revista = $revista;
        $this->nombreCarpeta = $nombreCarpeta;
    }

    public function handle()
    {
        try {
            Log::info("Iniciando procesamiento PDF - Revista: {$this->revista->id}");
            
            $this->revista->update([
                'estado_procesamiento' => 'procesando',
                'progreso' => 0,
            ]);

            $pdfPath = storage_path('app/public/' . $this->revista->ruta_pdf);
            if (!file_exists($pdfPath)) {
                throw new \Exception("Archivo PDF no encontrado en: " . $pdfPath);
            }

            // 1. Obtención instantánea del conteo de páginas (< 0.5s)
            $totalPaginas = $this->contarPaginas($pdfPath);
            $this->revista->update([
                'total_paginas' => $totalPaginas,
                'progreso' => 0,
            ]);

            Log::info("PDF detectado con {$totalPaginas} páginas para Revista: {$this->revista->id}");

            // 2. Preparar directorio de destino
            $outputPath = storage_path('app/public/paginas/');
            if ($this->nombreCarpeta) {
                $outputPath .= $this->nombreCarpeta . '/';
            }
            if (!file_exists($outputPath)) {
                mkdir($outputPath, 0755, true);
            }

            // 3. Motor dual: Poppler pdftoppm (ultra rápido) con fallback a Imagick
            $popplerBin = $this->detectarPoppler();

            if ($popplerBin) {
                $this->procesarConPoppler($popplerBin, $pdfPath, $totalPaginas, $outputPath);
            } else {
                $this->procesarConImagick($pdfPath, $totalPaginas, $outputPath);
            }

            // 4. Marcar como completado al 100%
            $this->revista->update([
                'estado_procesamiento' => 'completado',
                'progreso' => 100,
            ]);

            Log::info("Procesamiento completado con éxito - Revista: {$this->revista->id}");

        } catch (\Exception $e) {
            $this->revista->update([
                'estado_procesamiento' => 'error',
                'progreso' => 0,
            ]);
            Log::error("Error en ProcesarPdfJob (Revista {$this->revista->id}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cuenta las páginas del PDF de forma instantánea sin cargar imágenes a memoria.
     */
    private function contarPaginas(string $pdfPath): int
    {
        // A. Intentar con pdfinfo
        $pdfinfoBin = $this->detectarPdfInfo();
        if ($pdfinfoBin) {
            $cmd = escapeshellcmd($pdfinfoBin) . ' ' . escapeshellarg($pdfPath) . ' 2>&1';
            @exec($cmd, $lines, $code);
            if ($code === 0 && !empty($lines)) {
                foreach ($lines as $line) {
                    if (preg_match('/Pages:\s+(\d+)/i', $line, $matches)) {
                        $p = (int)$matches[1];
                        if ($p > 0) return $p;
                    }
                }
            }
        }

        // B. Intentar con pingImage de Imagick (solo lee metadatos de cabecera)
        if (extension_loaded('imagick')) {
            try {
                $im = new \Imagick();
                $im->pingImage($pdfPath);
                $p = $im->getNumberImages();
                $im->clear();
                $im->destroy();
                if ($p > 0) return $p;
            } catch (\Exception $e) {
                Log::warning("Imagick pingImage no disponible o falló: " . $e->getMessage());
            }
        }

        // C. Fallback por análisis binario de trailers y catálogo PDF
        $fp = @fopen($pdfPath, 'rb');
        if ($fp) {
            $chunk = fread($fp, 204800);
            fseek($fp, -204800, SEEK_END);
            $chunk .= fread($fp, 204800);
            fclose($fp);
            if (preg_match('/\/Count\s+(\d+)/', $chunk, $m)) {
                $p = (int)$m[1];
                if ($p > 0) return $p;
            }
        }

        return 1;
    }

    /**
     * Motor Poppler: Extrae páginas a alta resolución (180 DPI) y las convierte a WebP HD.
     */
    private function procesarConPoppler(string $popplerBin, string $pdfPath, int $totalPaginas, string $outputPath): void
    {
        Log::info("Ejecutando motor Poppler pdftoppm para {$totalPaginas} páginas.");

        for ($i = 1; $i <= $totalPaginas; $i++) {
            $prefijo = $outputPath . 'tmp_p_' . $i;

            // Renderizar la página individual en JPEG de alta calidad a 180 DPI (~2000-2400px ancho)
            $cmd = escapeshellcmd($popplerBin) . " -jpeg -r 180 -jpegopt quality=92 -f {$i} -l {$i} " .
                   escapeshellarg($pdfPath) . ' ' . escapeshellarg($prefijo);
            @exec($cmd, $out, $ret);

            $generados = glob($prefijo . '*.jpg');
            if (empty($generados)) {
                throw new \Exception("pdftoppm no pudo generar la página {$i}");
            }

            $archivoJpg = $generados[0];
            $nombreWebp = "pagina_{$i}.webp";
            $rutaCompletaWebp = $outputPath . $nombreWebp;

            // Optimización y compresión a WebP
            $this->convertirAWebp($archivoJpg, $rutaCompletaWebp);
            @unlink($archivoJpg);

            $this->guardarRegistroPagina($i, $nombreWebp, $rutaCompletaWebp);

            $progreso = round(($i / $totalPaginas) * 100);
            $this->revista->update(['progreso' => $progreso]);
            Log::info("Página {$i}/{$totalPaginas} completada ({$progreso}%)");
        }
    }

    /**
     * Motor Imagick (Fallback): Procesa página por página liberando memoria.
     */
    private function procesarConImagick(string $pdfPath, int $totalPaginas, string $outputPath): void
    {
        Log::info("Ejecutando motor Imagick nativo para {$totalPaginas} páginas.");

        for ($i = 0; $i < $totalPaginas; $i++) {
            $numeroPagina = $i + 1;
            $nombreArchivo = "pagina_{$numeroPagina}.webp";
            $rutaCompleta = $outputPath . $nombreArchivo;

            $imagick = new \Imagick();
            try {
                $imagick->setResolution(180, 180);
                $imagick->readImage($pdfPath . '[' . $i . ']');
                $imagick->setImageFormat('webp');
                $imagick->setImageCompressionQuality(88);
                $imagick->setImageBackgroundColor('white');
                $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

                if ($imagick->getImageWidth() > 2400) {
                    $imagick->resizeImage(2400, 0, \Imagick::FILTER_LANCZOS, 1);
                }

                $imagick->writeImage($rutaCompleta);
            } finally {
                $imagick->clear();
                $imagick->destroy();
                unset($imagick);
            }

            $this->guardarRegistroPagina($numeroPagina, $nombreArchivo, $rutaCompleta);

            $progreso = round((($i + 1) / $totalPaginas) * 100);
            $this->revista->update(['progreso' => $progreso]);

            gc_collect_cycles();
        }
    }

    /**
     * Convierte y optimiza un JPEG a WebP con Lanczos filter si supera los 2400px.
     */
    private function convertirAWebp(string $origen, string $destino): void
    {
        if (extension_loaded('imagick')) {
            $im = new \Imagick($origen);
            if ($im->getImageWidth() > 2400) {
                $im->resizeImage(2400, 0, \Imagick::FILTER_LANCZOS, 1);
            }
            $im->setImageFormat('webp');
            $im->setImageCompressionQuality(88);
            $im->writeImage($destino);
            $im->clear();
            $im->destroy();
            return;
        }

        // Fallback si no hay Imagick pero hay GD
        if (function_exists('imagecreatefromjpeg') && function_exists('imagewebp')) {
            $img = @imagecreatefromjpeg($origen);
            if ($img) {
                imagewebp($img, $destino, 88);
                imagedestroy($img);
                return;
            }
        }

        // Fallback básico: copiar archivo
        copy($origen, $destino);
    }

    /**
     * Guarda el registro de la página en BD y genera portada automática si es la página 1.
     */
    private function guardarRegistroPagina(int $numeroPagina, string $nombreArchivo, string $rutaCompleta): void
    {
        $rutaImagen = 'paginas/';
        if ($this->nombreCarpeta) {
            $rutaImagen .= $this->nombreCarpeta . '/';
        }
        $rutaImagen .= $nombreArchivo;

        // Portada automática desde página 1
        if ($numeroPagina === 1) {
            $nombrePortada = 'portada_' . time() . '.webp';
            $carpetaPortadas = storage_path('app/public/portadas/' . ($this->nombreCarpeta ?? ''));
            if (!file_exists($carpetaPortadas)) {
                mkdir($carpetaPortadas, 0755, true);
            }

            copy($rutaCompleta, $carpetaPortadas . '/' . $nombrePortada);

            $rutaPortadaRelativa = 'portadas/' . ($this->nombreCarpeta ? $this->nombreCarpeta . '/' : '') . $nombrePortada;
            $this->revista->update(['ruta_portada' => $rutaPortadaRelativa]);
            Log::info("Portada generada automáticamente desde página 1: {$rutaPortadaRelativa}");
        }

        $this->revista->paginas()->updateOrCreate(
            ['numero_pagina' => $numeroPagina],
            ['ruta_imagen' => $rutaImagen]
        );
    }

    /**
     * Detecta la ruta ejecutable de pdftoppm.
     */
    private function detectarPoppler(): ?string
    {
        $candidatos = array_filter([
            env('POPPLER_PATH'),
            'pdftoppm',
            '/usr/bin/pdftoppm',
            '/usr/local/bin/pdftoppm',
            '/bin/pdftoppm',
            '/opt/homebrew/bin/pdftoppm',
            'C:\\Users\\hazae\\scoop\\shims\\pdftoppm.exe',
            'C:\\Program Files\\poppler\\bin\\pdftoppm.exe'
        ]);

        foreach ($candidatos as $cmd) {
            @exec(escapeshellcmd($cmd) . ' -v 2>&1', $salida, $codigo);
            if ($codigo === 0) {
                return $cmd;
            }
            $salida = [];
        }

        return null;
    }

    /**
     * Detecta la ruta ejecutable de pdfinfo.
     */
    private function detectarPdfInfo(): ?string
    {
        $candidatos = array_filter([
            env('PDFINFO_PATH'),
            'pdfinfo',
            '/usr/bin/pdfinfo',
            '/usr/local/bin/pdfinfo',
            '/bin/pdfinfo',
            '/opt/homebrew/bin/pdfinfo',
            'C:\\Users\\hazae\\scoop\\shims\\pdfinfo.exe',
            'C:\\Program Files\\poppler\\bin\\pdfinfo.exe'
        ]);

        foreach ($candidatos as $cmd) {
            @exec(escapeshellcmd($cmd) . ' -v 2>&1', $salida, $codigo);
            if ($codigo === 0) {
                return $cmd;
            }
            $salida = [];
        }

        return null;
    }

    public function failed($exception)
    {
        Log::error("Job falló para revista: {$this->revista->id} - {$exception->getMessage()}");
        $this->revista->update([
            'estado_procesamiento' => 'error',
            'progreso' => 0,
        ]);
    }
}