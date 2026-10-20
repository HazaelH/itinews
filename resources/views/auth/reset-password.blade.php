<x-guest-layout>
    <h1>Restablecer contraseña</h1>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="inputBox">
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
            <span>Correo Electrónico</span>
            <i></i>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="inputBox">
            <input id="password" type="password" name="password" required autocomplete="new-password" data-pw-strength />
            <span>Nueva Contraseña</span>
            <i></i>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="inputBox">
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <span>Confirmar Nueva Contraseña</span>
            <i></i>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-key"></i>
            {{ __('Restablecer Contraseña') }}
        </button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left"></i> {{ __('Volver a iniciar sesión') }}
        </a>
    </div>
</x-guest-layout>
