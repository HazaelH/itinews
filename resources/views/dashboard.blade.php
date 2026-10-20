<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">
    <div class="dashboard-container">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="panel-alerta ok"><i class="fas fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="panel-alerta err"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="panel-alerta ok"><i class="fas fa-circle-info" aria-hidden="true"></i> {{ session('info') }}</div>
            @endif

            <div class="dashboard-welcome">
                <div class="flex justify-between items-center">
                    <div>
                        <h1>Bienvenido, {{ Auth::user()->name }}!</h1>
                        <p>Gestiona tu contenido desde el dashboard</p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.revistas.crear') }}" class="btn-panel">
                            <i class="fas fa-upload" aria-hidden="true"></i> Subir Nueva Revista
                        </a>

                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="btn-panel ghost">
                            <i class="fas fa-users" aria-hidden="true"></i> Gestionar Usuarios
                        </a>
                        <a href="{{ route('admin.fondos.index') }}" class="btn-panel ghost">
                            <i class="fas fa-images" aria-hidden="true"></i> Fondos del Portal
                        </a>
                        <a href="{{ route('admin.metricas.index') }}" class="btn-panel vino">
                            <i class="fas fa-chart-line" aria-hidden="true"></i> Métricas
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
            <section class="dashboard-admin-hub">
                <section class="panel-kpis" id="dashKpis"></section>

                <section class="panel-grid">
                    <div class="panel-card wide">
                        <div class="panel-card-head">
                            <h3>Visitas al portal por día</h3>
                            <select id="dashRango" aria-label="Rango de días">
                                <option value="7">Últimos 7 días</option>
                                <option value="30" selected>Últimos 30 días</option>
                                <option value="90">Últimos 90 días</option>
                                <option value="365">Último año</option>
                            </select>
                        </div>
                        <div class="panel-chart" id="dashVisitas"></div>
                    </div>

                    <div class="panel-card wide">
                        <h3>Horas pico de uso</h3>
                        <div class="panel-chart" id="dashHoras"></div>
                    </div>

                    <div class="panel-card span-2">
                        <h3>Revistas más abiertas</h3>
                        <div class="lista-mini" id="dashRevistas"></div>
                    </div>

                    <div class="panel-card">
                        <h3>Dispositivos</h3>
                        <div class="panel-chart" id="dashDispositivos"></div>
                    </div>

                    <div class="panel-card">
                        <h3>Resumen de usuarios</h3>
                        @if($userStats)
                        <div class="resumen-usuarios">
                            <div class="contadores">
                                <div class="c"><strong>{{ $userStats['total'] }}</strong><small>Total</small></div>
                                <div class="c"><strong>{{ $userStats['admins'] }}</strong><small>Admins</small></div>
                                <div class="c"><strong>{{ $userStats['editores'] }}</strong><small>Editores</small></div>
                            </div>
                            @foreach($userStats['recientes'] as $u)
                            <div class="fila">
                                <div class="avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                <div class="u-info">
                                    <strong>{{ $u->name }}</strong>
                                    <small>{{ $u->email }}</small>
                                </div>
                                <span class="etiqueta {{ $u->role === 'admin' ? 'admin' : '' }}">{{ $u->role }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="panel-card span-2">
                        <h3>Accesos rápidos</h3>
                        <div class="accesos-grid">
                            <a class="acceso-item" href="{{ route('admin.revistas.crear') }}">
                                <i class="fas fa-upload" aria-hidden="true"></i>
                                <span class="acceso-txt"><strong>Subir Revista</strong><small>Publicar un nuevo PDF</small></span>
                            </a>
                            <a class="acceso-item" href="{{ route('admin.metricas.index') }}">
                                <i class="fas fa-chart-line" aria-hidden="true"></i>
                                <span class="acceso-txt"><strong>Métricas</strong><small>Estadísticas del portal</small></span>
                            </a>
                            <a class="acceso-item" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users" aria-hidden="true"></i>
                                <span class="acceso-txt"><strong>Usuarios</strong><small>Gestionar cuentas</small></span>
                            </a>
                            <a class="acceso-item" href="{{ route('admin.fondos.index') }}">
                                <i class="fas fa-images" aria-hidden="true"></i>
                                <span class="acceso-txt"><strong>Fondos</strong><small>Imágenes del portal</small></span>
                            </a>
                            <a class="acceso-item" href="{{ route('revistas.index') }}">
                                <i class="fas fa-globe" aria-hidden="true"></i>
                                <span class="acceso-txt"><strong>Ver portal</strong><small>Revistas públicas</small></span>
                            </a>
                        </div>
                    </div>
                </section>
            </section>
            @endif

            <section class="rev-panel" id="revPanel">
                <header class="rev-panel-head">
                    <div class="rev-panel-title">
                        <span class="rev-panel-icon" aria-hidden="true"><i class="fas fa-book-open-reader"></i></span>
                        <div>
                            <h3>Tus Revistas</h3>
                            <p>
                                <span id="revConteo">{{ $revistas->total() }}</span>
                                {{ $revistas->total() === 1 ? 'revista' : 'revistas' }}
                                · <span class="rev-visibilidad activa"><i class="fas fa-eye" aria-hidden="true"></i> {{ $revistasActivas }} visibles</span>
                                · <span class="rev-visibilidad oculta"><i class="fas fa-eye-slash" aria-hidden="true"></i> {{ $revistasOcultas }} ocultas</span>
                                @if($revistas->hasPages())
                                    · página {{ $revistas->currentPage() }} de {{ $revistas->lastPage() }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="rev-tools">
                        <div class="rev-search">
                            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                            <input type="text" id="searchRevistas"
                                   placeholder="Buscar por título o descripción…"
                                   autocomplete="off"
                                   aria-label="Buscar revistas">
                            <button type="button" id="revLimpiar" class="rev-search-clear"
                                    title="Limpiar búsqueda" aria-label="Limpiar búsqueda" hidden>
                                <i class="fas fa-xmark" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="rev-filter">
                            <i class="fas fa-filter" aria-hidden="true"></i>
                            <select id="filterStatus" aria-label="Filtrar por estado">
                                <option value="">Todos los estados</option>
                                <option value="completado">Completadas</option>
                                <option value="procesando">En proceso</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="error">Con error</option>
                            </select>
                        </div>

                        <div class="rev-filter">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                            <select id="filterVisibilidad" aria-label="Filtrar por visibilidad">
                                <option value="">Visibilidad: todas</option>
                                <option value="1">Visibles</option>
                                <option value="0">Ocultas</option>
                            </select>
                        </div>

                        <span class="rev-resultados" id="revResultados" hidden></span>

                        <div class="rev-vista" role="group" aria-label="Cambiar vista">
                            <button type="button" class="rev-vista-btn is-active" data-vista="tabla"
                                    title="Vista de tabla" aria-pressed="true">
                                <i class="fas fa-table-list" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="rev-vista-btn" data-vista="tarjetas"
                                    title="Vista de tarjetas" aria-pressed="false">
                                <i class="fas fa-grip" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </header>

                @if($revistas->count() > 0)
                    <div class="rev-vista-tabla" id="revVistaTabla">
                        <div class="rev-tabla-scroll">
                            <table class="rev-tabla">
                                <thead>
                                    <tr>
                                        <th class="rev-th-orden" data-sort="titulo" aria-sort="none">
                                            <span>Revista</span> <i class="fas fa-sort" aria-hidden="true"></i>
                                        </th>
                                        <th class="rev-th-orden" data-sort="estado" aria-sort="none">
                                            <span>Estado</span> <i class="fas fa-sort" aria-hidden="true"></i>
                                        </th>
                                        <th class="rev-th-orden" data-sort="fecha" aria-sort="none">
                                            <span>Fecha</span> <i class="fas fa-sort" aria-hidden="true"></i>
                                        </th>
                                        <th class="rev-th-orden" data-sort="paginas" aria-sort="none">
                                            <span>Páginas</span> <i class="fas fa-sort" aria-hidden="true"></i>
                                        </th>
                                        <th class="rev-th-acciones">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="revistasTableBody">
                                    @foreach($revistas as $revista)
                                        @include('partials.revista-fila', ['revista' => $revista])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rev-tarjetas" id="revVistaTarjetas" hidden>
                        @foreach($revistas as $revista)
                            @include('partials.revista-tarjeta', ['revista' => $revista])
                        @endforeach
                    </div>

                    <div class="rev-sin-resultados" id="revSinResultados" hidden>
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <h4>Sin coincidencias</h4>
                        <p>No hay revistas que coincidan con tu búsqueda o filtro.</p>
                    </div>
                @else
                    <div class="rev-vacio">
                        <i class="fas fa-book" aria-hidden="true"></i>
                        <h4>No hay revistas todavía</h4>
                        <p>Comienza subiendo tu primera revista para verla aquí.</p>
                        <a href="{{ route('admin.revistas.crear') }}" class="rev-vacio-btn">
                            <i class="fas fa-upload" aria-hidden="true"></i> Subir revista
                        </a>
                    </div>
                @endif

                @if($revistas->hasPages())
                    <div class="rev-paginacion">{{ $revistas->links() }}</div>
                @endif
            </section>

        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/dashboard-revistas.js') }}" defer></script>
    @endpush

    @if(auth()->user()->isAdmin())
    @push('scripts')
    <script>
        window.ITIN_METRICAS_DATA_URL = "{{ route('admin.metricas.data') }}";
        window.ITIN_MINIATURA_URL = "{{ route('admin.media.miniatura') }}";
    </script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.1/dist/echarts.min.js"></script>
    <script src="{{ asset('js/graficas-itinews.js') }}"></script>
    <script src="{{ asset('js/dashboard-admin.js') }}"></script>
    @endpush
    @endif

</x-app-layout>
