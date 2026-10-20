<x-guest-layout>
    <h1>Confirmar contraseña</h1>

    <p class="auth-intro">
        {{ __('Esta es un área segura de la aplicación. Por favor, confirma tu contraseña antes de continuar.') }}
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="inputBox">
            <input id="password" type="password" name="password" required autocomplete="current-password" />
            <span>Contraseña</span>
            <i></i>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-shield-halved"></i>
            {{ __('Confirmar') }}
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ url('/dashboard') }}">
            <i class="fas fa-arrow-left"></i> {{ __('Volver al panel') }}
        </a>
    </div>
</x-guest-layout>
