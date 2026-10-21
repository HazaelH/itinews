<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ITINEWS - Portafolio</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link href="{{ asset('img/favicon.ico') }}" rel="icon">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">

    <link href="{{ asset('css/base.css') }}" rel="stylesheet">
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">
    <link href="{{ asset('css/carrusel-fondos.css') }}" rel="stylesheet">
    
    <style>
        /* ============================================
           VARIABLES Y ESTILOS GLOBALES
        ============================================ */
        :root {
            --vino: #7a1830;
            --vino-light: #9a1f3d;
            --vino-dark: #5e1023;
            --verde: #1f5b4f;
            --verde-light: #2a7a6a;
            --verde-dark: #164236;
            --crema: #f8f2ee;
            --crema-dark: #e8d5c8;
            --dark: #1e1f23;
            --white: #ffffff;
            --text-secondary: #666666;
        }

        /* ============================================
           SCROLLBAR PERSONALIZADA
        ============================================ */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--crema);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--verde);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--verde-dark);
        }

        /* ============================================
           ANIMACIONES MEJORADAS
        ============================================ */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes floatSlow {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(2deg);
            }
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(31, 91, 79, 0.3), 0 0 40px rgba(122, 24, 48, 0.2);
            }
            50% {
                box-shadow: 0 0 30px rgba(31, 91, 79, 0.5), 0 0 60px rgba(122, 24, 48, 0.3);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }
            100% {
                background-position: 200% center;
            }
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(60px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .animate-fade-left {
            animation: fadeInLeft 0.8s ease-out forwards;
        }

        .animate-fade-right {
            animation: fadeInRight 0.8s ease-out forwards;
        }

        .animate-float {
            animation: floatSlow 6s ease-in-out infinite;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }

        /* ============================================
           FONDO DINÁMICO
        ============================================ */
        body {
            background: var(--crema);
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 30% 40%, rgba(31, 91, 79, 0.08) 0%, transparent 30%),
                radial-gradient(circle at 70% 60%, rgba(122, 24, 48, 0.06) 0%, transparent 30%),
                radial-gradient(circle at 50% 50%, rgba(248, 242, 238, 0.5) 0%, transparent 70%);
            animation: floatSlow 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        /* ============================================
           BARRA DE BÚSQUEDA
        ============================================ */
        .search-bar-container {
            max-width: 600px;
            margin: 0 auto 1.5rem;
            position: relative;
            z-index: 2;
        }

        .search-bar {
            position: relative;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            box-shadow: 0 4px 20px rgba(30, 31, 35, 0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .search-bar:focus-within {
            border-color: var(--verde);
            box-shadow: 0 6px 25px rgba(31, 91, 79, 0.2);
        }

        .search-bar i {
            color: var(--verde);
            font-size: 1.1rem;
            margin-right: 0.8rem;
        }

        .search-bar input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.95rem;
            color: var(--dark);
            padding: 0.7rem 0;
            font-family: 'Inter', sans-serif;
        }

        .search-bar input::placeholder {
            color: #999;
        }

        .resultados-busqueda {
            font-size: 0.8rem;
            color: var(--text-secondary);
            white-space: nowrap;
            margin-left: 1rem;
        }

        /* ============================================
           BOTÓN VOLVER ARRIBA
        ============================================ */
        .scroll-top-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--verde), var(--verde-dark));
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(31, 91, 79, 0.3);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
            z-index: 999;
        }

        .scroll-top-btn.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .scroll-top-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(31, 91, 79, 0.5);
        }

        /* ============================================
           NAVBAR ELEGANTE (Colores sólidos)
        ============================================ */
        .navbar {
            background: #164236;
            backdrop-filter: blur(15px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            transition: all 0.4s ease;
            padding: 0.75rem 2rem;
            position: fixed;
            width: 100%;
            z-index: 1000;
            border-bottom: 2px solid #1f5b4f;
        }

        .navbar.scrolled {
            background: #11342a;
            padding: 0.45rem 2rem;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.35);
        }

        .navbar-brand {
            transition: all 0.4s ease;
            position: relative;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-brand img {
            height: 65px;
            width: auto;
            border-radius: 15px;
            transition: all 0.4s ease;
            border: 2px solid var(--verde);
            box-shadow: 0 4px 15px rgba(31, 91, 79, 0.3);
        }

        .nav-link {
            position: relative;
            font-weight: 600;
            color: var(--crema) !important;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
            padding: 0.5rem 1.2rem;
            border-radius: 25px;
            background: transparent;
        }

        .nav-link:hover {
            background: rgba(122, 24, 48, 0.3);
            color: var(--white) !important;
            transform: translateY(-2px);
        }

        .nav-link i {
            margin-right: 6px;
            color: var(--verde-light);
            transition: all 0.3s ease;
        }

        .nav-link:hover i {
            color: var(--crema);
            transform: scale(1.2);
        }

        /* ============================================
           HERO SECTION IMPACTANTE
        ============================================ */
        #home {
            position: relative;
            min-height: 100vh;
            padding-top: 100px;
            z-index: 1;
        }

        .badge-custom {
            display: inline-block;
            padding: 0.7rem 1.5rem;
            background: linear-gradient(135deg, var(--verde), var(--verde-dark));
            color: var(--white);
            border-radius: 50px;
            font-weight: 700;
            letter-spacing: 2px;
            box-shadow: 0 4px 15px rgba(31, 91, 79, 0.4);
            border: 2px solid var(--verde-light);
            position: relative;
            overflow: hidden;
        }

        .badge-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 3s infinite;
        }

        .display-4 {
            font-size: 3.5rem;
            font-weight: 900 !important;
            line-height: 1.2;
            color: var(--dark);
        }

        .display-4 span {
            color: var(--verde);
            position: relative;
            display: inline-block;
        }

        .display-4 span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background: rgba(122, 24, 48, 0.2);
            border-radius: 10px;
            z-index: -1;
        }

        .typed-text-output {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--vino);
            text-shadow: 0 2px 10px rgba(122, 24, 48, 0.1);
        }

        /* Botón de acceso mejorado */
        .access-button-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .btn-access {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--vino), var(--vino-dark));
            border-radius: 50%;
            transition: all 0.4s ease;
            animation: pulseGlow 2s infinite;
            border: 3px solid var(--vino-light);
        }

        .btn-access span {
            display: block;
            width: 0;
            height: 0;
            border-left: 18px solid var(--white);
            border-top: 12px solid transparent;
            border-bottom: 12px solid transparent;
            margin-left: 5px;
            transition: all 0.3s ease;
        }

        .btn-access:hover {
            transform: scale(1.15) rotate(360deg);
            background: linear-gradient(135deg, var(--vino-light), var(--vino));
            box-shadow: 0 0 40px rgba(122, 24, 48, 0.6);
        }

        .btn-access:hover span {
            border-left-color: var(--crema);
        }

        /* Contenedor de imagen con efectos */
        .image-container {
            position: relative;
            display: inline-block;
        }

        .image-wrapper {
            position: relative;
            display: inline-block;
            padding: 15px;
            background: linear-gradient(135deg, var(--verde), var(--vino));
            border-radius: 2.5rem;
            box-shadow: 0 20px 40px rgba(30, 31, 35, 0.3);
            transition: all 0.5s ease;
            animation: slideUpFade 1s ease-out forwards;
        }

        .image-wrapper::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            background: linear-gradient(135deg, var(--vino), var(--verde));
            border-radius: 2.8rem;
            z-index: -1;
            opacity: 0.5;
            filter: blur(20px);
            transition: all 0.5s ease;
        }

        .image-wrapper:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(30, 31, 35, 0.4);
        }

        .image-wrapper:hover::before {
            opacity: 0.8;
            filter: blur(30px);
        }

        .img-fluid {
            border-radius: 2rem !important;
            transition: all 0.5s ease;
            position: relative;
            z-index: 1;
        }

        /* Efectos decorativos alrededor de la imagen */
        .floating-decoration {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            pointer-events: none;
        }

        .decoration-1 {
            width: 60px;
            height: 60px;
            top: -20px;
            right: -20px;
            animation: floatSlow 4s ease-in-out infinite;
            background: rgba(31, 91, 79, 0.3);
        }

        .decoration-2 {
            width: 40px;
            height: 40px;
            bottom: 30px;
            left: -15px;
            animation: floatSlow 5s ease-in-out infinite reverse;
            background: rgba(122, 24, 48, 0.3);
        }

        .decoration-3 {
            width: 50px;
            height: 50px;
            top: 40%;
            right: -25px;
            animation: floatSlow 6s ease-in-out infinite;
            background: rgba(31, 91, 79, 0.2);
        }

        /* ============================================
           FOOTER ELEGANTE (Colores sólidos)
        ============================================ */
        .footer {
            background: #11342a;
            color: var(--crema);
            text-align: center;
            padding: 3rem 1rem 3.5rem;
            margin-top: 5rem;
            border-top: 3px solid #1f5b4f;
            position: relative;
        }

        .footer p {
            margin: 0 0 12px 0;
            font-size: 0.95rem;
            opacity: 0.95;
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        .footer .fa-university {
            color: var(--verde-light) !important;
            font-size: 1.2rem;
            animation: floatSlow 3s ease-in-out infinite;
        }

        .footer-legal-links {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .legal-link-btn {
            background: none;
            border: none;
            color: #72dfca;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            padding: 4px 6px;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 0.2s ease;
        }

        .legal-link-btn:hover {
            color: #a7f3d0;
        }

        .legal-sep {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
        }

        /* Modal Legal */
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
            text-align: left;
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
        .legal-modal-body h4 {
            color: #164236;
            margin: 18px 0 6px 0;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .legal-modal-footer {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
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

        /* Cookie Banner */
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
            text-align: left;
            animation: slideUp 0.3s ease;
        }
        .cookie-banner-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: #164236;
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

        /* ============================================
           ACCESIBILIDAD
        ============================================ */
        *:focus-visible {
            outline: 3px solid var(--verde);
            outline-offset: 2px;
            border-radius: 4px;
        }

        .skip-to-content {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--verde);
            color: white;
            padding: 8px;
            z-index: 1001;
            text-decoration: none;
            border-radius: 0 0 4px 0;
        }

        .skip-to-content:focus {
            top: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.5rem 1rem;
            }
            
            .navbar-brand img {
                height: 50px;
            }
            
            .display-4 {
                font-size: 2.2rem;
            }
            
            .typed-text-output {
                font-size: 1.2rem;
            }
            
            .btn-access {
                width: 60px;
                height: 60px;
            }
            
            .btn-access span {
                border-left: 15px solid var(--white);
                border-top: 10px solid transparent;
                border-bottom: 10px solid transparent;
            }

            .image-wrapper {
                padding: 10px;
                border-radius: 2rem;
            }

            .decoration-1,
            .decoration-2,
            .decoration-3 {
                display: none;
            }

            .scroll-top-btn {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>

<body data-spy="scroll" data-target=".navbar" data-offset="51">

    <!-- Skip to content link para accesibilidad -->
    <a href="#main-content" class="skip-to-content">Saltar al contenido principal</a>

    <!-- NAVBAR ELEGANTE -->
    <nav class="navbar px-4" id="navbar">
        <a href="{{ url('/') }}" class="navbar-brand">
            <img src="{{ asset('img/logo.jpg') }}" alt="Logo ITINEWS">
        </a>
        
        <div class="d-flex align-items-center">
            <a href="{{ route('revistas.index') }}" class="nav-link ml-2">
                <i class="fas fa-book mr-1"></i>
                <span>Revistas</span>
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="nav-link ml-3">
                    <i class="fas fa-tachometer-alt mr-1"></i>
                    <span>Dashboard</span>
                </a>
                
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <a href="{{ route('logout') }}" class="nav-link ml-3" 
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </form>
            @endauth
        </div>
    </nav>

    <!-- HERO SECTION REDISEÑADA -->
    <div class="container-fluid d-flex align-items-center mb-5 py-5" id="home" style="min-height: 100vh;">
        <div class="container" id="main-content">
            {{-- BARRA DE BÚSQUEDA --}}
            <div class="search-bar-container">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchRevistasHome" placeholder="Buscar revistas por título, año o descripción...">
                    <span id="resultados-busqueda-home" class="resultados-busqueda"></span>
                </div>
            </div>

            <div class="row align-items-center justify-content-between">
                
                <!-- Columna de texto -->
                <div class="col-lg-6 text-center text-lg-left order-2 order-lg-1 mt-5 mt-lg-0">
                    <div class="animate-fade-left">
                        <span class="badge-custom mb-4 text-uppercase">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            Ingeniería en TI
                        </span>
                    </div>
                    
                    <h1 class="display-4 font-weight-bold mb-4 animate-fade-left delay-1">
                        Explora el conocimiento en <span>ITINEWS</span>
                    </h1>
                    
                    <h4 class="font-weight-light mb-5 animate-fade-left delay-2">
                        <span class="typed-text-output"></span>
                    </h4>
                    <div class="typed-text d-none">Plataforma Científica, Innovación Tecnológica, Portal Académico</div>
                    
                    <div class="d-flex align-items-center justify-content-center justify-content-lg-start pt-2 animate-fade-left delay-3">
                        <div class="access-button-wrapper">
                            <a href="{{ route('revistas.index') }}" class="btn-access" aria-label="Acceder al Portal">
                                <span></span>
                            </a>
                        </div>
                        <h5 class="font-weight-bold m-0 ml-4" style="color: var(--verde);">Acceder al Portal</h5>
                    </div>
                </div>

                <!-- Columna de imagen con efectos especiales -->
                <div class="col-lg-5 order-1 order-lg-2 text-center animate-fade-right delay-2">
                    @php
                        $fondosInicio = \App\Support\FondoActual::paraUbicacion('inicio');
                        $fondosJson = json_encode($fondosInicio, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    @endphp
                    <div class="image-container">
                        <div class="image-wrapper">
                            <div class="floating-decoration decoration-1"></div>
                            <div class="floating-decoration decoration-2"></div>
                            <div class="floating-decoration decoration-3"></div>
                            <div class="carrusel-fondos">
                                <img class="img-fluid animate-float carrusel-img"
                                    src="{{ $fondosInicio[0] ?? asset('img/inicio.jpg') }}"
                                    alt="Recurso ITINEWS"
                                    data-carrusel-img
                                    data-srcs="{{ $fondosJson }}"
                                    data-intervalo="5000"
                                    style="border-radius: 2rem !important; max-width: 100%;">
                                <div class="carrusel-dots" data-carrusel-dots aria-hidden="true"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <i class="fas fa-university mr-2"></i>
                    <p class="d-inline-block">&copy; {{ date('Y') }} Portal ITINEWS - Universidad Politécnica del Valle de Toluca</p>
                    <div class="footer-legal-links">
                        <button type="button" class="legal-link-btn" onclick="abrirModalLegal('terminos')">Términos y Condiciones</button>
                        <span class="legal-sep">&bull;</span>
                        <button type="button" class="legal-link-btn" onclick="abrirModalLegal('privacidad')">Aviso de Privacidad</button>
                        <span class="legal-sep">&bull;</span>
                        <button type="button" class="legal-link-btn" onclick="abrirModalLegal('cookies')">Política de Cookies</button>
                        <span class="legal-sep">&bull;</span>
                        <button type="button" class="legal-link-btn" onclick="mostrarBannerCookies(true)">Preferencias de Privacidad</button>
                    </div>
                </div>
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
                <p>El Portal ITINEWS es un repositorio de libre acceso y <strong>no solicita ni almacena datos personales sensibles</strong> de los lectores generales. Las preferencias y métricas de lectura se conservan de forma local en su propio navegador.</p>

                <h4>3. Métricas Anónimas</h4>
                <p>Para fines de acreditación académica y optimización, el portal registra métricas agregadas anónimas (páginas vistas y tiempos) sin vincularlas a su identidad personal.</p>

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

    <!-- BANNER DE COOKIES FUNCIONALES -->
    <aside id="bannerCookies" class="cookie-banner" role="dialog" aria-labelledby="cookieBannerTitle" aria-describedby="cookieBannerDesc">
        <div class="cookie-banner-header">
            <i class="fas fa-cookie-bite fa-lg"></i>
            <h4 id="cookieBannerTitle">Privacidad y Almacenamiento Local</h4>
        </div>
        <p id="cookieBannerDesc" class="cookie-banner-text">
            Utilizamos almacenamiento local funcional para recordar tu tema (claro/oscuro), tus revistas favoritas y calcular tus métricas personales de lectura sin rastreo comercial.
        </p>
        <div class="cookie-banner-actions">
            <button type="button" class="btn-cookie-accept" onclick="gestionarCookies('todas')">
                <i class="fas fa-check"></i> Aceptar Funcionales
            </button>
            <button type="button" class="btn-cookie-essential" onclick="gestionarCookies('esenciales')">
                Solo Esenciales
            </button>
            <button type="button" class="btn-cookie-essential" onclick="abrirModalLegal('cookies')" style="border: 1px solid #cbd5e1;">
                <i class="fas fa-info-circle"></i> Detalles
            </button>
        </div>
    </aside>

    {{-- BOTÓN VOLVER ARRIBA --}}
    <button id="btnScrollTop" class="scroll-top-btn" title="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('lib/typed/typed.min.js') }}"></script>
    
    <script src="{{ asset('js/modules/navbar.js') }}"></script>
    <script src="{{ asset('js/modules/scrolling.js') }}"></script>
    <script src="{{ asset('js/modules/typed.js') }}"></script>
    <script src="{{ asset('js/modules/gallery.js') }}"></script>
    <script src="{{ asset('js/modules/video-modal.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/carrusel-fondos.js') }}"></script>

    <script>
        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        
        if (navbar) {
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        }

        // Smooth scroll para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '' && href !== '#main-content') {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Efecto parallax suave para elementos decorativos
        document.addEventListener('mousemove', function(e) {
            const decorations = document.querySelectorAll('.floating-decoration');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            decorations.forEach((decoration, index) => {
                const speed = (index + 1) * 20;
                const x = (mouseX - 0.5) * speed;
                const y = (mouseY - 0.5) * speed;
                decoration.style.transform = `translate(${x}px, ${y}px)`;
            });
        });

        // Botón volver arriba
        const btnScrollTop = document.getElementById('btnScrollTop');
        if (btnScrollTop) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    btnScrollTop.classList.add('visible');
                } else {
                    btnScrollTop.classList.remove('visible');
                }
            });
            btnScrollTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Búsqueda en página de inicio - redirige a revistas con el término
        const searchHome = document.getElementById('searchRevistasHome');
        if (searchHome) {
            searchHome.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && this.value.trim()) {
                    window.location.href = "{{ route('revistas.index') }}?search=" + encodeURIComponent(this.value.trim());
                }
            });
        }

        // Modales legales y banner de cookies
        function abrirModalLegal(tipo) {
            const idMap = {
                'terminos': 'modalLegalTerminos',
                'privacidad': 'modalLegalPrivacidad',
                'cookies': 'modalLegalCookies'
            };
            const modal = document.getElementById(idMap[tipo]);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function cerrarModalLegal(tipo) {
            const idMap = {
                'terminos': 'modalLegalTerminos',
                'privacidad': 'modalLegalPrivacidad',
                'cookies': 'modalLegalCookies'
            };
            const modal = document.getElementById(idMap[tipo]);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
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
                }
            });
        });
    </script>
</body>
</html>