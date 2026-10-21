<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    <div class="panel-page">
        <div class="panel-wrap">

            @if(session('error'))
                <div class="panel-alerta err"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="panel-alerta ok"><i class="fas fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</div>
            @endif

            <header class="panel-head">
                <div>
                    <h1>Editar Revista</h1>
                    <p>Actualiza la información, la visibilidad y la portada de la revista.</p>
                </div>
                <div class="panel-actions">
                    <a href="{{ route('revistas.show', $revista) }}" class="btn-panel ghost" target="_blank" rel="noopener">
                        <i class="far fa-eye" aria-hidden="true"></i> Ver revista
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Dashboard
                    </a>
                </div>
            </header>

            @php
                if ($revista->estaProcesada()) {
                    $estClase = 'completado'; $estIcono = 'fa-circle-check'; $estTexto = 'Completada';
                } elseif ($revista->tieneError()) {
                    $estClase = 'error'; $estIcono = 'fa-triangle-exclamation'; $estTexto = 'Con error';
                } elseif ($revista->estaProcesando()) {
                    $estClase = 'procesando'; $estIcono = 'fa-spinner fa-spin'; $estTexto = 'En proceso';
                } else {
                    $estClase = 'pendiente'; $estIcono = 'fa-clock'; $estTexto = 'Pendiente';
                }
            @endphp

            <div class="panel-form-card">
                <form action="{{ route('admin.revistas.update', $revista) }}" method="POST"
                      enctype="multipart/form-data" id="revistaForm">
                    @csrf
                    @method('PUT')

                    <div class="panel-form-grid">
                        <div class="pf-field full">
                            <label for="titulo" class="pf-label">
                                <i class="fas fa-heading" aria-hidden="true"></i> Título de la revista <span class="req">*</span>
                            </label>
                            <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $revista->titulo) }}"
                                   class="pf-input @error('titulo') is-invalid @enderror" required>
                            @error('titulo')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <label for="fecha_publicacion" class="pf-label">
                                <i class="fas fa-calendar-day" aria-hidden="true"></i> Fecha de publicación <span class="req">*</span>
                            </label>
                            <input type="date" id="fecha_publicacion" name="fecha_publicacion"
                                   value="{{ old('fecha_publicacion', $revista->fecha_publicacion?->format('Y-m-d')) }}"
                                   class="pf-input @error('fecha_publicacion') is-invalid @enderror" required>
                            @error('fecha_publicacion')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <span class="pf-label"><i class="fas fa-toggle-on" aria-hidden="true"></i> Visibilidad en el portal</span>
                            <label class="switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1" {{ old('activo', $revista->estaActiva() ? '1' : '0') ? 'checked' : '' }}>
                                <span class="track" aria-hidden="true"></span>
                                <span style="margin-left:10px; font-size:.88rem; color:#6b6b6b;">Visible en el portal</span>
                            </label>
                            <span class="pf-help">Ocúltala para retirarla del portal sin borrarla; puedes reactivarla cuando quieras.</span>
                        </div>

                        <div class="pf-field">
                            <span class="pf-label"><i class="fas fa-star" aria-hidden="true" style="color:#f59e0b;"></i> Revista Destacada</span>
                            <label class="switch">
                                <input type="hidden" name="destacada" value="0">
                                <input type="checkbox" name="destacada" value="1" {{ old('destacada', $revista->esDestacada() ? '1' : '0') ? 'checked' : '' }}>
                                <span class="track" aria-hidden="true"></span>
                                <span style="margin-left:10px; font-size:.88rem; color:#6b6b6b;">Mostrar en sección "Revistas Destacadas"</span>
                            </label>
                            <span class="pf-help">Si está activa, aparecerá fijada en la sección de destacadas del portal.</span>
                        </div>

                        <div class="pf-field full">
                            <label for="descripcion" class="pf-label">
                                <i class="fas fa-align-left" aria-hidden="true"></i> Descripción
                            </label>
                            <textarea id="descripcion" name="descripcion"
                                      class="pf-textarea @error('descripcion') is-invalid @enderror">{{ old('descripcion', $revista->descripcion) }}</textarea>
                            @error('descripcion')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field full">
                            <span class="pf-label"><i class="fas fa-image" aria-hidden="true"></i> Portada</span>
                            <div style="display:flex; gap:18px; align-items:flex-start; flex-wrap:wrap;">
                                <div style="flex:0 0 120px;">
                                    <img src="{{ asset('storage/' . $revista->ruta_portada) }}"
                                         alt="Portada actual de {{ $revista->titulo }}"
                                         id="portadaPreview"
                                         style="width:120px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,.2); background:#eee;">
                                </div>
                                <div style="flex:1; min-width:220px;">
                                    <label for="ruta_portada" class="pf-label" style="margin-bottom:8px;">
                                        <i class="fas fa-upload" aria-hidden="true"></i> Cambiar portada (opcional)
                                    </label>
                                    <input type="file" name="ruta_portada" id="ruta_portada"
                                           accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                           class="pf-input @error('ruta_portada') is-invalid @enderror">
                                    <span class="pf-help">JPEG, PNG, JPG, GIF o WEBP · máximo 2 MB. Si no eliges una, se conserva la actual.</span>
                                    @error('ruta_portada')<span class="pf-error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="pf-field full">
                            <div class="info-card">
                                <i class="fas {{ $estIcono }}" aria-hidden="true"></i>
                                <div style="flex:1;">
                                    <strong>Información del PDF</strong>
                                    <p>
                                        Archivo: {{ basename($revista->ruta_pdf) }} ·
                                        {{ $revista->paginas()->count() }} páginas ·
                                        Subido el {{ $revista->created_at->format('d/m/Y H:i') }}
                                    </p>
                                    <p style="margin-top:8px;">
                                        <span class="rev-estado {{ $estClase }}">
                                            <i class="fas {{ $estIcono }}" aria-hidden="true"></i> {{ $estTexto }}
                                        </span>
                                    </p>
                                    @if($revista->estaProcesando())
                                        <div class="progress-bar-container" style="margin-top:8px;">
                                            <div class="progress-bar-fill" data-progress="{{ $revista->progreso }}"
                                                 data-revista-id="{{ $revista->id }}"
                                                 style="width: {{ round($revista->progreso) }}%"></div>
                                        </div>
                                        <div class="rev-progreso-txt revista-progreso">{{ round($revista->progreso) }}%</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-form-pie">
                        <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                            <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-panel" id="revistaSubmit">
                            <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                            <span id="revistaSubmitTxt">Guardar Cambios</span>
                        </button>
                    </div>
                </form>
            </div>

            @if(auth()->user()->isAdmin())
                <div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:20px;">
                    @if($revista->estaProcesada())
                        <div class="info-card" style="flex:1; min-width:260px; justify-content:space-between;">
                            <div style="display:flex; gap:14px; align-items:flex-start;">
                                <i class="fas fa-rotate" aria-hidden="true"></i>
                                <div>
                                    <strong>Reprocesar PDF</strong>
                                    <p>Vuelve a generar las páginas y la portada desde el PDF original.</p>
                                </div>
                            </div>
                            <form action="{{ route('admin.revistas.reprocesar', $revista) }}" method="POST"
                                  onsubmit="return confirm('¿Reprocesar el PDF de esta revista?')">
                                @csrf
                                <button type="submit" class="btn-panel">
                                    <i class="fas fa-rotate" aria-hidden="true"></i> Reprocesar
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="danger-zone" style="flex:1; min-width:260px;">
                        <div class="txt">
                            <strong><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Eliminar revista</strong>
                            <small>Se borrarán la revista, sus páginas y sus imágenes. No se puede deshacer.</small>
                        </div>
                        <form action="{{ route('admin.revistas.destroy', $revista) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar esta revista permanentemente?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-panel vino">
                                <i class="far fa-trash-can" aria-hidden="true"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/revista-form.js') }}" defer></script>
    @endpush
</x-app-layout>
