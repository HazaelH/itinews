<section>
    <header class="panel-form-head">
        <h2><i class="fas fa-id-card" aria-hidden="true"></i> Información del perfil</h2>
        <p>Actualiza tu nombre y tu dirección de correo electrónico de la cuenta.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="panel-form">
        @csrf
        @method('patch')

        <div class="panel-form-grid">
            <div class="pf-field full">
                <label class="pf-label" for="name">
                    <i class="fas fa-user" aria-hidden="true"></i> Nombre <span class="req">*</span>
                </label>
                <input id="name" name="name" type="text"
                       class="pf-input @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}"
                       required autofocus autocomplete="name">
                @error('name')
                    <span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="pf-field full">
                <label class="pf-label" for="email">
                    <i class="fas fa-envelope" aria-hidden="true"></i> Correo electrónico
                </label>
                <input id="email" name="email" type="email"
                       class="pf-input @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" readonly>
                <span class="pf-help">El correo electrónico no puede modificarse desde aquí.</span>
                @error('email')
                    <span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="info-card vino" style="margin-top:10px;">
                        <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong>Correo sin verificar</strong>
                            <p>
                                Tu correo aún no está verificado.
                                <button form="send-verification" style="background:none;border:none;padding:0;color:inherit;text-decoration:underline;cursor:pointer;font:inherit;">
                                    Reenviar el correo de verificación.
                                </button>
                            </p>
                        </div>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <p class="pf-help" style="color:var(--itin-verde); font-weight:600; margin-top:8px;">
                            Se envió un nuevo enlace de verificación a tu correo.
                        </p>
                    @endif
                @endif
            </div>
        </div>

        <div class="panel-form-pie">
            <button type="submit" class="btn-panel">
                <i class="fas fa-floppy-disk" aria-hidden="true"></i> Guardar cambios
            </button>

            @if (session('status') === 'profile-updated')
                <span class="pf-saved"
                      x-data="{ show: true }"
                      x-show="show"
                      x-transition
                      x-init="setTimeout(() => show = false, 2500)">
                    <i class="fas fa-circle-check" aria-hidden="true"></i> Guardado
                </span>
            @endif
        </div>
    </form>
</section>
