<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    <div class="panel-page">
        <div class="panel-wrap">

            @if(session('success'))
                <div class="panel-alerta ok"><i class="fas fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="panel-alerta err"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ session('error') }}</div>
            @endif

            <header class="panel-head">
                <div>
                    <h1>Gestión de Usuarios</h1>
                    <p>Administra las cuentas con acceso al panel de ITINEWS.</p>
                </div>

                <div class="panel-actions">
                    <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn-panel">
                        <i class="fas fa-user-plus" aria-hidden="true"></i> Nuevo Usuario
                    </a>
                </div>
            </header>

            @if($users->count() > 0)
                <div class="panel-toolbar">
                    <div class="panel-buscador">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="text" id="usrBuscar" placeholder="Buscar por nombre o correo…"
                               autocomplete="off" aria-label="Buscar usuarios">
                    </div>

                    <div class="panel-selecto">
                        <i class="fas fa-user-shield" aria-hidden="true"></i>
                        <select id="usrRol" aria-label="Filtrar por rol">
                            <option value="">Todos los roles</option>
                            <option value="admin">Administradores</option>
                            <option value="editor">Editores</option>
                        </select>
                    </div>

                    <div class="panel-selecto">
                        <i class="fas fa-toggle-on" aria-hidden="true"></i>
                        <select id="usrEstado" aria-label="Filtrar por estado">
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>

                    <span class="panel-contador" id="usrResultados" hidden></span>
                    <span class="panel-contador">{{ $users->total() }} {{ $users->total() === 1 ? 'usuario' : 'usuarios' }}</span>
                </div>

                <div class="panel-tabla-card">
                    <div class="panel-tabla-scroll">
                        <table class="panel-tabla">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="usrTablaBody">
                                @foreach($users as $user)
                                    <tr data-user-item
                                        data-nombre="{{ \Illuminate\Support\Str::lower($user->name . ' ' . $user->email) }}"
                                        data-rol="{{ $user->role }}"
                                        data-activo="{{ $user->estaActivo() ? '1' : '0' }}">
                                        <td>
                                            <div class="adm-usuario">
                                                <span class="adm-avatar {{ $user->isAdmin() ? 'vino' : '' }}">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                                <div class="datos">
                                                    <strong>
                                                        {{ $user->name }}
                                                        @if($user->id === auth()->id())
                                                            <span class="adm-badge yo"><i class="fas fa-user" aria-hidden="true"></i> Tú</span>
                                                        @endif
                                                    </strong>
                                                    <small>{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="adm-badge {{ $user->isAdmin() ? 'admin' : 'editor' }}">
                                                <i class="fas {{ $user->isAdmin() ? 'fa-user-shield' : 'fa-pen-nib' }}" aria-hidden="true"></i>
                                                {{ $user->isAdmin() ? 'Administrador' : 'Editor' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if($user->estaActivo())
                                                <span class="adm-badge on"><i class="fas fa-circle-check" aria-hidden="true"></i> Activo</span>
                                            @else
                                                <span class="adm-badge off"><i class="fas fa-ban" aria-hidden="true"></i> Inactivo</span>
                                            @endif
                                        </td>

                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>

                                        <td>
                                            <div class="accion-grupo">
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                   class="accion-ico" title="Editar usuario" aria-label="Editar usuario">
                                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                                </a>

                                                @if($user->id !== auth()->id())
                                                    <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                                class="accion-ico {{ $user->estaActivo() ? 'ambar' : '' }}"
                                                                title="{{ $user->estaActivo() ? 'Desactivar usuario' : 'Activar usuario' }}"
                                                                aria-label="{{ $user->estaActivo() ? 'Desactivar usuario' : 'Activar usuario' }}">
                                                            <i class="fas {{ $user->estaActivo() ? 'fa-user-slash' : 'fa-user-check' }}" aria-hidden="true"></i>
                                                        </button>
                                                    </form>

                                                    @if($user->isAdmin())
                                                        <form action="{{ route('admin.users.demote', $user) }}" method="POST"
                                                              onsubmit="return confirm('¿Degradar a editor a {{ $user->name }}?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="accion-ico" title="Degradar a editor" aria-label="Degradar a editor">
                                                                <i class="fas fa-arrow-down" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.users.promote', $user) }}" method="POST"
                                                              onsubmit="return confirm('¿Promover a administrador a {{ $user->name }}?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="accion-ico vino" title="Promover a administrador" aria-label="Promover a administrador">
                                                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                          onsubmit="return confirm('¿Eliminar permanentemente a {{ $user->name }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="accion-ico rojo" title="Eliminar usuario" aria-label="Eliminar usuario">
                                                            <i class="far fa-trash-can" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div id="usrSinResultados" class="panel-empty" hidden>
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <h4>Sin coincidencias</h4>
                        <p>No hay usuarios que coincidan con la búsqueda o los filtros.</p>
                    </div>

                    <div style="padding: 14px 18px 18px;">{{ $users->links() }}</div>
                </div>
            @else
                <div class="panel-tabla-card">
                    <div class="panel-empty">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        <h4>No hay usuarios</h4>
                        <p>Comienza creando el primer usuario del sistema.</p>
                        <a href="{{ route('admin.users.create') }}" class="btn-panel">
                            <i class="fas fa-user-plus" aria-hidden="true"></i> Nuevo Usuario
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/dashboard-usuarios.js') }}" defer></script>
    @endpush
</x-app-layout>
