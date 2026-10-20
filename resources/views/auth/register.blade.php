<x-guest-layout>
    <h1>Crear cuenta</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="inputBox">
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
            <span>Nombre</span>
            <i></i>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="inputBox">
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
            <span>Correo Electrónico</span>
            <i></i>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="inputBox">
            <input id="password" type="password" name="password" required autocomplete="new-password" data-pw-strength />
            <span>Contraseña</span>
            <i></i>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="inputBox">
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <span>Confirmar Contraseña</span>
            <i></i>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-user-plus"></i>
            {{ __('Registrarse') }}
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">
            {{ __('¿Ya tienes una cuenta?') }} <strong>Inicia sesión</strong>
        </a>
    </div>
</x-guest-layout>
