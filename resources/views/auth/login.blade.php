<x-guest-layout box-class="box--login" formulario-class="formulario--login" sin-logo>
    <link rel="stylesheet" href="{{ asset('css/auth-login.css') }}">

    @push('scripts')
        {{-- SCRIPT INTELIGENTE: RECARGA SOLO AL INTENTAR ENVIAR --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Guardar el momento exacto de carga
                const tiempoInicio = new Date().getTime();
                // 2. Definir límite de seguridad (20 minutos en milisegundos)
                const tiempoLimite = 20 * 60 * 1000;

                // Referencia al formulario
                const form = document.querySelector('form');
                const emailInput = document.getElementById('email');

                // 3. Restaurar email si venimos de una recarga forzada
                if (sessionStorage.getItem('reloaded_email')) {
                    if (emailInput) emailInput.value = sessionStorage.getItem('reloaded_email');
                    sessionStorage.removeItem('reloaded_email'); // Limpiar
                }

                if (form) {
                    form.addEventListener('submit', function(e) {
                        const tiempoActual = new Date().getTime();
                        const tiempoTranscurrido = tiempoActual - tiempoInicio;

                        // 4. Si ha pasado más tiempo del límite...
                        if (tiempoTranscurrido > tiempoLimite) {
                            e.preventDefault(); // ¡ALTO! No envíes el formulario con token viejo

                            // Guardamos el email para no molestar al usuario
                            if (emailInput) sessionStorage.setItem('reloaded_email', emailInput.value);

                            alert('Tu sesión de seguridad ha expirado. La página se actualizará automáticamente. Por favor, intenta ingresar de nuevo.');

                            // Recargamos para obtener token fresco
                            window.location.reload();
                        }
                        // Si no ha pasado el tiempo, deja que el formulario se envíe normal.
                    });
                }
            });
        </script>
        <script src="{{ asset('js/auth-login.js') }}" defer></script>
    @endpush

    <div class="auth-split">
        <!-- Columna izquierda: marca + carrusel de portadas -->
        <aside class="auth-carrusel">
            <a href="/" class="auth-marca">
                <img src="{{ asset('img/logo.jpg') }}" alt="ITINEWS">
                <span>Portal ITINEWS</span>
            </a>

            @if (!empty($portadas))
                <div class="auth-carrusel-stage" id="authCarrusel">
                    @foreach ($portadas as $i => $src)
                        <figure class="auth-slide{{ $i === 0 ? ' is-active' : '' }}">
                            <img src="{{ $src }}" alt="Portada de revista ITINEWS"
                                 loading="{{ $i < 2 ? 'eager' : 'lazy' }}">
                        </figure>
                    @endforeach
                </div>
                <div class="auth-carrusel-dots" id="authCarruselDots" aria-hidden="true"></div>
            @else
                <div class="auth-carrusel-stage auth-carrusel-stage--empty">
                    <i class="fas fa-book-open-reader"></i>
                </div>
            @endif

            <div class="auth-carrusel-info">
                <h2>Explora nuestras publicaciones</h2>
                <p>Revistas creadas por estudiantes de la Universidad Politécnica del Valle de Toluca.</p>
            </div>
        </aside>

        <!-- Columna derecha: formulario -->
        <div class="auth-panel">
            <a href="/" class="auth-marca auth-marca--movil">
                <img src="{{ asset('img/logo.jpg') }}" alt="ITINEWS">
                <span>Portal ITINEWS</span>
            </a>

            <h1 class="auth-titulo">Iniciar sesión</h1>
            <p class="auth-sub">Accede al panel de administración de ITINEWS.</p>

            {{-- MENSAJE DE ERROR POR INACTIVIDAD (Middleware) --}}
            @if (session('error'))
                <div class="auth-alerta">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <x-auth-session-status class="auth-ok" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <!-- Email Address -->
                <div class="inputBox">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                    <span>Correo Electrónico</span>
                    <i></i>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="inputBox">
                    <input id="password" type="password" name="password" required autocomplete="current-password" />
                    <span>Contraseña</span>
                    <i></i>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="auth-acciones">
                    <label for="remember_me" class="auth-recordar">
                        <input id="remember_me" type="checkbox" name="remember">
                        <span>{{ __('Recordarme') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="auth-enlace" href="{{ route('password.request') }}">
                            {{ __('¿Olvidaste tu contraseña?') }}
                        </a>
                    @endif
                </div>

                <button type="submit" class="auth-submit">
                    {{ __('Iniciar Sesión') }}
                </button>
            </form>

            <div class="auth-volver">
                <a href="/">
                    <i class="fas fa-arrow-left"></i> Volver al portal
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
