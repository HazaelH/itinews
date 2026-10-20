<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ITINEWS') }} - Revistas</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,900" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
        </div>
    </nav>

    <div class="sidebar">
    </div>

    <main class="main-content">
        {{ $slot }}
    </main>

    <footer class="footer">
    </footer>

    <div id="flipbook-container">
    </div>

    <div id="preview-container">
    </div>
</body>
</html>