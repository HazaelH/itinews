<x-guest-layout>
    <h1>Recuperar contraseña</h1>

    <p class="auth-intro">
        {{ __('¿Olvidaste tu contraseña? No hay problema. Indícanos tu correo electrónico y te enviaremos un enlace para restablecerla.') }}
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="inputBox">
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            <span>Correo Electrónico</span>
            <i></i>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-paper-plane"></i>
            {{ __('Enviar enlace de restablecimiento') }}
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left"></i> {{ __('Volver a iniciar sesión') }}
        </a>
    </div>
</x-guest-layout>
