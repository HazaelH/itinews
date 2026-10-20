{{-- Modal de confirmación propio del sistema (reemplaza al confirm() del navegador).
     Se activa desde cualquier <form data-confirm="mensaje">. --}}
<div class="fondo-modal" id="fondoConfirmModal" hidden>
    <div class="fondo-modal-backdrop" data-confirm-cancel></div>
    <div class="fondo-modal-card fondo-modal-sm" role="dialog" aria-modal="true" aria-labelledby="fondoConfirmTitle">
        <header class="fondo-modal-head danger">
            <h3 id="fondoConfirmTitle"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> <span data-confirm-titulo>Confirmar acción</span></h3>
            <button type="button" class="fondo-modal-x" data-confirm-cancel aria-label="Cerrar">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </header>
        <div class="fondo-modal-body">
            <p class="fondo-confirm-msg" data-confirm-msg>¿Confirmas esta acción?</p>
        </div>
        <footer class="fondo-modal-pie">
            <button type="button" class="btn-panel ghost" data-confirm-cancel>
                <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
            </button>
            <button type="button" class="btn-panel vino" data-confirm-ok>
                <i class="far fa-trash-can" aria-hidden="true"></i> <span data-confirm-ok-txt>Eliminar</span>
            </button>
        </footer>
    </div>
</div>

{{-- Modal de confirmación propio del sistema para cambios sin guardar --}}
<div class="fondo-modal" id="fondoUnsavedModal" hidden>
    <div class="fondo-modal-backdrop" data-unsaved-stay></div>
    <div class="fondo-modal-card fondo-modal-sm" role="dialog" aria-modal="true" aria-labelledby="fondoUnsavedTitle">
        <header class="fondo-modal-head danger">
            <h3 id="fondoUnsavedTitle"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> <span>Cambios sin guardar</span></h3>
            <button type="button" class="fondo-modal-x" data-unsaved-stay aria-label="Cerrar">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </header>
        <div class="fondo-modal-body">
            <p class="fondo-confirm-msg">Tienes modificaciones pendientes en este formulario. Si sales ahora, se perderán los cambios realizados.</p>
        </div>
        <footer class="fondo-modal-pie">
            <button type="button" class="btn-panel ghost" data-unsaved-stay>
                <i class="fas fa-xmark" aria-hidden="true"></i> Permanecer aquí
            </button>
            <button type="button" class="btn-panel vino" data-unsaved-leave>
                <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i> Salir sin guardar
            </button>
        </footer>
    </div>
</div>

