<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>Portal ITINEWS - @yield('title', 'Revistas Académicas')</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css'])

    <link href="{{ asset('css/base.css') }}" rel="stylesheet">
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">
    <!--<link href="{{ asset('css/revistas.css') }}" rel="stylesheet">-->
    <link href="{{ asset('css/flipbook.css') }}" rel="stylesheet">
    <link href="{{ asset('css/carrusel-fondos.css') }}" rel="stylesheet">

    @stack('styles')

    <!-- ============================================ -->
    <!-- OVERRIDE TEMPORAL DE COLORES ITINEWS         -->
    <!-- ============================================ -->
    <style>
        :root {
            --primary: #1f5b4f !important;
            --primary-dark: #164236 !important;
            --primary-light: #2a7a6a !important;
            --secondary: #7a1830 !important;
            --secondary-dark: #5e1023 !important;
            --background-color: #f8f2ee !important;
            --surface-color: #ffffff !important;
            --text-color: #1e1f23 !important;
            --text-secondary: #666666 !important;
            --border-color: #e0d5c8 !important;
            --light-gray: #f8f2ee !important;
        }

        .dark-mode {
            --primary: #3a9a8a !important;
            --primary-dark: #2a7a6a !important;
            --secondary: #c42040 !important;
            --secondary-dark: #9a1f3d !important;
            --background-color: #1a1a1a !important;
            --surface-color: #2d2d2d !important;
            --text-color: #f8f2ee !important;
            --text-secondary: #c0c0c0 !important;
            --border-color: #505050 !important;
            --light-gray: #353535 !important;
        }

        /* Sidebar header */
        .sidebar-header {
            background: linear-gradient(135deg, #1f5b4f, #9ebbb3) !important;
        }

        /* Badges */
        .badge, #preview-cerrar {
            background: #7a1830 !important;
        }

        .estacion-badge, .btn-volver, .contador, #start-reading-btn {
            background: #1f5b4f !important;
        }

        #start-reading-btn {
            background: linear-gradient(135deg, #1f5b4f, #164236) !important;
            box-shadow: 0 4px 15px rgba(31, 91, 79, .3) !important;
        }

        /* Stats */
        .stat-icon, .stat-number, .estadistica-numero {
            color: #1f5b4f !important;
        }

        /* Favoritos */
        .favorito-btn {
            border-color: #7a1830 !important;
        }

        .favorito-btn .fa-heart {
            color: #7a1830 !important;
        }

        .favorito-btn:hover, .favorito-btn.favorito {
            background: #7a1830 !important;
        }

        .favorito-btn:hover .fa-heart, .favorito-btn.favorito .fa-heart {
            color: #fff !important;
        }

        /* Nav links */
        .nav-link.active, .filter-btn.active, .quick-btn.active {
            background: rgba(31, 91, 79, .15) !important;
            color: #1f5b4f !important;
            border-left-color: #1f5b4f !important;
        }

        .nav-link i, .filter-btn i, .quick-btn i {
            color: #1f5b4f !important;
        }

        /* Hover */
        .revista:hover p {
            background: linear-gradient(135deg, #1f5b4f, #164236) !important;
        }

        .tarjeta-compacta:hover {
            border-left: 4px solid #1f5b4f !important;
        }

        /* Iconos de tarjetas */
        .tarjeta-favoritos .tarjeta-icono {
            background: linear-gradient(135deg, #7a1830, #5e1023) !important;
        }

        .tarjeta-recientes .tarjeta-icono,
        .tarjeta-compacta-icono {
            background: linear-gradient(135deg, #1f5b4f, #164236) !important;
        }

        .tarjeta-destacadas .tarjeta-icono {
            background: linear-gradient(135deg, #c42040, #7a1830) !important;
        }

        .tarjeta-estadisticas .tarjeta-icono {
            background: linear-gradient(135deg, #2a7a6a, #1f5b4f) !important;
        }

        .tarjeta-acerca .tarjeta-icono {
            background: linear-gradient(135deg, #7a1830, #1f5b4f) !important;
        }

        /* Gradientes */
        .page-header::before,
        .page-title::after,
        .tarjeta-seccion::before,
        .stat-card::before {
            background: linear-gradient(135deg, #1f5b4f, #7a1830) !important;
        }

        /* Scrollbar */
        .sidebar-nav::-webkit-scrollbar-thumb,
        ::-webkit-scrollbar-thumb {
            background: #1f5b4f !important;
        }

        /* Tarjetas de sección */
        .tarjeta-favoritos {
            background: linear-gradient(135deg, var(--surface-color) 0%, rgba(122, 24, 48, .08) 100%) !important;
            border-left: 4px solid #7a1830 !important;
        }

        .tarjeta-recientes {
            background: linear-gradient(135deg, var(--surface-color) 0%, rgba(31, 91, 79, .08) 100%) !important;
            border-left: 4px solid #1f5b4f !important;
        }

        .tarjeta-acerca {
            background: linear-gradient(135deg, var(--surface-color) 0%, rgba(122, 24, 48, .06) 100%) !important;
            border-left: 4px solid #7a1830 !important;
        }

        /* Dark mode overrides */
        .dark-mode .sidebar-header {
            background: linear-gradient(135deg, #3a9a8a, #2a7a6a) !important;
        }

        .dark-mode .estacion-badge,
        .dark-mode .btn-volver,
        .dark-mode .contador,
        .dark-mode #start-reading-btn {
            background: #3a9a8a !important;
        }

        .dark-mode #start-reading-btn {
            background: linear-gradient(135deg, #3a9a8a, #2a7a6a) !important;
        }

        .dark-mode .stat-icon,
        .dark-mode .stat-number {
            color: #3a9a8a !important;
        }

        .dark-mode .favorito-btn {
            border-color: #c42040 !important;
        }

        .dark-mode .favorito-btn .fa-heart {
            color: #c42040 !important;
        }

        .dark-mode .favorito-btn:hover,
        .dark-mode .favorito-btn.favorito {
            background: #c42040 !important;
        }

        .dark-mode .badge,
        .dark-mode #preview-cerrar {
            background: #c42040 !important;
        }

        .dark-mode .nav-link.active,
        .dark-mode .filter-btn.active,
        .dark-mode .quick-btn.active {
            color: #3a9a8a !important;
            background: rgba(58, 154, 138, .2) !important;
            border-left-color: #3a9a8a !important;
        }

        .dark-mode .revista:hover p {
            background: linear-gradient(135deg, #3a9a8a, #2a7a6a) !important;
        }
    </style>

    {{-- FONDO DEL PORTAL (carrusel de fondos: portadas + fondos vigentes) --}}
    <style>
        /* El fondo ya no se pinta en el body: usa las capas .fondos-bg
           que administra js/carrusel-fondos.js (ver app/Support/FondoActual). */
        body {
            background-color: #f4f1ec !important;
            background-image: none !important;
        }

        /* Sidebar semi-transparente */
        .sidebar {
            background-color: rgba(255, 255, 255, 0.85) !important; 
            backdrop-filter: blur(10px) !important; 
            border-right: 1px solid rgba(125, 75, 75, 0.5); 
        }

        /* Sidebar header */
        .sidebar-header {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 25px 15px !important;
            text-align: center !important;
        }

        .logo-link {
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: inherit;
            transition: transform 0.3s ease;
        }

        .logo-link:hover {
            transform: scale(1.05);
        }

        .sidebar-logo {
            height: 70px !important;
            width: auto !important;
            margin-bottom: 12px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .sidebar-header h2 {
            font-size: 1.3rem !important;
            margin: 0 !important;
            font-weight: 800 !important;
            line-height: 1.2;
            color: #fff !important;
        }

        /* Main content transparente para ver el fondo */
        .main-content {
            background-color: transparent !important; 
            background-image: none !important; 
        }

        /* Tarjetas y contenedores con efecto vidrio */
        .page-header, 
        .tarjeta-seccion, 
        .stat-card,
        .container .white-box { 
            background: rgba(255, 255, 255, 0.90) !important; 
            backdrop-filter: blur(15px); 
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15) !important;
            border-radius: 15px !important;
            margin-bottom: 20px !important;
        }

        .page-header {
            margin-top: 20px;
            width: 95%;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Modo oscuro */
        body.dark-mode .sidebar,
        body.dark-mode .page-header,
        body.dark-mode .tarjeta-seccion,
        body.dark-mode .stat-card {
            background: rgba(30, 30, 30, 0.85) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .sidebar-header h2 {
            color: #fff !important;
        }
    </style>
</head>
<body class="light-mode" data-metrica-page="portal">

    @php $fondosPortal = \App\Support\FondoActual::paraUbicacion('revistas'); @endphp
    <div class="fondos-bg" data-fondos-bg aria-hidden="true">
        @foreach($fondosPortal as $i => $src)
            <div class="fondo-capa{{ $i === 0 ? ' is-active' : '' }}" style="background-image:url('{{ $src }}')"></div>
        @endforeach
    </div>

    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ url('/') }}" class="logo-link">
                <img src="{{ asset('img/logo.jpg') }}" alt="ITINEWS" class="sidebar-logo">
                <h2>Portal ITINEWS</h2>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <h3 class="section-title">Navegación</h3>
                <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </a>
                <a href="{{ route('revistas.index') }}" class="nav-link {{ request()->routeIs('revistas.index') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    <span>Todas las Revistas</span>
                </a>
            </div>

            <div class="nav-section">
                <h3 class="section-title">Filtrar por Año</h3>
                <button class="nav-link filter-btn active" data-filter="all">
                    <i class="fas fa-layer-group"></i>
                    <span>Todas las Revistas</span>
                    <span class="badge" id="count-all">0</span>
                </button>
                @php
                $anios = \App\Models\Revista::selectRaw('YEAR(fecha_publicacion) as año')
                    ->distinct()
                    ->orderBy('año', 'desc')
                    ->pluck('año');
                @endphp
                @foreach($anios as $anio)
                    <button class="nav-link filter-btn" data-filter="{{ $anio }}">
                        <i class="fas fa-calendar"></i>
                        <span>Año {{ $anio }}</span>
                    </button>
                @endforeach
            </div>

            <div class="nav-section">
                <h3 class="section-title">Accesos Directos</h3>
                <button class="nav-link quick-btn" id="btnFavoritos" data-tipo="favoritos">
                    <i class="fas fa-heart"></i>
                    <span>Mis Favoritos</span>
                    <span class="badge" id="favoritosCount">0</span>
                </button>
                <button class="nav-link quick-btn" id="btnRecientes" data-tipo="recientes">
                    <i class="fas fa-history"></i>
                    <span>Recientemente Vistas</span>
                </button>
                <button class="nav-link quick-btn" id="btnDestacadas" data-tipo="destacadas">
                    <i class="fas fa-star"></i>
                    <span>Revistas Destacadas</span>
                </button>
            </div>

            <div class="nav-section">
                <h3 class="section-title">Información</h3>
                <button class="nav-link quick-btn" id="btnEstadisticas" data-tipo="estadisticas">
                    <i class="fas fa-chart-bar"></i>
                    <span>Mis Estadísticas</span>
                </button>
                <button class="nav-link quick-btn" id="btnAcerca" data-tipo="acerca">
                    <i class="fas fa-info-circle"></i>
                    <span>Acerca del Portal</span>
                </button>
                <button class="theme-toggle-btn nav-style" id="btnTema">
                    <i class="fas fa-moon"></i>
                    <span>Modo Oscuro</span>
                </button>
            </div>

            @auth
            <div class="nav-section">
                <h3 class="section-title">Cuenta</h3>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.revistas.crear') }}" class="nav-link {{ request()->routeIs('admin.revistas.crear') ? 'active' : '' }}">
                    <i class="fas fa-upload"></i>
                    <span>Subir Revista</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" class="nav-link" 
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </form>
            </div>
            @endauth
        </nav>
    </aside>

    <main class="main-content">
        @yield('content')
        
        <section id="secciones-especiales" class="secciones-container" style="display: none;">
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Portal ITINEWS - Universidad Politécnica del Valle de Toluca</p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>
    <script src="{{ asset('js/metricas.js') }}" defer></script>
    <script src="{{ asset('js/carrusel-fondos.js') }}" defer></script>
    @stack('scripts')
</body>
</html>