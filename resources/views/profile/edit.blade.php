<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    @php
        $iniciales = collect(explode(' ', trim($user->name)))
            ->filter()
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->take(2)
            ->implode('');
        $esAdmin = $user->role === 'admin';
        $activo = $user->activo ?? true;
        $tz = 'America/Mexico_City';
    @endphp

    <div class="panel-page">
        <div class="panel-wrap">

            @if (session('status') === 'profile-updated')
                <div class="panel-alerta ok"><i class="fas fa-circle-check" aria-hidden="true"></i> Datos de perfil actualizados correctamente.</div>
            @endif
            @if (session('status') === 'password-updated')
                <div class="panel-alerta ok"><i class="fas fa-shield-halved" aria-hidden="true"></i> Contraseña actualizada correctamente.</div>
            @endif
            @if (session('status') === 'verification-link-sent')
                <div class="panel-alerta ok"><i class="fas fa-envelope-circle-check" aria-hidden="true"></i> Se envió un nuevo enlace de verificación a tu correo.</div>
            @endif

            <header class="panel-head">
                <div>
                    <h1>Mi perfil</h1>
                    <p>Administra tus datos de cuenta y tu contraseña.</p>
                </div>
                <div class="panel-filtros">
                    <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al panel
                    </a>
                </div>
            </header>

            <div class="perfil-layout">
                {{-- Columna lateral: identidad y datos de cuenta --}}
                <aside class="perfil-side">
                    <div class="panel-form-card perfil-identidad">
                        <div class="perfil-avatar">{{ $iniciales }}</div>
                        <h2>{{ $user->name }}</h2>
                        <p>{{ $user->email }}</p>
                        <div class="perfil-badges">
                            <span class="adm-badge {{ $esAdmin ? 'admin' : 'editor' }}">
                                <i class="fas {{ $esAdmin ? 'fa-user-shield' : 'fa-pen-nib' }}" aria-hidden="true"></i>
                                {{ $esAdmin ? 'Administrador' : 'Editor' }}
                            </span>
                            <span class="adm-badge {{ $activo ? 'on' : 'off' }}">
                                <i class="fas {{ $activo ? 'fa-circle-check' : 'fa-circle-xmark' }}" aria-hidden="true"></i>
                                {{ $activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>

                    <div class="panel-form-card">
                        <h3 style="margin:0 0 14px; font-size:.95rem; color:var(--itin-verde); display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-circle-info" aria-hidden="true"></i> Datos de la cuenta
                        </h3>
                        <ul class="perfil-meta">
                            <li>
                                <i class="fas fa-hashtag" aria-hidden="true"></i>
                                <div><span>Identificador</span><br><strong>#{{ $user->id }}</strong></div>
                            </li>
                            <li>
                                <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                                <div><span>Miembro desde</span><br>
                                    <strong>{{ $user->created_at ? $user->created_at->timezone($tz)->format('d/m/Y') : '—' }}</strong>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-clock" aria-hidden="true"></i>
                                <div><span>Última actualización</span><br>
                                    <strong>{{ $user->updated_at ? $user->updated_at->timezone($tz)->format('d/m/Y H:i') : '—' }}</strong>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="info-card">
                        <i class="fas fa-lightbulb" aria-hidden="true"></i>
                        <div>
                            <strong>Consejo de seguridad</strong>
                            <p>Usa una contraseña larga y única. No la compartas con nadie y cámbiala si sospechas que fue expuesta.</p>
                        </div>
                    </div>
                </aside>

                {{-- Columna principal: formularios --}}
                <div class="perfil-main">
                    <div class="panel-form-card">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <div class="panel-form-card">
                        @include('profile.partials.update-password-form')
                    </div>

                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/perfil.js') }}" defer></script>
    @endpush
</x-app-layout>
