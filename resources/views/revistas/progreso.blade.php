<x-app-layout>
    <div class="admin-layout-wrapper">
        <div class="progreso-vista-container">
            <div class="progreso-vista-card">
                <input type="hidden" id="revista-id" value="{{ $revista->id }}">

                <header class="progreso-vista-head">
                    <div class="upload-modal-badge {{ $revista->estaProcesada() ? 'badge-success' : ($revista->tieneError() ? 'badge-error' : '') }}" id="badgeProgreso">
                        <i class="fas {{ $revista->estaProcesada() ? 'fa-check' : ($revista->tieneError() ? 'fa-triangle-exclamation' : 'fa-rotate fa-spin') }}" id="iconoProgreso"></i>
                    </div>
                    <div class="upload-modal-titles" style="margin-top: 14px; text-align: center;">
                        <h3 id="tituloProgreso">{{ $revista->titulo }}</h3>
                        <span class="fondo-modal-sub" id="subtituloProgreso">
                            {{ $revista->estaProcesada() ? 'Procesamiento completado con éxito.' : 'Procesando páginas en Alta Resolución...' }}
                        </span>
                    </div>
                </header>

                <div class="progreso-vista-body">
                    <!-- Barra de progreso -->
                    <div class="upload-progress-track">
                        <div class="upload-progress-fill" id="barra-progreso" style="width: {{ $revista->progreso }}%;"></div>
                    </div>

                    <!-- Métricas -->
                    <div class="upload-progress-metrics">
                        <span class="upload-progress-status" id="estado">
                            {{ $revista->estaProcesada() ? 'Completado' : 'Iniciando procesamiento...' }}
                        </span>
                        <span class="upload-progress-percent" id="porcentaje">{{ $revista->progreso }}%</span>
                    </div>

                    <div class="upload-progress-detail">
                        <span id="contador-paginas">{{ $revista->paginas()->count() }}/{{ $revista->total_paginas }} páginas</span>
                    </div>

                    <div id="detalles" style="font-size: 0.85rem; color: #64748b; margin-top: 6px;">
                        {{ $revista->estaProcesada() ? 'Todas las páginas han sido convertidas exitosamente' : 'Preparando la conversión de páginas...' }}
                    </div>

                    <!-- Información de la revista -->
                    <div class="progreso-info-card">
                        <div><strong>Título:</strong> {{ $revista->titulo }}</div>
                        <div style="margin-top: 4px;"><strong>Fecha de publicación:</strong> {{ \Carbon\Carbon::parse($revista->fecha_publicacion)->format('d/m/Y') }}</div>
                    </div>

                    <!-- Estado Completado (Centrado) -->
                    <div id="completado" class="{{ $revista->estaProcesada() ? '' : 'hidden' }} progreso-completado-box">
                        <div class="progreso-completado-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h4>¡Procesamiento completado al 100%!</h4>
                        <p>La revista ha sido optimizada y está lista para su lectura.</p>
                        <div class="progreso-acciones">
                            <a href="{{ route('revistas.show', $revista) }}" class="btn-panel primary">
                                <i class="far fa-eye"></i> Ver Revista
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                                <i class="fas fa-table-list"></i> Volver al Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- Estado Error -->
                    <div id="error" class="{{ $revista->tieneError() ? '' : 'hidden' }} progreso-error-box" style="background: #fef2f2; border: 1.5px solid #ef4444; border-radius: 16px; padding: 24px; margin-top: 1.5rem; text-align: center;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #ef4444; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 12px;">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <h4 style="margin: 0 0 6px; font-size: 1.25rem; font-weight: 700; color: #991b1b;">Ocurrió un inconveniente</h4>
                        <p style="margin: 0 0 16px; color: #b91c1c; font-size: 0.95rem;" id="mensaje-error">Ha ocurrido un error al procesar las páginas de la revista.</p>
                        <div class="progreso-acciones">
                            <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                                <i class="fas fa-arrow-left"></i> Volver al Dashboard
                            </a>
                        </div>
                    </div>

                    <div id="spinner" class="{{ ($revista->estaProcesada() || $revista->tieneError()) ? 'hidden' : '' }}" style="margin-top: 1.5rem;">
                        <span style="font-size: 0.9rem; color: #64748b; font-weight: 500;">
                            <i class="fas fa-circle-notch fa-spin"></i> Actualizando progreso automáticamente...
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/progreso-revista.js') }}?v={{ file_exists(public_path('js/progreso-revista.js')) ? filemtime(public_path('js/progreso-revista.js')) : time() }}"></script>
    @endpush
</x-app-layout>