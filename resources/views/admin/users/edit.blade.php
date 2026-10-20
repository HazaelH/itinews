<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    <div class="panel-page">
        <div class="panel-wrap">

            @if(session('error'))
                <div class="panel-alerta err"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ session('error') }}</div>
            @endif

            <header class="panel-head">
                <div>
                    <h1>Editar Usuario</h1>
                    <p>Modifica la información y los permisos de la cuenta.</p>
                </div>
                <div class="panel-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn-panel ghost">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a Usuarios
                    </a>
                </div>
            </header>

            <div class="panel-form-card">
                <div class="adm-usuario" style="margin-bottom:20px;">
                    <span class="adm-avatar {{ $user->isAdmin() ? 'vino' : '' }}">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <div class="datos">
                        <strong style="font-size:1.05rem;">{{ $user->name }}</strong>
                        <small>{{ $user->email }} · registrado el {{ $user->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>

                <form action="{{ route('admin.users.update', $user) }}" method="POST" id="usrForm">
                    @csrf
                    @method('PUT')

                    <div class="panel-form-grid">
                        <div class="pf-field">
                            <label for="name" class="pf-label"><i class="fas fa-user" aria-hidden="true"></i> Nombre <span class="req">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                   class="pf-input @error('name') is-invalid @enderror" required>
                            @error('name')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <label for="email" class="pf-label"><i class="fas fa-envelope" aria-hidden="true"></i> Correo electrónico</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                   class="pf-input" readonly>
                            <span class="pf-help">El correo no se puede modificar.</span>
                            @error('email')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <span class="pf-label"><i class="fas fa-user-shield" aria-hidden="true"></i> Rol</span>
                            <div class="segmented">
                                <label>
                                    <input type="radio" name="role" value="editor" {{ old('role', $user->role) === 'editor' ? 'checked' : '' }}>
                                    <span class="seg"><i class="fas fa-pen-nib" aria-hidden="true"></i> Editor</span>
                                </label>
                                <label>
                                    <input type="radio" name="role" value="admin" {{ old('role', $user->role) === 'admin' ? 'checked' : '' }}>
                                    <span class="seg"><i class="fas fa-user-shield" aria-hidden="true"></i> Administrador</span>
                                </label>
                            </div>
                            @if($user->id === auth()->id())
                                <span class="pf-help">No puedes cambiar tu propio rol.</span>
                            @endif
                            @error('role')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <span class="pf-label"><i class="fas fa-toggle-on" aria-hidden="true"></i> Estado de la cuenta</span>
                            @if($user->id === auth()->id())
                                <div class="info-card">
                                    <i class="fas fa-circle-info" aria-hidden="true"></i>
                                    <p>No puedes desactivar tu propia cuenta.</p>
                                </div>
                            @else
                                <label class="switch">
                                    <input type="hidden" name="activo" value="0">
                                    <input type="checkbox" name="activo" value="1" {{ old('activo', $user->estaActivo() ? '1' : '0') ? 'checked' : '' }}>
                                    <span class="track" aria-hidden="true"></span>
                                    <span style="margin-left:10px; font-size:.88rem; color:#6b6b6b;">Activo (puede iniciar sesión)</span>
                                </label>
                            @endif
                        </div>

                        <div class="pf-field">
                            <label for="password" class="pf-label"><i class="fas fa-lock" aria-hidden="true"></i> Nueva contraseña</label>
                            <div class="pf-input-wrap">
                                <input type="password" name="password" id="password"
                                       class="pf-input @error('password') is-invalid @enderror"
                                       placeholder="Dejar en blanco para no cambiar">
                                <button type="button" class="pw-toggle" data-pw="password" aria-label="Mostrar u ocultar contraseña">
                                    <i class="far fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="pw-fuerza" id="pwFuerza" data-pw-indicador="password" aria-hidden="true">
                                <span></span><span></span><span></span><span></span>
                            </div>
                            @error('password')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <label for="password_confirmation" class="pf-label"><i class="fas fa-lock-open" aria-hidden="true"></i> Confirmar nueva contraseña</label>
                            <div class="pf-input-wrap">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="pf-input">
                                <button type="button" class="pw-toggle" data-pw="password_confirmation" aria-label="Mostrar u ocultar contraseña">
                                    <i class="far fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="panel-form-pie">
                        <a href="{{ route('admin.users.index') }}" class="btn-panel ghost">
                            <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-panel">
                            <i class="fas fa-floppy-disk" aria-hidden="true"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

            @if($user->id !== auth()->id())
                <div class="danger-zone" style="margin-top:20px;">
                    <div class="txt">
                        <strong><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Eliminar usuario</strong>
                        <small>Esta acción es permanente y no se puede deshacer.</small>
                    </div>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar permanentemente a {{ $user->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-panel vino">
                            <i class="far fa-trash-can" aria-hidden="true"></i> Eliminar
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/dashboard-usuarios.js') }}" defer></script>
    @endpush
</x-app-layout>
