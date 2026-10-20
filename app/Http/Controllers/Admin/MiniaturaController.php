<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Genera y sirve miniaturas ligeras de las portadas y páginas de las revistas.
 *
 * Las imágenes originales viven en `public_html/storage` (docroot) y pueden
 * pesar cientos de KB. Aquí se reducen con GD a un ancho pequeño, se cachean en
 * disco y se sirven con cabeceras de caché agresivas.
 *
 * Solo accesible para administradores.
 */
class MiniaturaController extends Controller
{
    /**
     * Subcarpetas permitidas dentro de public_html/storage.
     */
    private const DIRECTORIOS_PERMITIDOS = ['portadas', 'paginas'];

    private const ANCHO_MIN = 40;

    private const ANCHO_MAX = 400;

    public function __construct()
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
    }

    public function mostrar(Request $request): Response
    {
        if (!function_exists('imagecreatefromjpeg')) {
            abort(500, 'La extensión GD no está disponible.');
        }

        $ruta = (string) $request->query('p', '');
        $ancho = (int) $request->query('w', 120);
        $ancho = max(self::ANCHO_MIN, min(self::ANCHO_MAX, $ancho));

        $absoluta = $this->resolverRuta($ruta);
        abort_if($absoluta === null, 404);

        $cacheDir = storage_path('app/miniaturas');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $extension = strtolower(pathinfo($absoluta, PATHINFO_EXTENSION));
        $cacheKey = sha1($ruta . '|' . $ancho . '|' . (int) @filemtime($absoluta)) . '.jpg';
        $cachePath = $cacheDir . DIRECTORY_SEPARATOR . $cacheKey;

        if (!is_file($cachePath)) {
            $this->generar($absoluta, $extension, $ancho, $cachePath);
        }

        return response()->file($cachePath, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * Valida y resuelve la ruta relativa a un archivo real dentro del storage.
     * Evita salir del directorio permitido (path traversal).
     */
    private function resolverRuta(string $ruta): ?string
    {
        $ruta = str_replace('\\', '/', trim($ruta));

        if ($ruta === '' || str_contains($ruta, '..') || str_starts_with($ruta, '/')) {
            return null;
        }

        $segmentos = explode('/', $ruta);
        if (count($segmentos) < 2 || !in_array($segmentos[0], self::DIRECTORIOS_PERMITIDOS, true)) {
            return null;
        }

        $base = realpath(base_path('public_html/storage'));
        if ($base === false) {
            return null;
        }

        $absoluta = realpath($base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta));
        if ($absoluta === false || !is_file($absoluta)) {
            return null;
        }

        // El archivo debe seguir dentro del directorio base.
        if (!str_starts_with($absoluta, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $absoluta;
    }

    private function generar(string $origen, string $extension, int $ancho, string $destino): void
    {
        $imagen = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($origen),
            'png' => @imagecreatefrompng($origen),
            'webp' => @imagecreatefromwebp($origen),
            'gif' => @imagecreatefromgif($origen),
            default => false,
        };

        if ($imagen === false) {
            abort(404);
        }

        $reducida = imagescale($imagen, $ancho);
        if ($reducida === false) {
            $reducida = $imagen;
        }

        $anchoDestino = imagesx($reducida);
        $altoDestino = imagesy($reducida);

        // Se aplana sobre blanco para evitar transparencias oscuras en PNG/WEBP.
        $lienzo = imagecreatetruecolor($anchoDestino, $altoDestino);
        $blanco = imagecolorallocate($lienzo, 255, 255, 255);
        imagefill($lienzo, 0, 0, $blanco);
        imagecopy($lienzo, $reducida, 0, 0, 0, 0, $anchoDestino, $altoDestino);

        @imagejpeg($lienzo, $destino, 78);

        imagedestroy($lienzo);
        if ($reducida !== $imagen) {
            imagedestroy($reducida);
        }
        imagedestroy($imagen);
    }
}
