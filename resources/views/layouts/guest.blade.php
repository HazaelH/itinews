<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ITINEWS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js', 'resources/js/navbar.js'])

        <!-- Estilos compartidos de autenticación -->
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

        @stack('styles')
    </head>
    <body class="login-page-wrapper">
        <nav class="login-navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="/">
                        <img src="{{ asset('img/logo.jpg') }}" alt="ITINEWS" class="nav-logo-img">
                        <span>Portal ITINEWS</span>
                    </a>
                </div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a href="/revistas" class="nav-link">Ver Revistas</a>
                    </li>
                </ul>
                <div class="nav-toggle" id="navToggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>

        <div class="login-bg"></div>
        
        <div class="box {{ $attributes->get('box-class') }}">
            <div class="formulario {{ $attributes->get('formulario-class') }}">
                @unless($attributes->has('sin-logo'))
                    <a href="/" class="mb-6 logo-form">
                        <img src="{{ asset('img/logo.jpg') }}" alt="Logo" class="w-40 h-30">
                    </a>
                @endunless
                {{ $slot }}
            </div>
        </div>


        <footer class="login-footer">
            <div class="auth-footer-inner">
                <div class="auth-footer-marca">
                    <img src="{{ asset('img/logo.jpg') }}" alt="ITINEWS">
                    <div>
                        <strong>Portal ITINEWS</strong>
                        <small>Universidad Politécnica del Valle de Toluca</small>
                    </div>
                </div>
                <div class="auth-footer-meta">
                    <span>&copy; {{ date('Y') }} ITINEWS</span>
                    <span class="sep">•</span>
                    <a href="{{ url('/') }}">Inicio</a>
                    <a href="{{ url('/revistas') }}">Revistas</a>
                    <a href="{{ route('login') }}">Acceso</a>
                </div>
            </div>
        </footer>
        <script src="{{ asset('js/auth-password.js') }}" defer></script>
        @stack('scripts')
    </body>
</html>