{{-- Modal explicativo: ¿Cómo funciona el módulo de fondos? --}}
<div class="fondo-modal" id="fondoGuiaModal" hidden>
    <div class="fondo-modal-backdrop" data-guia-close></div>
    <div class="fondo-modal-card fondo-modal-lg" role="dialog" aria-modal="true" aria-labelledby="fondoGuiaTitle">
        <header class="fondo-modal-head">
            <div class="fondo-modal-head-titles">
                <h3 id="fondoGuiaTitle"><i class="fas fa-circle-question" aria-hidden="true"></i> ¿Cómo funciona el Gestor de Fondos?</h3>
                <span class="fondo-modal-sub">Guía detallada para configurar fondos en el portal ITINEWS sin errores</span>
            </div>
            <button type="button" class="fondo-modal-x" data-guia-close aria-label="Cerrar">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="fondo-modal-body fondo-guia-body">
            <div class="fondo-guia-grid">

                {{-- 1. Origen de la imagen --}}
                <div class="fondo-guia-card">
                    <div class="guia-card-icon"><i class="fas fa-image" aria-hidden="true"></i></div>
                    <div class="guia-card-info">
                        <h4>1. Origen de la imagen</h4>
                        <p>Puedes elegir de dónde proviene la ilustración que servirá como fondo:</p>
                        <ul>
                            <li><strong>Subir imagen:</strong> Sube una imagen propia (JPG, PNG o WEBP máx. 8 MB). Se recomienda al menos 1920×1080 px en orientación horizontal.</li>
                            <li><strong>Portada de revista:</strong> Elige la portada de alguna de las revistas activas de ITINEWS para promocionarla en el fondo.</li>
                        </ul>
                    </div>
                </div>

                {{-- 2. Dónde se muestra --}}
                <div class="fondo-guia-card">
                    <div class="guia-card-icon"><i class="fas fa-location-dot" aria-hidden="true"></i></div>
                    <div class="guia-card-info">
                        <h4>2. ¿Dónde se muestra? (Ubicación)</h4>
                        <p>Define con exactitud en qué sección de la plataforma aparecerá el fondo:</p>
                        <ul>
                            <li><strong>Inicio:</strong> Se mostrará exclusivamente en la pantalla de bienvenida y acceso general.</li>
                            <li><strong>Revistas:</strong> Se mostrará en el portal de revistas académicas detrás de las publicaciones y barra lateral.</li>
                            <li><strong>Ambas:</strong> Se proyectará en las dos secciones de forma sincronizada.</li>
                        </ul>
                    </div>
                </div>

                {{-- 3. Tipos de Vigencia --}}
                <div class="fondo-guia-card">
                    <div class="guia-card-icon"><i class="fas fa-calendar-days" aria-hidden="true"></i></div>
                    <div class="guia-card-info">
                        <h4>3. Vigencia y Prioridades</h4>
                        <p>El sistema decide automáticamente qué fondo mostrar según la fecha de hoy:</p>
                        <ul>
                            <li><strong>Siempre activo:</strong> El fondo se muestra permanentemente si no hay una fecha o rango especial activa.</li>
                            <li><strong>Rango de fechas:</strong> Permite establecer un periodo (ej. Otoño, Primavera o un mes). Si marcas <em>"Se repite cada año"</em>, se renovará solo.</li>
                            <li><strong>Conmemorativa (Prioridad máxima):</strong> Fechas cívicas o tecnológicas (ej. Día del Programador, Día del Ingeniero). Si hoy coincide con una fecha conmemorativa activa, <strong>tendrá prioridad inmediata</strong> sobre cualquier rango de fechas.</li>
                        </ul>
                    </div>
                </div>

                {{-- 4. Exclusividad del carrusel --}}
                <div class="fondo-guia-card">
                    <div class="guia-card-icon"><i class="fas fa-layer-group" aria-hidden="true"></i></div>
                    <div class="guia-card-info">
                        <h4>4. Funcionamiento del Carrusel</h4>
                        <p>El carrusel del portal funciona con una jerarquía inteligente:</p>
                        <ul>
                            <li><strong>Fondos configurados:</strong> Si creas un fondo (ej. Otoño para Revistas), se mostrará <strong>únicamente tu fondo</strong>, sin mezclar portadas adicionales.</li>
                            <li><strong>Respaldo de portadas:</strong> Las portadas de revistas solo se usarán si no tienes ningún fondo configurado para esa sección.</li>
                        </ul>
                    </div>
                </div>

                {{-- 5. Vista previa interactiva --}}
                <div class="fondo-guia-card full">
                    <div class="guia-card-icon"><i class="fas fa-eye" aria-hidden="true"></i></div>
                    <div class="guia-card-info">
                        <h4>5. Vista Previa Interactiva antes de Guardar</h4>
                        <p>Antes de guardar, haz clic en el botón <strong>"Ver vista previa"</strong>. Podrás verificar cómo se ve tu fondo tanto en la pantalla de Inicio como en el portal de Revistas con su barra de navegación y tarjetas, asegurando un contraste y legibilidad óptimos.</p>
                    </div>
                </div>

            </div>
        </div>

        <footer class="fondo-modal-pie">
            <button type="button" class="btn-panel" data-guia-close>
                <i class="fas fa-check" aria-hidden="true"></i> Entendido, volver al módulo
            </button>
        </footer>
    </div>
</div>

