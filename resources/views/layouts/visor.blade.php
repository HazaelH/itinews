<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - {{ $revista->titulo ?? 'Revista UPVT' }}</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PageFlip.js -->
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>
    
    <!-- CSS del Visor -->
    <link href="{{ asset('css/flipbook.css') }}?v={{ file_exists(public_path('css/flipbook.css')) ? filemtime(public_path('css/flipbook.css')) : time() }}" rel="stylesheet">
</head>
<body>
    @yield('visor-content')
    
    <!-- Métricas anónimas (debe cargarse antes del visor) -->
    <script src="{{ asset('js/metricas.js') }}?v={{ file_exists(public_path('js/metricas.js')) ? filemtime(public_path('js/metricas.js')) : time() }}"></script>

    <!-- JavaScript del Visor -->
    <script src="{{ asset('js/flipbook-viewer.js') }}?v={{ file_exists(public_path('js/flipbook-viewer.js')) ? filemtime(public_path('js/flipbook-viewer.js')) : time() }}"></script>
</body>
</html>