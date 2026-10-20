<section>
    <header class="panel-form-head">
        <h2><i class="fas fa-lock" aria-hidden="true"></i> Actualizar contraseña</h2>
        <p>Asegúrate de usar una contraseña larga y aleatoria para mantener tu cuenta segura.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="panel-form" data-pw-form>
        @csrf
        @method('put')

        <div class="panel-form-grid">
            <div class="pf-field full">
                <label class="pf-label" for="update_password_current_password">
                    <i class="fas fa-key" aria-hidden="true"></i> Contraseña actual <span class="req">*</span>
                </label>
                <div class="pf-input-wrap">
                    <input id="update_password_current_password" name="current_password" type="password"
                           class="pf-input @error('current_password', 'updatePassword') is-invalid @enderror"
                           autocomplete="current-password" data-pw>
                    <button type="button" class="pw-toggle" data-pw-toggle aria-label="Mostrar contraseña">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="pf-field">
                <label class="pf-label" for="update_password_password">
                    <i class="fas fa-lock" aria-hidden="true"></i> Nueva contraseña <span class="req">*</span>
                </label>
                <div class="pf-input-wrap">
                    <input id="update_password_password" name="password" type="password"
                           class="pf-input @error('password', 'updatePassword') is-invalid @enderror"
                           autocomplete="new-password" data-pw data-pw-strength>
                    <button type="button" class="pw-toggle" data-pw-toggle aria-label="Mostrar contraseña">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="pw-fuerza" data-pw-bars aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                </div>
                <span class="pf-help" data-pw-help>Mínimo 8 caracteres.</span>
                @error('password', 'updatePassword')
                    <span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="pf-field">
                <label class="pf-label" for="update_password_password_confirmation">
                    <i class="fas fa-lock" aria-hidden="true"></i> Confirmar contraseña <span class="req">*</span>
                </label>
                <div class="pf-input-wrap">
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                           class="pf-input" autocomplete="new-password" data-pw>
                    <button type="button" class="pw-toggle" data-pw-toggle aria-label="Mostrar contraseña">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="panel-form-pie">
            <button type="submit" class="btn-panel">
                <i class="fas fa-shield-halved" aria-hidden="true"></i> Actualizar contraseña
            </button>

            @if (session('status') === 'password-updated')
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
