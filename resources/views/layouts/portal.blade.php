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
            --text-secondary: #4b5563 !important;
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
            background: #1f5b4f !important;
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
            color: #ffffff !important;
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
            background: #164236 !important;
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
            color: #ffffff !important;
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

        /* Sidebar semi-transparente y sin scroll horizontal */
        .sidebar {
            background-color: rgba(255, 255, 255, 0.92) !important; 
            backdrop-filter: blur(10px) !important; 
            border-right: 1px solid rgba(125, 75, 75, 0.3) !important;
            overflow-x: hidden !important;
            max-width: 280px !important;
            box-sizing: border-box !important;
        }

        .sidebar-nav {
            overflow-x: hidden !important;
            overflow-y: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .nav-section {
            max-width: 100% !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
        }

        .sidebar-header::after {
            display: none !important;
        }

        .nav-link, .filter-btn, .quick-btn, .theme-toggle-btn.nav-style {
            box-sizing: border-box !important;
            max-width: 100% !important;
        }

        .nav-link:hover, .filter-btn:hover, .quick-btn:hover, .theme-toggle-btn.nav-style:hover {
            transform: none !important;
            padding-left: 20px !important;
            background: rgba(31, 91, 79, 0.08) !important;
            color: #1f5b4f !important;
            border-left: 4px solid #1f5b4f !important;
        }

        .dark-mode .sidebar-nav .nav-link:hover, 
        .dark-mode .sidebar-nav .filter-btn:hover, 
        .dark-mode .sidebar-nav .quick-btn:hover,
        .dark-mode .sidebar-nav .theme-toggle-btn:hover {
            background: rgba(58, 154, 138, 0.16) !important;
            color: #72dfca !important;
            border-left: 4px solid #3a9a8a !important;
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

        .sidebar-logo-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #ffffff !important;
            padding: 8px 18px !important;
            border-radius: 14px !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22) !important;
            margin-bottom: 12px !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease !important;
        }

        .sidebar-logo-badge:hover {
            transform: scale(1.03) !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3) !important;
        }

        .sidebar-logo {
            height: 56px !important;
            width: auto !important;
            margin: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            object-fit: contain !important;
            display: block !important;
        }

        .acerca-logo-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #ffffff !important;
            padding: 10px 22px !important;
            border-radius: 16px !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
            margin: 0 auto 1.5rem !important;
        }

        .acerca-logo-img {
            max-width: 140px !important;
            height: auto !important;
            object-fit: contain !important;
            display: block !important;
        }

        .sidebar-header h2 {
            font-size: 1.3rem !important;
            margin: 0 !important;
            font-weight: 800 !important;
            line-height: 1.2;
            color: #fff !important;
        }

        /* Main content transparente para ver el fondo sin capas oscuras */
        .main-content {
            background-color: transparent !important; 
            background-image: none !important; 
            transform: none !important;
        }

        .main-content::before,
        .main-content:not(.all-bg)::before {
            display: none !important;
            content: none !important;
        }

        /* Tarjetas y contenedores con efecto vidrio */
        .page-header, 
        .tarjeta-seccion, 
        .stat-card,
        .container .white-box,
        .seccion-header { 
            background: rgba(255, 255, 255, 0.94) !important; 
            backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.12) !important;
            border-radius: 16px !important;
            margin-bottom: 20px !important;
        }

        .page-header,
        .seccion-header {
            margin-top: 20px;
            width: 95%;
            margin-left: auto;
            margin-right: auto;
            padding: 1.5rem 2rem !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .seccion-titulo {
            font-size: 1.85rem !important;
            font-weight: 800 !important;
            color: #164236 !important; /* Verde oscuro ITINEWS con excelente legibilidad */
            margin: 0 !important;
            letter-spacing: -0.02em;
        }
        
        /* Modo oscuro */
        body.dark-mode .sidebar,
        body.dark-mode .page-header,
        body.dark-mode .seccion-header,
        body.dark-mode .tarjeta-seccion,
        body.dark-mode .stat-card {
            background: rgba(30, 30, 30, 0.88) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.35) !important;
        }

        body.dark-mode .seccion-titulo {
            color: #f8f2ee !important;
        }

        body.dark-mode .sidebar-header h2 {
            color: #fff !important;
        }

        /* ============================================
           ESTADÍSTICAS TRANSFORMADAS
        ============================================ */
        .stats-dashboard {
            width: 95%;
            margin: 0 auto 40px auto;
        }
        .stats-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stats-kpi-card {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 16px !important;
            padding: 20px !important;
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06) !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
        }
        .stats-kpi-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 30px rgba(31, 91, 79, 0.15) !important;
        }
        .stats-kpi-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            flex-shrink: 0;
        }
        .stats-kpi-icon.icon-vistas { background: linear-gradient(135deg, #1f5b4f, #2a7a6a); }
        .stats-kpi-icon.icon-tiempo { background: linear-gradient(135deg, #7a1830, #9a1f3d); }
        .stats-kpi-icon.icon-paginas { background: linear-gradient(135deg, #2a7a6a, #164236); }
        .stats-kpi-icon.icon-favoritos { background: linear-gradient(135deg, #c42040, #7a1830); }

        .stats-kpi-info h4 {
            margin: 0;
            font-size: 0.85rem;
            color: #4b5563;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stats-kpi-info .kpi-num {
            margin: 4px 0 0 0;
            font-size: 1.75rem;
            font-weight: 800;
            color: #164236;
            line-height: 1.1;
        }

        .stats-spotlight-card {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-left: 6px solid #1f5b4f !important;
            border-radius: 16px !important;
            padding: 22px !important;
            margin-bottom: 25px !important;
            display: flex !important;
            align-items: center !important;
            gap: 24px !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08) !important;
        }
        .stats-spotlight-cover {
            width: 100px;
            height: 135px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 6px 18px rgba(0,0,0,0.18);
        }
        .stats-spotlight-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .stats-spotlight-info {
            flex: 1;
        }
        .stats-spotlight-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1f5b4f;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stats-section-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #164236;
            margin: 30px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-revistas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-revista-card {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 16px !important;
            padding: 16px !important;
            display: flex !important;
            gap: 16px !important;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06) !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
        }
        .stats-revista-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12) !important;
        }
        .stats-revista-thumb {
            width: 80px;
            height: 110px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .stats-revista-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .stats-revista-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
        }
        .stats-revista-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e1f23;
            margin: 0 0 6px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stats-revista-meta {
            font-size: 0.82rem;
            color: #4b5563;
            margin-bottom: 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .stats-revista-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .stats-btn-continuar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #1f5b4f, #164236);
            color: #fff !important;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 7px 14px;
            border-radius: 20px;
            transition: all 0.25s ease;
            align-self: flex-start;
        }
        .stats-btn-continuar:hover {
            background: linear-gradient(135deg, #2a7a6a, #1f5b4f);
            transform: translateX(3px);
            color: #fff !important;
        }

        body.dark-mode .stats-kpi-card,
        body.dark-mode .stats-spotlight-card,
        body.dark-mode .stats-revista-card {
            background: rgba(30, 30, 30, 0.88) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        body.dark-mode .stats-kpi-info h4,
        body.dark-mode .stats-revista-meta {
            color: #cbd5e1 !important;
        }
        body.dark-mode .stats-kpi-info .kpi-num,
        body.dark-mode .stats-section-title,
        body.dark-mode .stats-revista-title {
            color: #f8f2ee !important;
        }
        body.dark-mode .stats-spotlight-info h3 {
            color: #f8f2ee !important;
        }

        /* Marco blanco para títulos de subsección */
        .stats-section-header {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 16px !important;
            padding: 1.2rem 1.8rem !important;
            margin: 2rem 0 1.5rem 0 !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .stats-section-title {
            font-size: 1.35rem !important;
            font-weight: 800 !important;
            color: #164236 !important;
            margin: 0 !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .stats-section-subtitle {
            font-size: 0.85rem;
            color: #4b5563;
            font-weight: 500;
        }

        /* Botón sólido de reiniciar estadísticas */
        .btn-reiniciar-solido {
            background: #dc2626 !important;
            color: #ffffff !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 0.88rem !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35) !important;
            transition: all 0.25s ease !important;
        }
        .btn-reiniciar-solido:hover {
            background: #b91c1c !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.45) !important;
            color: #ffffff !important;
        }

        /* Meta tags badges en tarjetas de estadísticas */
        .stats-revista-meta {
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
            margin-bottom: 12px !important;
        }
        .meta-pill {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            font-size: 0.82rem !important;
            padding: 4px 10px !important;
            border-radius: 6px !important;
            background: rgba(31, 91, 79, 0.08) !important;
            color: #1f5b4f !important;
            width: fit-content !important;
            font-weight: 600 !important;
        }
        .meta-pill.pill-tiempo {
            background: rgba(122, 24, 48, 0.08) !important;
            color: #7a1830 !important;
            font-weight: 700 !important;
        }
        .meta-pill.pill-fecha,
        .meta-pill.pill-reciente {
            background: rgba(0, 0, 0, 0.05) !important;
            color: #4b5563 !important;
        }
        .meta-pill.pill-destacada {
            background: rgba(122, 24, 48, 0.08) !important;
            color: #7a1830 !important;
            font-weight: 700 !important;
        }

        body.dark-mode .meta-pill {
            background: rgba(58, 154, 138, 0.22) !important;
            color: #a7f3d0 !important;
            border: 1px solid rgba(58, 154, 138, 0.35) !important;
        }
        body.dark-mode .meta-pill.pill-tiempo {
            background: rgba(220, 38, 38, 0.22) !important;
            color: #fca5a5 !important;
            border: 1px solid rgba(220, 38, 38, 0.35) !important;
        }
        body.dark-mode .meta-pill.pill-fecha,
        body.dark-mode .meta-pill.pill-reciente {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #f1f5f9 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
        }
        body.dark-mode .meta-pill.pill-destacada {
            background: rgba(245, 158, 11, 0.22) !important;
            color: #fde047 !important;
            border: 1px solid rgba(245, 158, 11, 0.4) !important;
            font-weight: 700 !important;
        }

        /* Modal propio de confirmación de reinicio */
        .custom-confirm-overlay {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100dvh !important;
            background: rgba(15, 20, 25, 0.75) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            z-index: 9999999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 1.5rem !important;
            box-sizing: border-box !important;
            animation: fadeIn 0.25s ease !important;
        }
        .custom-confirm-card {
            background: #ffffff !important;
            border-radius: 20px !important;
            padding: 2.2rem 1.8rem !important;
            max-width: 440px !important;
            width: 100% !important;
            text-align: center !important;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4) !important;
            border: 2px solid #ef4444 !important;
            animation: slideUp 0.3s ease !important;
        }
        .custom-confirm-icon {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.85rem;
            margin: 0 auto 15px auto;
        }
        .custom-confirm-card h3 {
            margin: 0 0 10px 0;
            color: #1e1f23;
            font-size: 1.35rem;
            font-weight: 800;
        }
        .custom-confirm-card p {
            margin: 0 0 22px 0;
            color: #4b5563;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .custom-confirm-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn-confirm-cancelar {
            background: #e5e7eb;
            color: #374151;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-confirm-cancelar:hover {
            background: #d1d5db;
        }
        .btn-confirm-aceptar {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
            transition: all 0.2s ease;
        }
        .btn-confirm-aceptar:hover {
            background: #b91c1c;
        }

        /* ============================================
           REBRANDING EDITORIAL DE SECCIONES
           (Favoritos, Recientes, Destacadas, Acerca)
        ============================================ */
        .editorial-section-container {
            width: 95%;
            margin: 0 auto 40px auto;
        }

        .editorial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 22px;
            margin-bottom: 30px;
        }

        .editorial-card {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 18px !important;
            overflow: hidden !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08) !important;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease !important;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .editorial-card:hover {
            transform: translateY(-8px) !important;
            box-shadow: 0 16px 40px rgba(31, 91, 79, 0.18) !important;
            border-color: rgba(31, 91, 79, 0.4) !important;
        }
        .editorial-cover-wrap {
            position: relative;
            width: 100%;
            height: 280px;
            background: #e8d5c8;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .editorial-cover-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .editorial-card:hover .editorial-cover-wrap img {
            transform: scale(1.06);
        }
        .editorial-badge-anio {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(31, 91, 79, 0.9);
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            backdrop-filter: blur(6px);
            letter-spacing: 0.5px;
        }
        .editorial-btn-fav {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            color: #7a1830;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.25s ease;
            font-size: 1rem;
        }
        .editorial-btn-fav:hover {
            transform: scale(1.15);
            background: #7a1830;
            color: #fff;
        }
        .editorial-btn-fav.active {
            background: #7a1830;
            color: #fff;
        }

        .editorial-body {
            padding: 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .editorial-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1e1f23;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }
        .editorial-desc {
            font-size: 0.88rem;
            color: #4b5563;
            line-height: 1.5;
            margin: 0 0 14px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .editorial-footer {
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .editorial-btn-leer {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #1f5b4f, #164236);
            color: #fff !important;
            text-decoration: none;
            padding: 9px 16px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 4px 14px rgba(31, 91, 79, 0.3);
            transition: all 0.25s ease;
        }
        .editorial-btn-leer:hover {
            background: linear-gradient(135deg, #2a7a6a, #1f5b4f);
            transform: translateY(-2px);
            color: #fff !important;
            box-shadow: 0 6px 18px rgba(31, 91, 79, 0.4);
        }

        /* Empty state editorial */
        .editorial-empty-card {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 20px !important;
            padding: 50px 24px !important;
            text-align: center !important;
            max-width: 650px !important;
            margin: 30px auto !important;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08) !important;
        }
        .editorial-empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(31, 91, 79, 0.12), rgba(122, 24, 48, 0.12));
            color: #1f5b4f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 20px auto;
        }
        .editorial-empty-card h3 {
            font-size: 1.45rem;
            font-weight: 800;
            color: #1e1f23;
            margin: 0 0 10px 0;
        }
        .editorial-empty-card p {
            font-size: 0.95rem;
            color: #4b5563;
            line-height: 1.6;
            margin: 0 0 24px 0;
        }

        /* Acerca del Portal - Editorial Grid */
        .acerca-hero-card {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 20px !important;
            padding: 30px !important;
            margin-bottom: 25px !important;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08) !important;
            display: flex;
            gap: 25px;
            align-items: center;
        }
        .acerca-logo-wrap {
            width: 120px;
            height: 120px;
            border-radius: 16px;
            background: #fff;
            padding: 10px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .acerca-logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .acerca-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .acerca-feat-card {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 18px !important;
            padding: 24px !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
        }
        .acerca-feat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
            margin-bottom: 15px;
        }
        .feat-1 { background: linear-gradient(135deg, #1f5b4f, #2a7a6a); }
        .feat-2 { background: linear-gradient(135deg, #7a1830, #9a1f3d); }
        .feat-3 { background: linear-gradient(135deg, #2a7a6a, #164236); }

        body.dark-mode .stats-section-header,
        body.dark-mode .editorial-card,
        body.dark-mode .editorial-empty-card,
        body.dark-mode .acerca-hero-card,
        body.dark-mode .acerca-feat-card,
        body.dark-mode .custom-confirm-card {
            background: rgba(30, 30, 30, 0.88) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        body.dark-mode .stats-section-title,
        body.dark-mode .editorial-title,
        body.dark-mode .editorial-empty-card h3,
        body.dark-mode .custom-confirm-card h3 {
            color: #f8f2ee !important;
        }
        body.dark-mode .stats-section-subtitle,
        body.dark-mode .editorial-desc,
        body.dark-mode .editorial-empty-card p,
        body.dark-mode .custom-confirm-card p {
            color: #cbd5e1 !important;
        }
        body.dark-mode .editorial-footer {
            border-top-color: rgba(255, 255, 255, 0.08) !important;
        }
        body.dark-mode .btn-confirm-cancelar {
            background: #374151;
            color: #e5e7eb;
        }

        /* Tipografía de Spotlight */
        .stats-spotlight-title {
            margin: 0 0 6px 0;
            font-size: 1.35rem;
            color: #164236;
            font-weight: 800;
        }
        .stats-spotlight-desc {
            margin: 0 0 12px 0;
            color: #4b5563;
            font-size: 0.92rem;
            line-height: 1.5;
        }
        body.dark-mode .stats-spotlight-title {
            color: #ffffff !important;
        }
        body.dark-mode .stats-spotlight-desc {
            color: #e2e8f0 !important;
        }
        body.dark-mode .stats-spotlight-desc strong {
            color: #72dfca !important;
        }

        /* Tipografía y elementos de Acerca del Portal */
        .acerca-hero-title {
            margin: 0 0 10px 0;
            font-size: 1.45rem;
            color: #164236;
            font-weight: 800;
            line-height: 1.3;
        }
        .acerca-hero-desc {
            margin: 0;
            color: #4b5563;
            font-size: 0.96rem;
            line-height: 1.6;
        }
        .acerca-feat-title {
            margin: 0 0 8px 0;
            font-size: 1.15rem;
            font-weight: 700;
        }
        .acerca-feat-title.feat-title-1 { color: #164236; }
        .acerca-feat-title.feat-title-2 { color: #7a1830; }
        .acerca-feat-title.feat-title-3 { color: #1f5b4f; }
        .acerca-feat-desc {
            margin: 0;
            color: #4b5563;
            font-size: 0.88rem;
            line-height: 1.5;
        }
        .acerca-cta-title {
            margin: 0 0 6px 0;
            font-size: 1.15rem;
            color: #164236;
            font-weight: 700;
        }
        .acerca-cta-desc {
            margin: 0 0 16px 0;
            color: #4b5563;
            font-size: 0.9rem;
        }
        .acerca-logo-wrap {
            width: 110px;
            height: 110px;
            border-radius: 16px;
            background: #ffffff;
            border: 2px solid rgba(31, 91, 79, 0.2);
            padding: 8px;
            box-sizing: border-box;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .acerca-logo-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        body.dark-mode .acerca-hero-title {
            color: #ffffff !important;
        }
        body.dark-mode .acerca-hero-desc {
            color: #f1f5f9 !important;
        }
        body.dark-mode .acerca-hero-desc strong {
            color: #72dfca !important;
        }
        body.dark-mode .acerca-feat-title.feat-title-1 {
            color: #6ee7b7 !important;
        }
        body.dark-mode .acerca-feat-title.feat-title-2 {
            color: #fca5a5 !important;
        }
        body.dark-mode .acerca-feat-title.feat-title-3 {
            color: #93c5fd !important;
        }
        body.dark-mode .acerca-feat-desc {
            color: #cbd5e1 !important;
        }
        body.dark-mode .acerca-cta-title {
            color: #ffffff !important;
        }
        body.dark-mode .acerca-cta-desc {
            color: #cbd5e1 !important;
        }
        body.dark-mode .acerca-logo-wrap {
            background: #1e2422 !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        /* Footer y enlaces legales en Portal */
        .portal-footer {
            text-align: center;
            padding: 2.2rem 20px 2.8rem;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(10px);
            margin-left: 280px;
            width: calc(100% - 280px);
            box-sizing: border-box;
            transition: all 0.4s ease;
        }
        body.dark-mode .portal-footer {
            background: rgba(25, 26, 30, 0.94) !important;
            border-top-color: rgba(255, 255, 255, 0.1) !important;
        }
        .portal-footer-content p {
            margin: 0 0 10px 0;
            font-size: 0.93rem;
            color: #4b5563;
        }
        body.dark-mode .portal-footer-content p {
            color: #cbd5e1 !important;
        }
        .portal-legal-links {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .legal-link-btn {
            background: none;
            border: none;
            color: #1f5b4f;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 6px;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 0.2s ease;
        }
        .legal-link-btn:hover {
            color: #164236;
        }
        body.dark-mode .legal-link-btn {
            color: #72dfca !important;
        }
        body.dark-mode .legal-link-btn:hover {
            color: #a7f3d0 !important;
        }
        .legal-sep {
            color: #9ca3af;
            font-size: 0.8rem;
        }

        /* Modal Legal (Términos, Privacidad, Cookies) */
        .legal-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 20, 25, 0.75);
            backdrop-filter: blur(8px);
            z-index: 100000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
            animation: fadeIn 0.25s ease;
        }
        .legal-modal-overlay.active {
            display: flex;
        }
        .legal-modal-card {
            background: #ffffff;
            border-radius: 20px;
            max-width: 680px;
            width: 100%;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(0,0,0,0.1);
        }
        body.dark-mode .legal-modal-card {
            background: #1e2422 !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #f1f5f9;
        }
        .legal-modal-header {
            padding: 20px 24px;
            background: #1f5b4f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .legal-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .legal-modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.4rem;
            cursor: pointer;
            opacity: 0.85;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .legal-modal-close:hover {
            opacity: 1;
            transform: scale(1.1);
        }
        .legal-modal-body {
            padding: 24px;
            overflow-y: auto;
            font-size: 0.92rem;
            line-height: 1.65;
            color: #374151;
        }
        body.dark-mode .legal-modal-body {
            color: #cbd5e1;
        }
        .legal-modal-body h4 {
            color: #164236;
            margin: 18px 0 6px 0;
            font-size: 1.05rem;
            font-weight: 700;
        }
        body.dark-mode .legal-modal-body h4 {
            color: #6ee7b7;
        }
        .legal-modal-footer {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
        }
        body.dark-mode .legal-modal-footer {
            background: #161b1a;
            border-top-color: rgba(255, 255, 255, 0.1);
        }
        .btn-legal-entendido {
            background: #1f5b4f;
            color: #fff;
            border: none;
            padding: 8px 22px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .btn-legal-entendido:hover {
            background: #164236;
        }

        /* Cookie Consent Banner */
        .cookie-banner {
            position: fixed;
            bottom: 20px;
            right: 20px;
            max-width: 480px;
            width: calc(100% - 40px);
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            border: 2px solid #1f5b4f;
            z-index: 99999;
            box-sizing: border-box;
            display: none;
            animation: slideUp 0.3s ease;
        }
        body.dark-mode .cookie-banner {
            background: #1e2422 !important;
            border-color: #3a9a8a !important;
            color: #f1f5f9;
        }
        .cookie-banner-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: #164236;
        }
        body.dark-mode .cookie-banner-header {
            color: #6ee7b7;
        }
        .cookie-banner-header h4 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .cookie-banner-text {
            font-size: 0.86rem;
            color: #4b5563;
            line-height: 1.5;
            margin: 0 0 16px 0;
        }
        body.dark-mode .cookie-banner-text {
            color: #cbd5e1;
        }
        .cookie-banner-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-cookie-accept {
            flex: 1;
            background: #1f5b4f;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.86rem;
            cursor: pointer;
            transition: background 0.2s ease;
            text-align: center;
        }
        .btn-cookie-accept:hover {
            background: #164236;
        }
        .btn-cookie-essential {
            background: #e5e7eb;
            color: #374151;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.86rem;
            cursor: pointer;
            transition: background 0.2s ease;
            text-align: center;
        }
        .btn-cookie-essential:hover {
            background: #d1d5db;
        }
        body.dark-mode .btn-cookie-essential {
            background: #374151;
            color: #e5e7eb;
        }

        .btn-cookie-pref {
            background: transparent;
            color: #1f5b4f;
            border: 1.5px solid #1f5b4f;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.86rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-align: center;
        }
        .btn-cookie-pref:hover {
            background: rgba(31, 91, 79, 0.08);
            border-color: #164236;
        }
        body.dark-mode .btn-cookie-pref {
            color: #72dfca;
            border-color: #72dfca;
        }
        body.dark-mode .btn-cookie-pref:hover {
            background: rgba(114, 223, 202, 0.12);
        }

        /* Estilos del Centro de Preferencias */
        .cookie-pref-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 12px;
        }
        body.dark-mode .cookie-pref-item {
            background: #1a2220;
            border-color: #2a3a35;
        }
        .cookie-pref-info strong {
            display: block;
            font-size: 0.95rem;
            color: #1f2937;
            margin-bottom: 4px;
        }
        body.dark-mode .cookie-pref-info strong {
            color: #f1f5f9;
        }
        .cookie-pref-info p {
            margin: 0;
            font-size: 0.84rem;
            color: #64748b;
            line-height: 1.4;
        }
        body.dark-mode .cookie-pref-info p {
            color: #94a3b8;
        }
        .badge-siempre-activa {
            display: inline-block;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #166534;
            background: #dcfce7;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }
        body.dark-mode .badge-siempre-activa {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
        }
        .switch-pref {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
            flex-shrink: 0;
        }
        .switch-pref input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider-pref {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #cbd5e1;
            transition: .25s ease;
            border-radius: 26px;
        }
        .slider-pref:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .25s ease;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .switch-pref input:checked + .slider-pref {
            background-color: #1f5b4f;
        }
        .switch-pref input:checked + .slider-pref:before {
            transform: translateX(22px);
        }
        .btn-guardar-pref {
            background: #1f5b4f;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .btn-guardar-pref:hover {
            background: #164236;
        }
        .msg-pref-guardado {
            margin-top: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        body.dark-mode .msg-pref-guardado {
            background: rgba(34, 197, 94, 0.15);
            color: #86efac;
        }

        @media (max-width: 600px) {
            .stats-spotlight-card,
            .acerca-hero-card {
                flex-direction: column !important;
                text-align: center;
            }
            .stats-spotlight-cover,
            .acerca-logo-wrap {
                margin: 0 auto;
            }
            .stats-spotlight-badge {
                margin: 0 auto 10px auto;
            }
            .stats-btn-continuar {
                align-self: center;
                width: 100%;
            }
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
                <div class="sidebar-logo-badge">
                    <img src="{{ asset('img/logo.jpg') }}" alt="ITINEWS" class="sidebar-logo">
                </div>
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
                $anios = \App\Models\Revista::activas()
                    ->selectRaw('YEAR(fecha_publicacion) as año')
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

    <footer class="portal-footer">
        <div class="portal-footer-content">
            <p>&copy; {{ date('Y') }} Portal ITINEWS - Universidad Politécnica del Valle de Toluca</p>
            <div class="portal-legal-links">
                <button type="button" class="legal-link-btn" onclick="abrirModalLegal('terminos')">Términos y Condiciones</button>
                <span class="legal-sep">&bull;</span>
                <button type="button" class="legal-link-btn" onclick="abrirModalLegal('privacidad')">Aviso de Privacidad</button>
                <span class="legal-sep">&bull;</span>
                <button type="button" class="legal-link-btn" onclick="abrirModalLegal('cookies')">Política de Cookies</button>
                <span class="legal-sep">&bull;</span>
                <button type="button" class="legal-link-btn" onclick="abrirModalLegal('preferencias')">Preferencias de Privacidad</button>
            </div>
        </div>
    </footer>

    <!-- MODAL TÉRMINOS Y CONDICIONES -->
    <div id="modalLegalTerminos" class="legal-modal-overlay" onclick="if(event.target===this) cerrarModalLegal('terminos')">
        <div class="legal-modal-card">
            <div class="legal-modal-header">
                <h3><i class="fas fa-file-contract"></i> Términos y Condiciones de Uso</h3>
                <button type="button" class="legal-modal-close" onclick="cerrarModalLegal('terminos')" aria-label="Cerrar">&times;</button>
            </div>
            <div class="legal-modal-body">
                <h4>1. Aceptación de los Términos</h4>
                <p>Al acceder y utilizar el <strong>Portal ITINEWS</strong> de la Universidad Politécnica del Valle de Toluca (UPVT), usted acepta sujetarse a los presentes Términos y Condiciones de uso con fines exclusivamente académicos, informativos y de divulgación científica.</p>

                <h4>2. Propiedad Intelectual y Derechos de Autor</h4>
                <p>Todo el contenido publicado, incluyendo artículos, reseñas, imágenes, diseños editoriales, portadas y logotipos, se encuentra protegido por las leyes de propiedad intelectual mexicanas e internacionales. Los autores de los artículos conservan sus derechos morales, otorgando a la UPVT el derecho de difusión no comercial.</p>

                <h4>3. Uso Permitido y Citas</h4>
                <p>Se autoriza la consulta, lectura y cita académica de las publicaciones siempre que se otorgue el debido crédito al autor y a la revista ITINEWS UPVT, indicando el número y año de la edición correspondiente. Queda prohibida la reproducción total o parcial con fines de lucro sin autorización previa por escrito.</p>

                <h4>4. Responsabilidad del Usuario</h4>
                <p>El usuario se compromete a hacer un uso lícito y respetuoso de los recursos del portal, absteniéndose de realizar actos que puedan dañar, inutilizar, sobrecargar o deteriorar los servidores o servicios del sistema.</p>
            </div>
            <div class="legal-modal-footer">
                <button type="button" class="btn-legal-entendido" onclick="cerrarModalLegal('terminos')">Entendido</button>
            </div>
        </div>
    </div>

    <!-- MODAL AVISO DE PRIVACIDAD -->
    <div id="modalLegalPrivacidad" class="legal-modal-overlay" onclick="if(event.target===this) cerrarModalLegal('privacidad')">
        <div class="legal-modal-card">
            <div class="legal-modal-header">
                <h3><i class="fas fa-shield-alt"></i> Aviso de Privacidad Integral</h3>
                <button type="button" class="legal-modal-close" onclick="cerrarModalLegal('privacidad')" aria-label="Cerrar">&times;</button>
            </div>
            <div class="legal-modal-body">
                <h4>1. Responsable del Tratamiento</h4>
                <p>La <strong>Universidad Politécnica del Valle de Toluca (UPVT)</strong>, a través de la División de Ingeniería en Tecnologías de la Información, es la responsable del tratamiento de los datos en cumplimiento con la <em>Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados</em>.</p>

                <h4>2. Datos Recabados y Finalidad</h4>
                <p>El Portal ITINEWS es un repositorio de libre acceso y <strong>no solicita ni almacena datos personales sensibles</strong> de los lectores generales. Las estadísticas de lectura (tiempo dedicado, páginas visitadas y revistas favoritas) se procesan y almacenan de forma local en su navegador web.</p>

                <h4>3. Métricas Anónimas</h4>
                <p>Para fines de mejora continua y acreditación académica, el portal puede registrar métricas técnicas agregadas y anónimas (ediciones más consultadas y concurrencia) sin vincularlas a su identidad ni a direcciones IP personales.</p>

                <h4>4. Ejercicio de Derechos ARCO</h4>
                <p>Para cualquier consulta referente a la privacidad del portal o el ejercicio de derechos ARCO, puede comunicarse con la Unidad de Transparencia de la UPVT a través de sus canales institucionales oficiales.</p>
            </div>
            <div class="legal-modal-footer">
                <button type="button" class="btn-legal-entendido" onclick="cerrarModalLegal('privacidad')">Entendido</button>
            </div>
        </div>
    </div>

    <!-- MODAL POLÍTICA DE COOKIES -->
    <div id="modalLegalCookies" class="legal-modal-overlay" onclick="if(event.target===this) cerrarModalLegal('cookies')">
        <div class="legal-modal-card">
            <div class="legal-modal-header">
                <h3><i class="fas fa-cookie-bite"></i> Política de Cookies y Almacenamiento</h3>
                <button type="button" class="legal-modal-close" onclick="cerrarModalLegal('cookies')" aria-label="Cerrar">&times;</button>
            </div>
            <div class="legal-modal-body">
                <h4>1. ¿Qué tecnologías utilizamos?</h4>
                <p>El Portal ITINEWS utiliza <strong>almacenamiento web funcional (LocalStorage)</strong> y cookies técnicas esenciales. No utilizamos cookies publicitarias, comerciales ni rastreadores de terceros.</p>

                <h4>2. Finalidades Funcionales</h4>
                <ul>
                    <li><strong>Preferencia Visual:</strong> Recordar si prefieres navegar en Modo Claro o Modo Oscuro.</li>
                    <li><strong>Favoritos:</strong> Guardar las revistas que marcas con corazón en tu biblioteca personal del navegador.</li>
                    <li><strong>Métricas de Lectura Personal:</strong> Permitir que lleves el control del tiempo dedicado y páginas leídas en tu sección "Mis Estadísticas".</li>
                    <li><strong>Sesión Administrativa:</strong> En el caso de docentes y administradores, validar el token CSRF y la sesión segura de Laravel.</li>
                </ul>

                <h4>3. Control y Eliminación</h4>
                <p>Puedes borrar tus datos locales en cualquier momento desde la sección <em>Mis Estadísticas &gt; Reiniciar Estadísticas</em> o limpiando el almacenamiento de tu navegador.</p>
            </div>
            <div class="legal-modal-footer">
                <button type="button" class="btn-legal-entendido" onclick="cerrarModalLegal('cookies')">Entendido</button>
            </div>
        </div>
    </div>

    <!-- MODAL PREFERENCIAS DE PRIVACIDAD -->
    <div id="modalLegalPreferencias" class="legal-modal-overlay" onclick="if(event.target===this) cerrarModalLegal('preferencias')">
        <div class="legal-modal-card">
            <div class="legal-modal-header">
                <h3><i class="fas fa-sliders-h"></i> Centro de Preferencias de Privacidad</h3>
                <button type="button" class="legal-modal-close" onclick="cerrarModalLegal('preferencias')" aria-label="Cerrar">&times;</button>
            </div>
            <div class="legal-modal-body">
                <p style="margin-bottom: 16px; font-size: 0.92rem; color: #4b5563;">
                    En el <strong>Portal ITINEWS</strong> respetamos tu privacidad. Puedes configurar qué tipo de almacenamiento local permites durante tu navegación.
                </p>

                <!-- Tarjeta: Esenciales -->
                <div class="cookie-pref-item">
                    <div class="cookie-pref-info">
                        <strong><i class="fas fa-lock" style="color: #1f5b4f;"></i> Cookies Técnicas y Esenciales</strong>
                        <p>Necesarias para la navegación básica, seguridad contra ataques CSRF y gestión de sesiones administrativas. No se pueden desactivar.</p>
                    </div>
                    <div class="cookie-pref-switch">
                        <span class="badge-siempre-activa">Siempre activas</span>
                    </div>
                </div>

                <!-- Tarjeta: Funcionales -->
                <div class="cookie-pref-item">
                    <div class="cookie-pref-info">
                        <strong><i class="fas fa-heart" style="color: #7a1830;"></i> Almacenamiento Funcional y Personalización</strong>
                        <p>Permite guardar tu preferencia de Tema Claro/Oscuro, tu lista local de Revistas Favoritas y calcular tus métricas privadas de lectura en "Mis Estadísticas".</p>
                    </div>
                    <div class="cookie-pref-switch">
                        <label class="switch-pref">
                            <input type="checkbox" id="checkPrefFuncionales" checked>
                            <span class="slider-pref"></span>
                        </label>
                    </div>
                </div>

                <div id="msgGuardadoPreferencias" class="msg-pref-guardado" style="display: none;">
                    <i class="fas fa-circle-check"></i> Tus preferencias de privacidad han sido actualizadas.
                </div>
            </div>
            <div class="legal-modal-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <button type="button" class="btn-cookie-essential" onclick="gestionarCookies('esenciales'); cerrarModalLegal('preferencias');">Solo necesarias</button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn-guardar-pref" onclick="guardarDesdeModalPreferencias()">Guardar preferencias</button>
                    <button type="button" class="btn-legal-entendido" onclick="gestionarCookies('todas'); cerrarModalLegal('preferencias');">Permitir todas</button>
                </div>
            </div>
        </div>
    </div>

    <!-- BANNER DE COOKIES FUNCIONALES -->
    <aside id="bannerCookies" class="cookie-banner" role="dialog" aria-labelledby="cookieBannerTitle" aria-describedby="cookieBannerDesc">
        <div class="cookie-banner-header">
            <i class="fas fa-cookie-bite fa-lg"></i>
            <h4 id="cookieBannerTitle">Privacidad y Cookies</h4>
        </div>
        <p id="cookieBannerDesc" class="cookie-banner-text">
            Utilizamos cookies esenciales y almacenamiento local funcional para guardar tus favoritos, recordar tu tema visual (claro/oscuro) y tus estadísticas privadas de lectura. No realizamos rastreo comercial.
        </p>
        <div class="cookie-banner-actions">
            <button type="button" class="btn-cookie-accept" onclick="gestionarCookies('todas')">
                <i class="fas fa-check"></i> Permitir cookies
            </button>
            <button type="button" class="btn-cookie-essential" onclick="gestionarCookies('esenciales')">
                Solo necesarias
            </button>
            <button type="button" class="btn-cookie-pref" onclick="abrirModalLegal('preferencias')">
                <i class="fas fa-sliders-h"></i> Preferencias
            </button>
        </div>
    </aside>
    
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>
    <script src="{{ asset('js/metricas.js') }}" defer></script>
    <script src="{{ asset('js/carrusel-fondos.js') }}" defer></script>
    
    <script>
        function abrirModalLegal(tipo) {
            const idMap = {
                'terminos': 'modalLegalTerminos',
                'privacidad': 'modalLegalPrivacidad',
                'cookies': 'modalLegalCookies',
                'preferencias': 'modalLegalPreferencias'
            };
            const modal = document.getElementById(idMap[tipo]);
            if (modal) {
                if (tipo === 'preferencias') {
                    const consent = localStorage.getItem('itinews_cookies_consent');
                    const check = document.getElementById('checkPrefFuncionales');
                    if (check) {
                        check.checked = (consent !== 'esenciales');
                    }
                }
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function cerrarModalLegal(tipo) {
            const idMap = {
                'terminos': 'modalLegalTerminos',
                'privacidad': 'modalLegalPrivacidad',
                'cookies': 'modalLegalCookies',
                'preferencias': 'modalLegalPreferencias'
            };
            const modal = document.getElementById(idMap[tipo]);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function guardarDesdeModalPreferencias() {
            const check = document.getElementById('checkPrefFuncionales');
            const nivel = (check && check.checked) ? 'todas' : 'esenciales';
            gestionarCookies(nivel);
            const msg = document.getElementById('msgGuardadoPreferencias');
            if (msg) {
                msg.style.display = 'flex';
                setTimeout(() => {
                    msg.style.display = 'none';
                    cerrarModalLegal('preferencias');
                }, 800);
            } else {
                cerrarModalLegal('preferencias');
            }
        }

        function gestionarCookies(nivel) {
            try {
                localStorage.setItem('itinews_cookies_consent', nivel);
                localStorage.setItem('itinews_cookies_date', new Date().toISOString());
            } catch(e) {}
            const banner = document.getElementById('bannerCookies');
            if (banner) banner.style.display = 'none';
        }

        function mostrarBannerCookies(forzar = false) {
            const banner = document.getElementById('bannerCookies');
            if (!banner) return;
            const consent = localStorage.getItem('itinews_cookies_consent');
            if (!consent || forzar) {
                banner.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                mostrarBannerCookies(false);
            }, 800);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    cerrarModalLegal('terminos');
                    cerrarModalLegal('privacidad');
                    cerrarModalLegal('cookies');
                    cerrarModalLegal('preferencias');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>