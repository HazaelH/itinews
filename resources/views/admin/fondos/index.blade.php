<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    @php
        $origenLabel = ['archivo' => 'Imagen subida', 'portada' => 'Portada de revista'];
        $origenIcon = ['archivo' => 'fa-upload', 'portada' => 'fa-book-open'];
        $ubicLabel = ['inicio' => 'Inicio', 'revistas' => 'Revistas', 'ambos' => 'Ambas'];
        $ubicIcon = ['inicio' => 'fa-house', 'revistas' => 'fa-book', 'ambos' => 'fa-layer-group'];
    @endphp

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
                    <h1>Fondos del Portal</h1>
                    <p>Administra el carrusel de imágenes que se muestra en la página de inicio y en la sección de revistas.</p>
                </div>
                <div class="panel-actions">
                    <button type="button" class="btn-panel ghost" data-fondo-guia-btn>
                        <i class="fas fa-circle-question" aria-hidden="true"></i> ¿Cómo funciona?
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.fondos.create') }}" class="btn-panel">
                        <i class="fas fa-plus" aria-hidden="true"></i> Nuevo Fondo
                    </a>
                </div>
            </header>

            <div class="info-card" style="margin-bottom:20px;">
                <i class="fas fa-circle-info" aria-hidden="true"></i>
                <div>
                    <strong>Prioridad del carrusel de fondos</strong>
                    <p>Los fondos que crees y que estén vigentes hoy se mostrarán de forma exclusiva en la ubicación que elijas (Inicio, Revistas o Ambas). Las portadas de revistas activas se usan como respaldo cuando no hay fondos personalizados vigentes.</p>
                </div>
            </div>

            @if($fondos->isEmpty())
                <div class="panel-form-card">
                    <div class="fondo-vacio">
                        <i class="fas fa-images" aria-hidden="true"></i>
                        <h4>Sin fondos configurados</h4>
                        <p>Agrega una imagen o selecciona la portada de una revista para el carrusel del portal.</p>
                        <a href="{{ route('admin.fondos.create') }}" class="btn-panel">
                            <i class="fas fa-plus" aria-hidden="true"></i> Crear el primero
                        </a>
                    </div>
                </div>
            @else
                {{-- Barra de búsqueda, filtrado y ordenación --}}
                <div class="fondos-toolbar">
                    <div class="fondos-toolbar-top">
                        <div class="fondos-search-box">
                            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                            <input type="search" id="filtroFondoBusqueda" placeholder="Buscar fondo por título..." autocomplete="off">
                            <button type="button" class="fondos-search-clear" id="limpiarBusquedaBtn" title="Limpiar búsqueda" hidden>
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="fondos-counter" id="fondosCounter">
                            Mostrando <strong>{{ $fondos->count() }}</strong> de {{ $fondos->count() }} fondos
                        </div>
                    </div>

                    <div class="fondos-toolbar-filters">
                        <div class="fondos-filter-group">
                            <label for="filtroFondoUbicacion"><i class="fas fa-location-dot" aria-hidden="true"></i> Ubicación:</label>
                            <select id="filtroFondoUbicacion">
                                <option value="todas">Todas</option>
                                <option value="inicio">Inicio</option>
                                <option value="revistas">Revistas</option>
                                <option value="ambos">Ambas</option>
                            </select>
                        </div>

                        <div class="fondos-filter-group">
                            <label for="filtroFondoVigencia"><i class="fas fa-calendar-days" aria-hidden="true"></i> Vigencia:</label>
                            <select id="filtroFondoVigencia">
                                <option value="todas">Todas</option>
                                <option value="siempre">Siempre activo</option>
                                <option value="rango">Rango de fechas</option>
                                <option value="conmemorativa">Conmemorativa</option>
                            </select>
                        </div>

                        <div class="fondos-filter-group">
                            <label for="filtroFondoEstado"><i class="fas fa-toggle-on" aria-hidden="true"></i> Estado:</label>
                            <select id="filtroFondoEstado">
                                <option value="todos">Todos</option>
                                <option value="hoy">Activo hoy</option>
                                <option value="activos">Activos</option>
                                <option value="inactivos">Inactivos</option>
                            </select>
                        </div>

                        <div class="fondos-filter-group sort">
                            <label for="filtroFondoOrden"><i class="fas fa-arrow-down-short-wide" aria-hidden="true"></i> Ordenar por:</label>
                            <select id="filtroFondoOrden">
                                <option value="recientes">Más recientes</option>
                                <option value="antiguos">Más antiguos</option>
                                <option value="orden">Prioridad / Orden (1, 2, 3...)</option>
                                <option value="az">Título (A → Z)</option>
                                <option value="za">Título (Z → A)</option>
                            </select>
                        </div>

                        <button type="button" class="btn-panel ghost sm" id="btnRestablecerFiltros" title="Restablecer filtros">
                            <i class="fas fa-rotate-left" aria-hidden="true"></i> Restablecer
                        </button>
                    </div>
                </div>

                {{-- Contenedor de resultado vacío al filtrar --}}
                <div class="panel-form-card" id="fondosNoResultados" hidden style="margin-bottom: 20px;">
                    <div class="fondo-vacio">
                        <i class="fas fa-filter-circle-xmark" aria-hidden="true"></i>
                        <h4>Sin coincidencias</h4>
                        <p>No se encontró ningún fondo que coincida con los filtros y la búsqueda aplicados.</p>
                        <button type="button" class="btn-panel ghost" id="btnLimpiarFiltrosVacio">
                            <i class="fas fa-rotate-left" aria-hidden="true"></i> Restablecer filtros
                        </button>
                    </div>
                </div>

                <div class="fondos-grid">
                    @foreach($fondos as $fondo)
                        @php $vigenteHoy = $fondo->activo && $fondo->estaVigenteHoy(); @endphp
                        <article class="fondo-card {{ $fondo->activo ? '' : 'is-off' }}"
                                 data-fondo-item
                                 data-id="{{ $fondo->id }}"
                                 data-titulo="{{ mb_strtolower($fondo->titulo, 'UTF-8') }}"
                                 data-ubicacion="{{ $fondo->ubicacion }}"
                                 data-vigencia="{{ $fondo->tipo_vigencia }}"
                                 data-activo="{{ $fondo->activo ? '1' : '0' }}"
                                 data-hoy="{{ $vigenteHoy ? '1' : '0' }}"
                                 data-orden="{{ (int)$fondo->orden }}"
                                 data-fecha="{{ $fondo->created_at ? $fondo->created_at->timestamp : $fondo->id }}">
                            <div class="fondo-card-media">
                                @if($fondo->url())
                                    <img src="{{ $fondo->url() }}" alt="{{ $fondo->titulo }}" loading="lazy">
                                @else
                                    <span class="fondo-sin-img"><i class="fas fa-image" aria-hidden="true"></i></span>
                                @endif

                                <span class="fondo-estado {{ $fondo->activo ? 'on' : 'off' }}">
                                    <i class="fas {{ $fondo->activo ? 'fa-eye' : 'fa-eye-slash' }}" aria-hidden="true"></i>
                                    {{ $fondo->activo ? 'Activo' : 'Inactivo' }}
                                </span>

                                @if($vigenteHoy)
                                    <span class="fondo-hoy"><i class="fas fa-bolt" aria-hidden="true"></i> Activo hoy</span>
                                @endif
                            </div>

                            <div class="fondo-card-body">
                                <h3>{{ $fondo->titulo }}</h3>

                                <p class="fondo-tags">
                                    <span class="fondo-tag">
                                        <i class="fas {{ $origenIcon[$fondo->origen] ?? 'fa-image' }}" aria-hidden="true"></i>
                                        {{ $origenLabel[$fondo->origen] ?? $fondo->origen }}
                                    </span>
                                    <span class="fondo-tag">
                                        <i class="fas {{ $ubicIcon[$fondo->ubicacion] ?? 'fa-layer-group' }}" aria-hidden="true"></i>
                                        {{ $ubicLabel[$fondo->ubicacion] ?? $fondo->ubicacion }}
                                    </span>
                                    <span class="fondo-tag orden-tag" title="Orden en el carrusel">
                                        <i class="fas fa-arrow-down-1-9" aria-hidden="true"></i> Orden: {{ $fondo->orden }}
                                    </span>
                                </p>

                                <p class="fondo-vigencia">
                                    <i class="fas fa-calendar-day" aria-hidden="true"></i> {{ $fondo->vigenciaTexto() }}
                                </p>
                            </div>

                            <div class="fondo-card-actions">
                                <form action="{{ route('admin.fondos.toggle', $fondo) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-panel ghost" title="{{ $fondo->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas {{ $fondo->activo ? 'fa-eye-slash' : 'fa-eye' }}" aria-hidden="true"></i>
                                        {{ $fondo->activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>

                                <a href="{{ route('admin.fondos.edit', $fondo) }}" class="btn-panel ghost">
                                    <i class="fas fa-pen" aria-hidden="true"></i> Editar
                                </a>

                                <form action="{{ route('admin.fondos.destroy', $fondo) }}" method="POST"
                                      data-confirm="¿Eliminar permanentemente el fondo «{{ $fondo->titulo }}»?"
                                      data-confirm-titulo="Eliminar fondo"
                                      data-confirm-ok-txt="Eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-panel vino" title="Eliminar">
                                        <i class="far fa-trash-can" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @include('admin.fondos._modales')

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/admin-fondos.js') }}" defer></script>
    @endpush
</x-app-layout>
