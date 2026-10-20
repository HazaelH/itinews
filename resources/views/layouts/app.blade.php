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
        <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}?v={{ file_exists(public_path('css/panel-admin.css')) ? filemtime(public_path('css/panel-admin.css')) : time() }}">
    </head>

    <body class="font-sans antialiased admin-layout-body">
        <div class="admin-layout-wrapper">
            <nav class="login-navbar">
                <div class="nav-container">
                    
                    <div class="nav-logo">
                        <a href="/dashboard">
                            <img src="{{ asset('img/logo.jpg') }}" alt="ITINEWS" class="nav-logo-img">
                            <span>Portal ITINEWS</span>
                        </a>
                    </div>

                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                        </li>
                        
                        @auth
                            @if(auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a href="{{ route('admin.metricas.index') }}" class="nav-link {{ request()->routeIs('admin.metricas.*') ? 'active' : '' }}">Métricas</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.revistas.crear') }}" class="nav-link {{ request()->routeIs('admin.revistas.crear') ? 'active' : '' }}">Subir Revista</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Gestionar Usuarios</a>
                                </li>
                            @endif
                        @endauth

                        <li class="nav-item">
                            <a href="{{ route('revistas.index') }}" class="nav-link">Ver Portal</a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                                Editar Perfil
                            </a>
                        </li>

                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <a href="{{ route('logout') }}" class="nav-link"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Cerrar Sesión
                                </a>
                            </form>
                        </li>
                    </ul>
                    <div class="nav-toggle" id="navToggle">
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                    </div>
                </div>
            </nav>

            <main class="admin-layout-main">
                {{ $slot }}
            </main>

        </div>

        @stack('scripts')
    </body>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</html>