{{-- Modales propios del Dashboard ITINEWS:
     1. Modal de confirmación para eliminar revista permanentemente.
     2. Modal de confirmación para reprocesar revista.
     3. Modal de progreso en vivo durante el reprocesamiento. --}}

{{-- 1. Confirmación de Eliminación --}}
<div class="fondo-modal" id="dashboardConfirmDeleteModal" hidden>
    <div class="fondo-modal-backdrop" data-delete-cancel></div>
    <div class="fondo-modal-card fondo-modal-sm" role="dialog" aria-modal="true" aria-labelledby="dashboardDeleteTitle">
        <header class="fondo-modal-head danger">
            <h3 id="dashboardDeleteTitle">
                <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                <span>¿Eliminar revista permanentemente?</span>
            </h3>
            <button type="button" class="fondo-modal-x" data-delete-cancel aria-label="Cerrar">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </header>
        <div class="fondo-modal-body">
            <p class="fondo-confirm-msg">
                Estás a punto de eliminar la revista <strong id="deleteModalRevistaTitulo">esta revista</strong>.
            </p>
            <p class="fondo-confirm-sub" style="font-size: 0.85rem; color: #64748b; margin-top: 0.5rem;">
                Esta acción borrará de manera definitiva el documento PDF, su portada y todas sus páginas optimizadas. No se puede deshacer.
            </p>
        </div>
        <footer class="fondo-modal-pie">
            <button type="button" class="btn-panel ghost" data-delete-cancel>
                <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
            </button>
            <button type="button" class="btn-panel vino" id="btnConfirmDelete">
                <i class="far fa-trash-can" aria-hidden="true"></i> Eliminar definitivamente
            </button>
        </footer>
    </div>
</div>

{{-- 2. Confirmación de Reprocesamiento --}}
<div class="fondo-modal" id="dashboardConfirmReprocessModal" hidden>
    <div class="fondo-modal-backdrop" data-reprocess-cancel></div>
    <div class="fondo-modal-card fondo-modal-sm" role="dialog" aria-modal="true" aria-labelledby="dashboardReprocessTitle">
        <header class="fondo-modal-head">
            <h3 id="dashboardReprocessTitle">
                <i class="fas fa-rotate" aria-hidden="true"></i>
                <span>¿Reprocesar revista?</span>
            </h3>
            <button type="button" class="fondo-modal-x" data-reprocess-cancel aria-label="Cerrar">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </header>
        <div class="fondo-modal-body">
            <p class="fondo-confirm-msg">
                ¿Deseas volver a procesar el PDF de <strong id="reprocessModalRevistaTitulo">esta revista</strong>?
            </p>
            <p class="fondo-confirm-sub" style="font-size: 0.85rem; color: #64748b; margin-top: 0.5rem;">
                Se limpiarán las imágenes actuales y se volverán a generar todas las páginas en formato WebP de alta resolución junto con su portada.
            </p>
        </div>
        <footer class="fondo-modal-pie">
            <button type="button" class="btn-panel ghost" data-reprocess-cancel>
                <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
            </button>
            <button type="button" class="btn-panel primary" id="btnConfirmReprocess">
                <i class="fas fa-rotate" aria-hidden="true"></i> Sí, reprocesar
            </button>
        </footer>
    </div>
</div>

{{-- 3. Modal de Progreso en Vivo: Reprocesamiento HD --}}
<div class="fondo-modal" id="dashboardModalProgresoReprocesar" hidden>
    <div class="fondo-modal-backdrop" id="reprocessModalBackdrop"></div>
    <div class="fondo-modal-card upload-progress-card" role="dialog" aria-modal="true" aria-labelledby="reprocessProgressTitle">
        <header class="fondo-modal-head upload-progress-head">
            <div class="upload-modal-badge" id="reprocessProgressBadge">
                <i class="fas fa-rotate fa-spin" id="reprocessProgressBadgeIcon"></i>
            </div>
            <div class="upload-modal-titles">
                <h3 id="reprocessProgressTitle">Reprocesando revista</h3>
                <span class="fondo-modal-sub" id="reprocessProgressSub">Por favor mantén esta ventana abierta mientras se completa el proceso.</span>
            </div>
        </header>

        <div class="fondo-modal-body upload-progress-body">
            <!-- Barra de progreso con animación fluida -->
            <div class="upload-progress-track">
                <div class="upload-progress-fill" id="reprocessProgressFill" style="width: 0%;"></div>
            </div>

            <!-- Métricas de estado y porcentaje numérico -->
            <div class="upload-progress-metrics">
                <span class="upload-progress-status" id="reprocessProgressStatus">Iniciando reprocesamiento...</span>
                <span class="upload-progress-percent" id="reprocessProgressPercent">0%</span>
            </div>

            <!-- Detalle de páginas procesadas -->
            <div class="upload-progress-detail" id="reprocessProgressDetail">
                <span id="reprocessDetailText">Preparando páginas del PDF...</span>
            </div>

            <!-- Fases del proceso -->
            <div class="upload-step-list" id="reprocessStepList">
                <div class="upload-step active" id="reprocessStepPrep">
                    <span class="step-dot"><i class="fas fa-file-pdf"></i></span>
                    <span class="step-text">Limpieza y preparación del documento PDF</span>
                </div>
                <div class="upload-step" id="reprocessStepConvert">
                    <span class="step-dot"><i class="fas fa-images"></i></span>
                    <span class="step-text">Extracción y optimización de páginas WebP HD</span>
                </div>
                <div class="upload-step" id="reprocessStepFinish">
                    <span class="step-dot"><i class="fas fa-check"></i></span>
                    <span class="step-text">Actualización de portada y publicación</span>
                </div>
            </div>

            <!-- Mensaje de error si falla -->
            <div class="upload-error-box" id="reprocessErrorBox" hidden>
                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                <div id="reprocessErrorMsg"></div>
            </div>
        </div>

        <footer class="fondo-modal-pie" id="reprocessModalFooter" hidden>
            <button type="button" class="btn-panel ghost" id="reprocessModalCloseBtn">
                <i class="fas fa-xmark" aria-hidden="true"></i> Cerrar
            </button>
        </footer>
    </div>
</div>