{{-- Modal propio para crear, editar y eliminar Presets (Fechas conmemorativas o Rangos) --}}
<div class="fondo-modal" id="fondoPresetGestorModal" hidden>
    <div class="fondo-modal-backdrop" data-preset-gestor-close></div>
    <div class="fondo-modal-card fondo-modal-lg" role="dialog" aria-modal="true" aria-labelledby="fondoPresetGestorTitle">
        <header class="fondo-modal-head">
            <div class="fondo-modal-head-titles">
                <h3 id="fondoPresetGestorTitle"><i class="fas fa-calendar-days" aria-hidden="true"></i> <span data-gestor-tipo-label>Gestionar Fechas Predeterminadas</span></h3>
                <span class="fondo-modal-sub">Agrega, edita o elimina opciones de la cinta de selección rápida</span>
            </div>
            <button type="button" class="fondo-modal-x" data-preset-gestor-close aria-label="Cerrar">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="fondo-modal-body fondo-preset-gestor-body">
            {{-- Formulario para crear o editar un preset --}}
            <div class="preset-form-box">
                <h4 id="presetFormTitulo"><i class="fas fa-plus-circle" aria-hidden="true"></i> <span data-preset-form-modo>Nueva opción</span></h4>
                <form id="formPresetItem" onsubmit="return false;">
                    <input type="hidden" id="presetId" value="">
                    <input type="hidden" id="presetTipo" value="conmemorativa">

                    <div class="preset-form-row">
                        <div class="preset-field name-field">
                            <label for="presetNombre">Nombre <span class="req">*</span></label>
                            <input type="text" id="presetNombre" class="pf-input" placeholder="Ej. Aniversario UPVT..." required>
                        </div>

                        {{-- Campos para fecha conmemorativa (un solo día MM-DD) --}}
                        <div class="preset-field date-field" data-campo-preset="conmemorativa">
                            <label for="presetDiaDia">Fecha conmemorativa (Día y Mes) <span class="req">*</span></label>
                            <div class="preset-date-inputs">
                                <select id="presetDiaDia" class="pf-input select-dia">
                                    @for($d = 1; $d <= 31; $d++)
                                        <option value="{{ sprintf('%02d', $d) }}">{{ sprintf('%02d', $d) }}</option>
                                    @endfor
                                </select>
                                <span class="preset-sep">de</span>
                                <select id="presetDiaMes" class="pf-input select-mes">
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                        </div>

                        {{-- Campos para rango de fechas (desde MM-DD hasta MM-DD) --}}
                        <div class="preset-field range-field" data-campo-preset="rango" hidden>
                            <label>Periodo del rango (Desde → Hasta) <span class="req">*</span></label>
                            <div class="preset-range-inputs">
                                <div class="preset-date-inputs">
                                    <span class="range-lbl">Desde:</span>
                                    <select id="presetDesdeDia" class="pf-input select-dia">
                                        @for($d = 1; $d <= 31; $d++)
                                            <option value="{{ sprintf('%02d', $d) }}">{{ sprintf('%02d', $d) }}</option>
                                        @endfor
                                    </select>
                                    <select id="presetDesdeMes" class="pf-input select-mes">
                                        <option value="01">Ene</option><option value="02">Feb</option><option value="03">Mar</option><option value="04">Abr</option>
                                        <option value="05">May</option><option value="06">Jun</option><option value="07">Jul</option><option value="08">Ago</option>
                                        <option value="09">Sep</option><option value="10">Oct</option><option value="11">Nov</option><option value="12">Dic</option>
                                    </select>
                                </div>
                                <span class="range-arrow"><i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                                <div class="preset-date-inputs">
                                    <span class="range-lbl">Hasta:</span>
                                    <select id="presetHastaDia" class="pf-input select-dia">
                                        @for($d = 1; $d <= 31; $d++)
                                            <option value="{{ sprintf('%02d', $d) }}">{{ sprintf('%02d', $d) }}</option>
                                        @endfor
                                    </select>
                                    <select id="presetHastaMes" class="pf-input select-mes">
                                        <option value="01">Ene</option><option value="02">Feb</option><option value="03">Mar</option><option value="04">Abr</option>
                                        <option value="05">May</option><option value="06">Jun</option><option value="07">Jul</option><option value="08">Ago</option>
                                        <option value="09">Sep</option><option value="10">Oct</option><option value="11">Nov</option><option value="12">Dic</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="preset-field actions-field">
                            <button type="button" class="btn-panel" id="btnGuardarPresetItem">
                                <i class="fas fa-floppy-disk" aria-hidden="true"></i> Guardar
                            </button>
                            <button type="button" class="btn-panel ghost" id="btnCancelarEdicionPreset" hidden>
                                Cancelar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Lista de presets actuales --}}
            <div class="preset-lista-wrap">
                <h4><i class="fas fa-list-check" aria-hidden="true"></i> Opciones en la cinta</h4>
                <div class="preset-table-responsive">
                    <table class="preset-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Fecha / Periodo</th>
                                <th style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPresetsBody">
                            {{-- Poblado por JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="fondo-modal-pie">
            <button type="button" class="btn-panel" data-preset-gestor-close>
                <i class="fas fa-check" aria-hidden="true"></i> Listo, volver al formulario
            </button>
        </footer>
    </div>
</div>

