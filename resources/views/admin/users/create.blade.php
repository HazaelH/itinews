<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    <div class="panel-page">
        <div class="panel-wrap">

            <header class="panel-head">
                <div>
                    <h1>Crear Nuevo Usuario</h1>
                    <p>Agrega una cuenta con acceso al panel de ITINEWS.</p>
                </div>
                <div class="panel-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn-panel ghost">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a Usuarios
                    </a>
                </div>
            </header>

            <div class="panel-form-card">
                <form action="{{ route('admin.users.store') }}" method="POST" id="usrForm">
                    @csrf

                    <div class="panel-form-grid">
                        <div class="pf-field">
                            <label for="name" class="pf-label"><i class="fas fa-user" aria-hidden="true"></i> Nombre <span class="req">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                   class="pf-input @error('name') is-invalid @enderror" required autofocus>
                            @error('name')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <label for="email" class="pf-label"><i class="fas fa-envelope" aria-hidden="true"></i> Correo electrónico <span class="req">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="pf-input @error('email') is-invalid @enderror" required>
                            @error('email')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <label for="password" class="pf-label"><i class="fas fa-lock" aria-hidden="true"></i> Contraseña <span class="req">*</span></label>
                            <div class="pf-input-wrap">
                                <input type="password" name="password" id="password"
                                       class="pf-input @error('password') is-invalid @enderror" required>
                                <button type="button" class="pw-toggle" data-pw="password" aria-label="Mostrar u ocultar contraseña">
                                    <i class="far fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="pw-fuerza" id="pwFuerza" data-pw-indicador="password" aria-hidden="true">
                                <span></span><span></span><span></span><span></span>
                            </div>
                            <span class="pf-help">Mínimo 8 caracteres. Combina mayúsculas, números y símbolos.</span>
                            @error('password')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <label for="password_confirmation" class="pf-label"><i class="fas fa-lock-open" aria-hidden="true"></i> Confirmar contraseña <span class="req">*</span></label>
                            <div class="pf-input-wrap">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="pf-input" required>
                                <button type="button" class="pw-toggle" data-pw="password_confirmation" aria-label="Mostrar u ocultar contraseña">
                                    <i class="far fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pf-field">
                            <span class="pf-label"><i class="fas fa-user-shield" aria-hidden="true"></i> Rol</span>
                            <div class="segmented">
                                <label>
                                    <input type="radio" name="role" value="editor" {{ old('role', 'editor') === 'editor' ? 'checked' : '' }}>
                                    <span class="seg"><i class="fas fa-pen-nib" aria-hidden="true"></i> Editor</span>
                                </label>
                                <label>
                                    <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }}>
                                    <span class="seg"><i class="fas fa-user-shield" aria-hidden="true"></i> Administrador</span>
                                </label>
                            </div>
                            <span class="pf-help">Los editores pueden subir y gestionar revistas; los administradores también gestionan usuarios y métricas.</span>
                            @error('role')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <span class="pf-label"><i class="fas fa-toggle-on" aria-hidden="true"></i> Estado de la cuenta</span>
                            <label class="switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1" {{ old('activo', '1') ? 'checked' : '' }}>
                                <span class="track" aria-hidden="true"></span>
                                <span style="margin-left:10px; font-size:.88rem; color:#6b6b6b;">Activo (puede iniciar sesión)</span>
                            </label>
                        </div>
                    </div>

                    <div class="panel-form-pie">
                        <a href="{{ route('admin.users.index') }}" class="btn-panel ghost">
                            <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-panel">
                            <i class="fas fa-user-plus" aria-hidden="true"></i> Crear Usuario
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/dashboard-usuarios.js') }}" defer></script>
    @endpush
</x-app-layout>
