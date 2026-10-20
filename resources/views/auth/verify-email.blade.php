<x-guest-layout>
    <h1>Verificar correo</h1>

    <p class="auth-intro">
        {{ __('¡Gracias por registrarte! Antes de comenzar, verifica tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar. Si no lo recibiste, con gusto te enviaremos otro.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="auth-status">
            <i class="fas fa-circle-check"></i>
            {{ __('Se ha enviado un nuevo enlace de verificación a tu correo.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf

        <button type="submit" class="auth-btn">
            <i class="fas fa-envelope-circle-check"></i>
            {{ __('Reenviar correo de verificación') }}
        </button>
    </form>

    <div class="auth-links">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="auth-link-btn">
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>
