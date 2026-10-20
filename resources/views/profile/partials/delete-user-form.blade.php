@if(Auth::user()->role === 'admin')
<div class="panel-form-card peligro-card" x-data="{ confirmar: false }">
    <header class="panel-form-head">
        <h2><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Eliminar cuenta</h2>
        <p>Una vez eliminada, todos tus recursos y datos se borrarán de forma permanente e irreversible.</p>
    </header>

    <div class="danger-zone" x-show="!confirmar">
        <div class="txt">
            <strong>Zona de peligro</strong>
            <small>Esta acción no se puede deshacer.</small>
        </div>
        <button type="button" class="btn-panel vino" x-on:click.prevent="confirmar = true">
            <i class="fas fa-trash-can" aria-hidden="true"></i> Eliminar cuenta
        </button>
    </div>

    <div x-show="confirmar" x-transition style="margin-top:8px;">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <h3 style="margin:0 0 4px; font-size:1rem; color:#c0392b;">
                ¿Seguro que deseas eliminar tu cuenta?
            </h3>
            <p class="pf-help" style="margin:0 0 16px;">
                Introduce tu contraseña para confirmar que deseas eliminar tu cuenta de forma permanente.
            </p>

            <div class="pf-field" style="max-width:360px;">
                <label class="pf-label" for="password_delete">Contraseña</label>
                <input id="password_delete" name="password" type="password" class="pf-input" placeholder="Contraseña" autocomplete="current-password">
                @error('password', 'userDeletion')
                    <span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="panel-form-pie" style="justify-content:flex-end;">
                <button type="button" class="btn-panel ghost" x-on:click.prevent="confirmar = false">
                    Cancelar
                </button>
                <button type="submit" class="btn-panel vino">
                    <i class="fas fa-trash-can" aria-hidden="true"></i> Eliminar cuenta
                </button>
            </div>
        </form>
    </div>
</div>
@endif
