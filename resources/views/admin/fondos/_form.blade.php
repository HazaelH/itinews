@php
    /** @var \App\Models\Fondo $fondo */
    $fondo = $fondo ?? new \App\Models\Fondo();
    $origen = old('origen', $fondo->origen ?? 'archivo');
    $ubicacion = old('ubicacion', $fondo->ubicacion ?? 'ambos');
    $vigencia = old('tipo_vigencia', $fondo->tipo_vigencia ?? 'siempre');
    $revistaSel = (int) old('revista_id', $fondo->revista_id);
@endphp

<div class="panel-form-grid">
    {{-- Título --}}
    <div class="pf-field full">
        <label for="titulo" class="pf-label"><i class="fas fa-heading" aria-hidden="true"></i> Título <span class="req">*</span></label>
        <input type="text" id="titulo" name="titulo"
               class="pf-input @error('titulo') is-invalid @enderror"
               value="{{ old('titulo', $fondo->titulo) }}" required autofocus>
        @error('titulo')<span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>@enderror
    </div>

    {{-- Origen de la imagen --}}
    <div class="pf-field">
        <span class="pf-label"><i class="fas fa-image" aria-hidden="true"></i> Origen de la imagen</span>
        <div class="segmented">
            <label>
                <input type="radio" name="origen" value="archivo" {{ $origen === 'archivo' ? 'checked' : '' }} data-fondo-origen>
                <span class="seg"><i class="fas fa-upload" aria-hidden="true"></i> Subir imagen</span>
            </label>
            <label>
                <input type="radio" name="origen" value="portada" {{ $origen === 'portada' ? 'checked' : '' }} data-fondo-origen>
                <span class="seg"><i class="fas fa-book-open" aria-hidden="true"></i> Portada de revista</span>
            </label>
        </div>
        <span class="pf-help" data-origen-hint></span>
    </div>

    {{-- Ubicación --}}
    <div class="pf-field">
        <span class="pf-label"><i class="fas fa-location-dot" aria-hidden="true"></i> ¿Dónde se muestra?</span>
        <div class="segmented">
            <label>
                <input type="radio" name="ubicacion" value="inicio" {{ $ubicacion === 'inicio' ? 'checked' : '' }} data-fondo-ubicacion>
                <span class="seg"><i class="fas fa-house" aria-hidden="true"></i> Inicio</span>
            </label>
            <label>
                <input type="radio" name="ubicacion" value="revistas" {{ $ubicacion === 'revistas' ? 'checked' : '' }} data-fondo-ubicacion>
                <span class="seg"><i class="fas fa-book" aria-hidden="true"></i> Revistas</span>
            </label>
            <label>
                <input type="radio" name="ubicacion" value="ambos" {{ $ubicacion === 'ambos' ? 'checked' : '' }} data-fondo-ubicacion>
                <span class="seg"><i class="fas fa-layer-group" aria-hidden="true"></i> Ambas</span>
            </label>
        </div>
        <span class="pf-help" data-ubicacion-hint></span>
    </div>

    {{-- Imagen (origen archivo) --}}
    <div class="pf-field full" data-when-origen="archivo">
        <label for="archivo" class="pf-label">
            <i class="fas fa-file-image" aria-hidden="true"></i> Imagen @unless($fondo->exists)<span class="req">*</span>@endunless
        </label>
        <input type="file" id="archivo" name="archivo" accept="image/jpeg,image/png,image/webp"
               class="pf-input @error('archivo') is-invalid @enderror" data-fondo-file>
        <span class="pf-help">JPG, PNG o WEBP · máx. 8 MB. Se recomienda al menos 1920 px de ancho.</span>
        @error('archivo')<span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>@enderror
        <div class="fondo-preview" data-fondo-preview>
            @if($fondo->exists && $fondo->origen === 'archivo' && $fondo->url())
                <img src="{{ $fondo->url() }}" alt="Vista previa del fondo">
            @endif
        </div>
    </div>

    {{-- Portada de revista (origen portada) --}}
    <div class="pf-field full" data-when-origen="portada">
        <span class="pf-label"><i class="fas fa-book-open" aria-hidden="true"></i> Elige una portada <span class="req">*</span></span>

        @if($revistas->isEmpty())
            <div class="portada-vacio">
                <i class="fas fa-book" aria-hidden="true"></i>
                <p>No hay revistas activas con portada todavía.</p>
                <a href="{{ route('admin.revistas.crear') }}" class="btn-panel ghost">
                    <i class="fas fa-upload" aria-hidden="true"></i> Subir una revista
                </a>
            </div>
        @else
            <div class="portada-picker" data-portada-picker>
                @foreach($revistas as $r)
                    <label class="portada-opcion">
                        <input type="radio" name="revista_id" value="{{ $r->id }}"
                               {{ $revistaSel === $r->id ? 'checked' : '' }}
                               data-url="{{ $r->urlPortada() }}">
                        <span class="portada-thumb">
                            <img src="{{ $r->urlPortada() }}" alt="Portada de {{ $r->titulo }}" loading="lazy">
                            <span class="portada-nombre">{{ $r->titulo }}</span>
                            <span class="portada-check"><i class="fas fa-check" aria-hidden="true"></i></span>
                        </span>
                    </label>
                @endforeach
            </div>
            <span class="pf-help">La portada se mostrará como fondo del portal.</span>
        @endif
        @error('revista_id')<span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>@enderror
    </div>

    {{-- Vigencia --}}
    <div class="pf-field full">
        <span class="pf-label"><i class="fas fa-calendar-days" aria-hidden="true"></i> Vigencia</span>
        <div class="segmented">
            <label>
                <input type="radio" name="tipo_vigencia" value="siempre" {{ $vigencia === 'siempre' ? 'checked' : '' }} data-fondo-vigencia>
                <span class="seg"><i class="fas fa-infinity" aria-hidden="true"></i> Siempre</span>
            </label>
            <label>
                <input type="radio" name="tipo_vigencia" value="rango" {{ $vigencia === 'rango' ? 'checked' : '' }} data-fondo-vigencia>
                <span class="seg"><i class="fas fa-calendar-week" aria-hidden="true"></i> Rango de fechas</span>
            </label>
            <label>
                <input type="radio" name="tipo_vigencia" value="conmemorativa" {{ $vigencia === 'conmemorativa' ? 'checked' : '' }} data-fondo-vigencia>
                <span class="seg"><i class="fas fa-star" aria-hidden="true"></i> Conmemorativa</span>
            </label>
        </div>
        <span class="pf-help" data-vigencia-hint></span>
        
        {{-- Presets: Conmemorativas --}}
        <div class="fondo-presets" data-preset-conmemorativa hidden>
            <div class="fondo-presets-header">
                <span class="fondo-presets-title"><i class="fas fa-star" aria-hidden="true"></i> Fechas conmemorativas</span>
                <button type="button" class="btn-preset-gestionar" data-abrir-preset-gestor="conmemorativa">
                    <i class="fas fa-pen-to-square" aria-hidden="true"></i> Gestionar / Agregar fechas
                </button>
            </div>
            <div class="fondo-preset-chips" id="chipsConmemorativa">
                @foreach($presetsConm ?? [] as $preset)
                    <button type="button" class="fondo-preset" data-preset-conm data-id="{{ $preset->id }}" data-dia="{{ $preset->dia }}" data-nombre="{{ $preset->nombre }}">
                        {{ $preset->nombre }}
                    </button>
                @endforeach
            </div>
            <span class="pf-help">Haz clic para llenar la fecha (se marca como recurrente cada año). O pulsa en "Gestionar / Agregar" para crear o editar fechas.</span>
        </div>

        {{-- Presets: Rangos (estaciones y eventos) --}}
        <div class="fondo-presets" data-preset-rango hidden>
            <div class="fondo-presets-header">
                <span class="fondo-presets-title"><i class="fas fa-calendar-week" aria-hidden="true"></i> Rangos predeterminados</span>
                <button type="button" class="btn-preset-gestionar" data-abrir-preset-gestor="rango">
                    <i class="fas fa-pen-to-square" aria-hidden="true"></i> Gestionar / Agregar rangos
                </button>
            </div>
            <div class="fondo-preset-chips" id="chipsRango">
                @foreach($presetsRango ?? [] as $preset)
                    <button type="button" class="fondo-preset" data-preset-rango data-id="{{ $preset->id }}" data-desde="{{ $preset->desde }}" data-hasta="{{ $preset->hasta }}" data-nombre="{{ $preset->nombre }}">
                        {{ $preset->nombre }}
                    </button>
                @endforeach
            </div>
            <span class="pf-help">Haz clic para llenar las fechas. Puedes usar "Gestionar / Agregar" para personalizar tus propios periodos o estaciones.</span>
        </div>
    </div>

    {{-- Fecha de inicio (rango) / Día conmemorativo --}}
    <div class="pf-field" data-when-vigencia="rango conmemorativa">
        <label for="fecha_inicio" class="pf-label">
            <i class="fas fa-calendar-day" aria-hidden="true"></i>
            <span data-fecha-inicio-label>Desde</span> <span class="req">*</span>
        </label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" class="pf-input @error('fecha_inicio') is-invalid @enderror"
               value="{{ old('fecha_inicio', optional($fondo->fecha_inicio)->format('Y-m-d')) }}" data-fondo-inicio>
        @error('fecha_inicio')<span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>@enderror
    </div>

    {{-- Fecha de fin (solo rango) --}}
    <div class="pf-field" data-when-vigencia="rango">
        <label for="fecha_fin" class="pf-label"><i class="fas fa-calendar-check" aria-hidden="true"></i> Hasta <span class="req">*</span></label>
        <input type="date" id="fecha_fin" name="fecha_fin" class="pf-input @error('fecha_fin') is-invalid @enderror"
               value="{{ old('fecha_fin', optional($fondo->fecha_fin)->format('Y-m-d')) }}" data-fondo-fin>
        @error('fecha_fin')<span class="pf-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</span>@enderror
    </div>

    {{-- Recurrencia anual --}}
    <div class="pf-field" data-when-vigencia="rango conmemorativa">
        <span class="pf-label"><i class="fas fa-repeat" aria-hidden="true"></i> ¿Se repite cada año?</span>
        <label class="switch">
            <input type="hidden" name="recurrente" value="0">
            <input type="checkbox" name="recurrente" value="1" {{ old('recurrente', $fondo->recurrente ?? true) ? 'checked' : '' }}>
            <span class="track" aria-hidden="true"></span>
            <span class="switch-txt">Sí, se repite cada año</span>
        </label>
    </div>

    {{-- Aviso interactivo si ya existen otros fondos en este periodo --}}
    <div class="pf-field full" id="avisoCoincidenciaFondo" hidden>
        <div class="fondo-coincidencia-card">
            <div class="coincidencia-icon">
                <i class="fas fa-layer-group" aria-hidden="true"></i>
            </div>
            <div class="coincidencia-info">
                <strong><i class="fas fa-circle-info" aria-hidden="true"></i> Ya existen otros fondos configurados para este periodo:</strong>
                <ul class="coincidencia-lista" id="listaCoincidencias">
                    {{-- Llenado dinámico por JavaScript --}}
                </ul>
                <p class="coincidencia-explicacion">
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                    <strong>¿Qué pasará?</strong> Ambos fondos no compiten ni se sobreescriben: <strong>rotarán juntos en el carrusel</strong>.
                    Asigna la <strong>Posición / Turno</strong> de abajo para elegir cuál imagen aparece primero.
                </p>
            </div>
        </div>
    </div>

    {{-- Posición / Turno en el carrusel (rediseño intuitivo) --}}
    <div class="pf-field full">
        <label class="pf-label">
            <i class="fas fa-arrow-down-1-9" aria-hidden="true"></i> Posición en el carrusel (Turno de rotación)
        </label>
        <div class="orden-selector-wrap">
            <div class="orden-stepper">
                <button type="button" class="orden-btn-step" id="btnOrdenMenos" title="Disminuir posición">
                    <i class="fas fa-minus" aria-hidden="true"></i>
                </button>
                <div class="orden-input-box">
                    <span class="orden-prefix">Turno #</span>
                    <input type="number" id="orden" name="orden" min="1" max="99" class="orden-number-input" value="{{ old('orden', ($fondo->orden > 0 ? $fondo->orden : 1)) }}">
                </div>
                <button type="button" class="orden-btn-step" id="btnOrdenMas" title="Aumentar posición">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                </button>
            </div>

            <div class="orden-quick-pills">
                <button type="button" class="orden-pill {{ old('orden', $fondo->orden ?? 1) == 1 ? 'is-active' : '' }}" data-orden-val="1">
                    <i class="fas fa-play" aria-hidden="true"></i> 1° Primero
                </button>
                <button type="button" class="orden-pill {{ old('orden', $fondo->orden ?? 1) == 2 ? 'is-active' : '' }}" data-orden-val="2">
                    2° Segundo
                </button>
                <button type="button" class="orden-pill {{ old('orden', $fondo->orden ?? 1) == 3 ? 'is-active' : '' }}" data-orden-val="3">
                    3° Tercero
                </button>
                <button type="button" class="orden-pill {{ old('orden', $fondo->orden ?? 1) >= 4 ? 'is-active' : '' }}" data-orden-val="4">
                    4° Cuarto o posterior
                </button>
            </div>
        </div>
        <p class="pf-help" id="ordenExplicacionTexto">
            <i class="fas fa-circle-question" aria-hidden="true"></i>
            <span id="ordenDescDinamica">Esta imagen será la <strong>primera (Turno 1)</strong> que verán los usuarios al ingresar al portal.</span>
        </p>
    </div>

    {{-- Estado --}}
    <div class="pf-field">
        <span class="pf-label"><i class="fas fa-toggle-on" aria-hidden="true"></i> Estado</span>
        <label class="switch">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" {{ old('activo', $fondo->activo ?? true) ? 'checked' : '' }}>
            <span class="track" aria-hidden="true"></span>
            <span class="switch-txt">Activo</span>
        </label>
    </div>

    {{-- Datos de fondos existentes para detección de coincidencias en tiempo real --}}
    <script id="fondosExistentesData" type="application/json">{!! json_encode($fondosExistentes ?? []) !!}</script>

    {{-- Vista previa --}}
    <div class="pf-field full">
        <div class="fondo-preview-cta">
            <div class="txt">
                <strong><i class="fas fa-eye" aria-hidden="true"></i> Vista previa interactiva</strong>
                <small>Mira cómo lucirá el fondo en Inicio y en Revistas antes de guardar.</small>
            </div>
            <button type="button" class="btn-panel ghost" data-fondo-preview-btn>
                <i class="fas fa-expand" aria-hidden="true"></i> Ver vista previa
            </button>
        </div>
    </div>
