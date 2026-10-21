<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    <div class="panel-page">
        <div class="panel-wrap">

            <header class="panel-head">
                <div>
                    <h1>Subir Nueva Revista</h1>
                    <p>Carga un PDF y el sistema generará la portada y las páginas automáticamente.</p>
                </div>
                <div class="panel-actions">
                    <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al Dashboard
                    </a>
                </div>
            </header>

            @if(session('error'))
                <div class="panel-alerta err"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ session('error') }}</div>
            @endif

            <div class="panel-form-card">
                <form method="POST" action="{{ route('admin.revistas.store') }}"
                      enctype="multipart/form-data" id="revistaForm">
                    @csrf

                    <div class="panel-form-grid">
                        <div class="pf-field full">
                            <label for="titulo" class="pf-label">
                                <i class="fas fa-heading" aria-hidden="true"></i> Título de la revista <span class="req">*</span>
                            </label>
                            <input type="text" id="titulo" name="titulo" value="{{ old('titulo') }}"
                                   class="pf-input @error('titulo') is-invalid @enderror"
                                   placeholder="Ej. Gaceta ITINEWS · Octubre 2026" required autofocus>
                            @error('titulo')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <label for="fecha_publicacion" class="pf-label">
                                <i class="fas fa-calendar-day" aria-hidden="true"></i> Fecha de publicación <span class="req">*</span>
                            </label>
                            <input type="date" id="fecha_publicacion" name="fecha_publicacion"
                                   value="{{ old('fecha_publicacion', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="pf-input @error('fecha_publicacion') is-invalid @enderror" required>
                            @error('fecha_publicacion')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field">
                            <span class="pf-label"><i class="fas fa-toggle-on" aria-hidden="true"></i> Visibilidad</span>
                            <label class="switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1" {{ old('activo', '1') ? 'checked' : '' }}>
                                <span class="track" aria-hidden="true"></span>
                                <span style="margin-left:10px; font-size:.88rem; color:#6b6b6b;">Publicar en el portal al procesar</span>
                            </label>
                        </div>

                        <div class="pf-field">
                            <span class="pf-label"><i class="fas fa-star" aria-hidden="true" style="color:#f59e0b;"></i> Revista Destacada</span>
                            <label class="switch">
                                <input type="hidden" name="destacada" value="0">
                                <input type="checkbox" name="destacada" value="1" {{ old('destacada', '0') === '1' ? 'checked' : '' }}>
                                <span class="track" aria-hidden="true"></span>
                                <span style="margin-left:10px; font-size:.88rem; color:#6b6b6b;">Mostrar en sección "Revistas Destacadas"</span>
                            </label>
                        </div>

                        <div class="pf-field full">
                            <label for="descripcion" class="pf-label">
                                <i class="fas fa-align-left" aria-hidden="true"></i> Descripción
                            </label>
                            <textarea id="descripcion" name="descripcion"
                                      class="pf-textarea @error('descripcion') is-invalid @enderror"
                                      placeholder="Breve descripción de la revista (opcional)…">{{ old('descripcion') }}</textarea>
                            @error('descripcion')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field full">
                            <span class="pf-label">
                                <i class="fas fa-file-pdf" aria-hidden="true"></i> Archivo PDF <span class="req">*</span>
                            </span>

                            <div class="dropzone" id="dropzone" role="button" tabindex="0" aria-label="Seleccionar archivo PDF">
                                <input type="file" name="archivo_pdf" id="archivo_pdf"
                                       accept="application/pdf" required>
                                <span class="dropzone-ico"><i class="fas fa-cloud-arrow-up" aria-hidden="true"></i></span>
                                <strong>Arrastra tu PDF aquí o haz clic para seleccionar</strong>
                                <small>Formato PDF · hasta 50 MB</small>
                                <div class="dropzone-archivo" id="dropArchivo" hidden>
                                    <i class="fas fa-file-pdf" aria-hidden="true"></i>
                                    <span id="dropNombre"></span>
                                    <button type="button" class="quitar" id="dropQuitar">
                                        <i class="fas fa-xmark" aria-hidden="true"></i> Quitar
                                    </button>
                                </div>
                            </div>
                            @error('archivo_pdf')<span class="pf-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pf-field full">
                            <div class="info-card">
                                <i class="fas fa-circle-info" aria-hidden="true"></i>
                                <div>
                                    <strong>No necesitas subir una portada</strong>
                                    <p>El sistema tomará automáticamente la <strong>primera página</strong> del PDF como portada de la revista.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-form-pie">
                        <a href="{{ route('dashboard') }}" class="btn-panel ghost">
                            <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-panel" id="revistaSubmit">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                            <span id="revistaSubmitTxt">Subir y Procesar Revista</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Modal de Progreso en Vivo: Subida y Conversión HD -->
            <div class="fondo-modal" id="modalProgresoSubida" hidden>
                <div class="fondo-modal-backdrop" id="modalProgresoBackdrop"></div>
                <div class="fondo-modal-card upload-progress-card" role="dialog" aria-modal="true" aria-labelledby="uploadModalTitle">
                    <header class="fondo-modal-head upload-progress-head">
                        <div class="upload-modal-badge" id="uploadModalBadge">
                            <i class="fas fa-cloud-arrow-up" id="uploadModalBadgeIcon"></i>
                        </div>
                        <div class="upload-modal-titles">
                            <h3 id="uploadModalTitle">Subiendo revista al servidor</h3>
                            <span class="fondo-modal-sub" id="uploadModalSub">Por favor mantén esta ventana abierta mientras se completa el proceso.</span>
                        </div>
                    </header>

                    <div class="fondo-modal-body upload-progress-body">
                        <!-- Barra de progreso principal con animación fluida -->
                        <div class="upload-progress-track">
                            <div class="upload-progress-fill" id="uploadProgressFill" style="width: 0%;"></div>
                        </div>

                        <!-- Estado y porcentaje numérico -->
                        <div class="upload-progress-metrics">
                            <span class="upload-progress-status" id="uploadProgressStatus">Preparando envío...</span>
                            <span class="upload-progress-percent" id="uploadProgressPercent">0%</span>
                        </div>

                        <!-- Detalles de bytes y velocidad -->
                        <div class="upload-progress-detail" id="uploadProgressDetail">
                            <span id="uploadBytesDetail">0 MB / 0 MB</span>
                            <span id="uploadSpeedDetail"></span>
                        </div>

                        <!-- Fases del proceso -->
                        <div class="upload-step-list" id="uploadStepList">
                            <div class="upload-step active" id="stepUpload">
                                <span class="step-dot"><i class="fas fa-arrow-up"></i></span>
                                <span class="step-text">Envío del archivo PDF al servidor</span>
                            </div>
                            <div class="upload-step" id="stepConvert">
                                <span class="step-dot"><i class="fas fa-images"></i></span>
                                <span class="step-text">Extracción y optimización de páginas WebP HD</span>
                            </div>
                            <div class="upload-step" id="stepFinish">
                                <span class="step-dot"><i class="fas fa-check"></i></span>
                                <span class="step-text">Generación de portada y publicación</span>
                            </div>
                        </div>

                        <!-- Mensaje de error si falla la subida -->
                        <div class="upload-error-box" id="uploadErrorBox" hidden>
                            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            <div id="uploadErrorMsg"></div>
                        </div>
                    </div>

                    <footer class="fondo-modal-pie" id="uploadModalFooter" hidden>
                        <button type="button" class="btn-panel ghost" id="uploadModalCloseBtn">
                            <i class="fas fa-xmark" aria-hidden="true"></i> Cerrar y corregir
                        </button>
                    </footer>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/revista-form.js') }}" defer></script>
    @endpush
</x-app-layout>