</div>

{{-- Modal de vista previa (propio del sistema con maquetas duales) --}}
<div class="fondo-modal" id="fondoPreviewModal" hidden>
    <div class="fondo-modal-backdrop" data-fondo-preview-close></div>
    <div class="fondo-modal-card fondo-modal-card-lg" role="dialog" aria-modal="true" aria-labelledby="fondoPreviewTitle">
        <header class="fondo-modal-head">
            <div class="fondo-modal-head-titles">
                <h3 id="fondoPreviewTitle"><i class="fas fa-eye" aria-hidden="true"></i> Vista previa del fondo</h3>
                <span class="fondo-modal-sub" data-preview-sub-hint>Simulación del portal en tiempo real</span>
            </div>
            <button type="button" class="fondo-modal-x" data-fondo-preview-close aria-label="Cerrar">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="fondo-modal-body">
            {{-- Barra de pestañas (Inicio / Revistas) --}}
            <div class="fondo-preview-tabs" data-preview-tabs>
                <button type="button" class="fondo-preview-tab is-active" data-preview-tab="inicio">
                    <i class="fas fa-house" aria-hidden="true"></i> Vista en Inicio
                </button>
                <button type="button" class="fondo-preview-tab" data-preview-tab="revistas">
                    <i class="fas fa-book-open" aria-hidden="true"></i> Vista en Revistas
                </button>
            </div>

            <div class="fondo-preview-portal">
                {{-- Capa con la imagen de fondo --}}
                <div class="fondo-preview-bg" data-preview-bg></div>

                {{-- Escenario 1: Simulación de la pantalla de Inicio --}}
                <div class="fondo-preview-stage stage-inicio" data-preview-stage="inicio">
                    <div class="mockup-inicio-card">
                        <div class="mockup-logo-tag"><i class="fas fa-book-bookmark" aria-hidden="true"></i> ITINEWS</div>
                        <h4 class="mockup-title">Portal de Revistas Académicas</h4>
                        <p class="mockup-desc">Explora las últimas investigaciones y publicaciones digitales universitarias.</p>
                        <div class="mockup-btn-access">
                            <span class="mockup-play-icon"><i class="fas fa-play" aria-hidden="true"></i></span>
                            <span class="mockup-btn-txt">Ingresar al Portal</span>
                        </div>
                    </div>
                </div>

                {{-- Escenario 2: Simulación de la pantalla de Revistas --}}
                <div class="fondo-preview-stage stage-revistas" data-preview-stage="revistas" hidden>
                    <aside class="mockup-revistas-sidebar">
                        <div class="mockup-sidebar-header">
                            <i class="fas fa-newspaper" aria-hidden="true"></i>
                            <span>Portal ITINEWS</span>
                        </div>
                        <div class="mockup-sidebar-menu">
                            <div class="mockup-nav-item is-active"><i class="fas fa-home" aria-hidden="true"></i> Inicio</div>
                            <div class="mockup-nav-item"><i class="fas fa-book" aria-hidden="true"></i> Revistas</div>
                            <div class="mockup-nav-item"><i class="fas fa-calendar" aria-hidden="true"></i> Año 2025</div>
                            <div class="mockup-nav-item"><i class="fas fa-heart" aria-hidden="true"></i> Favoritos</div>
                        </div>
                    </aside>
                    <div class="mockup-revistas-content">
                        <div class="mockup-content-header">
                            <div class="mockup-header-title">Todas las Revistas</div>
                            <div class="mockup-search-pill"><i class="fas fa-search" aria-hidden="true"></i> Buscar...</div>
                        </div>
                        <div class="mockup-grid">
                            <div class="mockup-card">
                                <div class="mockup-cover cover-1"></div>
                                <div class="mockup-card-title">Enero - Abril 2025</div>
                            </div>
                            <div class="mockup-card">
                                <div class="mockup-cover cover-2"></div>
                                <div class="mockup-card-title">Mayo - Agosto 2024</div>
                            </div>
                            <div class="mockup-card">
                                <div class="mockup-cover cover-3"></div>
                                <div class="mockup-card-title">Marzo - Abril 2024</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Overlay de datos del fondo --}}
                <div class="fondo-preview-overlay" data-preview-overlay>
                    <div class="fondo-preview-overlay-info">
                        <span class="fondo-preview-badge" data-preview-ubicacion>Inicio</span>
                        <strong data-preview-titulo>Sin título</strong>
                        <small data-preview-vigencia>Siempre activo</small>
                    </div>
                </div>

                {{-- Mensaje de fondo vacío --}}
                <div class="fondo-preview-vacio" data-preview-vacio hidden>
                    <i class="fas fa-image" aria-hidden="true"></i>
                    <span>Selecciona una imagen o una portada para ver la vista previa.</span>
                </div>
            </div>

            <p class="fondo-modal-hint" data-preview-hint></p>
        </div>

        <footer class="fondo-modal-pie">
            <button type="button" class="btn-panel" data-fondo-preview-close>Cerrar vista previa</button>
        </footer>
    </div>
</div>
