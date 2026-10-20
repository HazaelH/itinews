/**
 * Formulario de revista (crear / editar) — ITINEWS.
 *
 *   - Dropzone: arrastrar y soltar PDF, mostrar nombre/peso y quitar.
 *   - Subida AJAX con barra de progreso en tiempo real (0 - 100%, MBs y velocidad).
 *   - Transición fluida a progreso de conversión de páginas HD.
 *   - Vista previa al cambiar la portada (modo edición).
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function pesos(bytes) {
        if (!bytes || bytes <= 0) { return '0 KB'; }
        var mb = bytes / (1024 * 1024);
        if (mb >= 1) { return mb.toFixed(2) + ' MB'; }
        return Math.round(bytes / 1024) + ' KB';
    }

    ready(function () {
        // -------------------------------------------------------------
        // Dropzone (solo en "crear")
        // -------------------------------------------------------------
        var dropzone = document.getElementById('dropzone');
        var input = document.getElementById('archivo_pdf');

        if (dropzone && input) {
            var archivo = document.getElementById('dropArchivo');
            var nombre = document.getElementById('dropNombre');
            var quitar = document.getElementById('dropQuitar');

            var pintar = function () {
                var f = input.files && input.files[0];
                if (f) {
                    dropzone.classList.add('has-file');
                    if (archivo) { archivo.hidden = false; }
                    if (nombre) { nombre.textContent = f.name + ' · ' + pesos(f.size); }
                } else {
                    dropzone.classList.remove('has-file');
                    if (archivo) { archivo.hidden = true; }
                    if (nombre) { nombre.textContent = ''; }
                }
            };

            dropzone.addEventListener('click', function (e) {
                if (e.target.closest('.quitar')) {
                    return;
                }
                input.click();
            });

            dropzone.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    input.click();
                }
            });

            input.addEventListener('change', pintar);

            ['dragenter', 'dragover'].forEach(function (ev) {
                dropzone.addEventListener(ev, function (e) {
                    e.preventDefault();
                    dropzone.classList.add('is-drag');
                });
            });

            ['dragleave', 'drop'].forEach(function (ev) {
                dropzone.addEventListener(ev, function (e) {
                    e.preventDefault();
                    dropzone.classList.remove('is-drag');
                });
            });

            dropzone.addEventListener('drop', function (e) {
                var files = e.dataTransfer && e.dataTransfer.files;
                if (files && files.length) {
                    input.files = files;
                    pintar();
                }
            });

            if (quitar) {
                quitar.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    input.value = '';
                    pintar();
                });
            }

            pintar();
        }

        // -------------------------------------------------------------
        // Vista previa de portada (solo en "editar")
        // -------------------------------------------------------------
        var portada = document.getElementById('ruta_portada');
        var preview = document.getElementById('portadaPreview');
        if (portada && preview) {
            portada.addEventListener('change', function () {
                var f = portada.files && portada.files[0];
                if (f) {
                    preview.src = URL.createObjectURL(f);
                }
            });
        }

        // -------------------------------------------------------------
        // Envío AJAX con Modal de Progreso en Vivo
        // -------------------------------------------------------------
        var form = document.getElementById('revistaForm');
        var submit = document.getElementById('revistaSubmit');
        var submitTxt = document.getElementById('revistaSubmitTxt');
        var uploadModal = document.getElementById('modalProgresoSubida');

        if (form && uploadModal) {
            var progressFill = document.getElementById('uploadProgressFill');
            var progressPercent = document.getElementById('uploadProgressPercent');
            var progressStatus = document.getElementById('uploadProgressStatus');
            var bytesDetail = document.getElementById('uploadBytesDetail');
            var speedDetail = document.getElementById('uploadSpeedDetail');
            var modalTitle = document.getElementById('uploadModalTitle');
            var modalSub = document.getElementById('uploadModalSub');
            var badgeIcon = document.getElementById('uploadModalBadgeIcon');
            var modalBadge = document.getElementById('uploadModalBadge');
            var stepUpload = document.getElementById('stepUpload');
            var stepConvert = document.getElementById('stepConvert');
            var stepFinish = document.getElementById('stepFinish');
            var errorBox = document.getElementById('uploadErrorBox');
            var errorMsg = document.getElementById('uploadErrorMsg');
            var modalFooter = document.getElementById('uploadModalFooter');
            var closeBtn = document.getElementById('uploadModalCloseBtn');

            var pollInterval = null;
            var isUploading = false;

            var warnBeforeUnload = function (e) {
                if (isUploading) {
                    e.preventDefault();
                    e.returnValue = 'La revista se está procesando. ¿Estás seguro de que deseas salir?';
                    return e.returnValue;
                }
            };

            var mostrarError = function (mensaje) {
                isUploading = false;
                window.removeEventListener('beforeunload', warnBeforeUnload);
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }

                if (modalBadge) {
                    modalBadge.className = 'upload-modal-badge badge-error';
                }
                if (badgeIcon) {
                    badgeIcon.className = 'fas fa-triangle-exclamation';
                }
                if (modalTitle) {
                    modalTitle.textContent = 'Ocurrió un inconveniente';
                }
                if (modalSub) {
                    modalSub.textContent = 'Revisa los detalles y vuelve a intentarlo.';
                }
                if (errorBox) {
                    errorBox.hidden = false;
                }
                if (errorMsg) {
                    errorMsg.textContent = mensaje;
                }
                if (modalFooter) {
                    modalFooter.hidden = false;
                }

                if (submit) {
                    submit.disabled = false;
                    submit.style.opacity = '1';
                }
                if (submitTxt) {
                    submitTxt.textContent = 'Subir y Procesar Revista';
                }
            };

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    uploadModal.hidden = true;
                    if (errorBox) { errorBox.hidden = true; }
                    if (modalFooter) { modalFooter.hidden = true; }
                });
            }

            form.addEventListener('submit', function (e) {
                // Validación básica de HTML5
                if (!form.checkValidity()) {
                    return;
                }

                e.preventDefault();

                var fileInput = document.getElementById('archivo_pdf');
                if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                    alert('Por favor selecciona un archivo PDF para la revista.');
                    return;
                }

                var file = fileInput.files[0];
                if (file.size > 50 * 1024 * 1024) {
                    alert('El archivo supera los 50 MB permitidos.');
                    return;
                }

                // Bloquear botón y abrir modal
                isUploading = true;
                window.addEventListener('beforeunload', warnBeforeUnload);

                if (submit) {
                    submit.disabled = true;
                    submit.style.opacity = '.75';
                }
                if (submitTxt) {
                    submitTxt.textContent = 'Enviando…';
                }

                // Resetear estado del modal
                uploadModal.hidden = false;
                if (errorBox) { errorBox.hidden = true; }
                if (modalFooter) { modalFooter.hidden = true; }
                if (modalBadge) { modalBadge.className = 'upload-modal-badge'; }
                if (badgeIcon) { badgeIcon.className = 'fas fa-cloud-arrow-up'; }
                if (modalTitle) { modalTitle.textContent = 'Subiendo revista al servidor'; }
                if (modalSub) { modalSub.textContent = 'Por favor mantén esta ventana abierta mientras se completa el proceso.'; }
                if (progressFill) { progressFill.style.width = '0%'; }
                if (progressPercent) { progressPercent.textContent = '0%'; }
                if (progressStatus) { progressStatus.textContent = 'Enviando archivo PDF...'; }
                if (bytesDetail) { bytesDetail.textContent = '0 MB / ' + pesos(file.size); }
                if (speedDetail) { speedDetail.textContent = ''; }

                if (stepUpload) { stepUpload.className = 'upload-step active'; }
                if (stepConvert) { stepConvert.className = 'upload-step'; }
                if (stepFinish) { stepFinish.className = 'upload-step'; }

                // Iniciar XHR con seguimiento de subida
                var xhr = new XMLHttpRequest();
                var startTime = Date.now();
                var prevLoaded = 0;
                var prevTime = startTime;

                xhr.upload.addEventListener('progress', function (evt) {
                    if (evt.lengthComputable) {
                        var pct = Math.round((evt.loaded / evt.total) * 100);
                        if (progressFill) { progressFill.style.width = pct + '%'; }
                        if (progressPercent) { progressPercent.textContent = pct + '%'; }
                        if (bytesDetail) {
                            bytesDetail.textContent = pesos(evt.loaded) + ' de ' + pesos(evt.total);
                        }

                        // Cálculo de velocidad (MB/s)
                        var now = Date.now();
                        var timeDiff = (now - prevTime) / 1000;
                        if (timeDiff >= 0.5) {
                            var bytesDiff = evt.loaded - prevLoaded;
                            var speed = bytesDiff / timeDiff; // bytes/sec
                            if (speedDetail) {
                                speedDetail.textContent = pesos(speed) + '/s';
                            }
                            prevLoaded = evt.loaded;
                            prevTime = now;
                        }

                        if (pct >= 100) {
                            if (progressStatus) {
                                progressStatus.textContent = 'Archivo recibido. Verificando...';
                            }
                        }
                    }
                });

                xhr.addEventListener('load', function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var resp;
                        try {
                            resp = JSON.parse(xhr.responseText);
                        } catch (err) {
                            mostrarError('Respuesta inesperada del servidor.');
                            return;
                        }

                        if (resp.success && resp.revista_id) {
                            // Fase 2: Conversión de Páginas
                            iniciarFaseConversion(resp.revista_id, resp.redirect_url);
                        } else {
                            mostrarError(resp.error || 'No se pudo iniciar el procesamiento de la revista.');
                        }
                    } else {
                        var msg = 'Error en el servidor (' + xhr.status + ').';
                        try {
                            var errResp = JSON.parse(xhr.responseText);
                            if (errResp.message) { msg = errResp.message; }
                            if (errResp.error) { msg = errResp.error; }
                            if (errResp.errors) {
                                var errs = Object.values(errResp.errors).flat();
                                msg = errs.join(' ');
                            }
                        } catch (e) {}
                        mostrarError(msg);
                    }
                });

                xhr.addEventListener('error', function () {
                    mostrarError('Error de red al intentar subir el archivo. Verifica tu conexión.');
                });

                xhr.addEventListener('abort', function () {
                    mostrarError('Subida cancelada.');
                });

                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                var formData = new FormData(form);
                xhr.send(formData);
            });

            function iniciarFaseConversion(revistaId, redirectUrl) {
                // Actualizar UI a fase de extracción/conversión
                if (stepUpload) { stepUpload.className = 'upload-step done'; }
                if (stepConvert) { stepConvert.className = 'upload-step active'; }

                if (modalBadge) { modalBadge.className = 'upload-modal-badge'; }
                if (badgeIcon) { badgeIcon.className = 'fas fa-gears fa-spin'; }

                if (modalTitle) { modalTitle.textContent = 'Procesando páginas en Alta Resolución'; }
                if (modalSub) { modalSub.textContent = 'Convirtiendo el PDF a formato WebP optimizado y generando portada.'; }
                if (progressStatus) { progressStatus.textContent = 'Preparando páginas...'; }
                if (progressPercent) { progressPercent.textContent = '0%'; }
                if (progressFill) { progressFill.style.width = '0%'; }
                if (bytesDetail) { bytesDetail.textContent = 'Iniciando motor de optimización...'; }
                if (speedDetail) { speedDetail.textContent = ''; }

                // Polling cada 1.2 segundos
                pollInterval = setInterval(function () {
                    fetch('/revista/' + encodeURIComponent(revistaId) + '/progreso', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(function (res) { return res.ok ? res.json() : null; })
                        .then(function (data) {
                            if (!data) return;

                            var total = data.total_paginas || 0;
                            var procesadas = data.paginas_procesadas || 0;
                            var pct = Math.round(data.progreso || 0);

                            if (total > 0) {
                                if (bytesDetail) {
                                    bytesDetail.textContent = 'Página ' + procesadas + ' de ' + total + ' páginas';
                                }
                                if (progressStatus) {
                                    progressStatus.textContent = 'Optimizando página ' + Math.min(procesadas + 1, total) + '...';
                                }
                            } else {
                                if (progressStatus) {
                                    progressStatus.textContent = 'Analizando estructura del documento...';
                                }
                            }

                            if (progressFill) { progressFill.style.width = pct + '%'; }
                            if (progressPercent) { progressPercent.textContent = pct + '%'; }

                            if (data.completado) {
                                clearInterval(pollInterval);
                                pollInterval = null;
                                isUploading = false;
                                window.removeEventListener('beforeunload', warnBeforeUnload);

                                // Fase 3: Completado
                                if (stepConvert) { stepConvert.className = 'upload-step done'; }
                                if (stepFinish) { stepFinish.className = 'upload-step done'; }

                                if (modalBadge) { modalBadge.className = 'upload-modal-badge badge-success'; }
                                if (badgeIcon) { badgeIcon.className = 'fas fa-check'; }

                                if (modalTitle) { modalTitle.textContent = '¡Revista lista!'; }
                                if (modalSub) { modalSub.textContent = 'La revista se ha optimizado y publicado exitosamente.'; }
                                if (progressStatus) { progressStatus.textContent = 'Redirigiendo al visor...'; }
                                if (progressFill) { progressFill.style.width = '100%'; }
                                if (progressPercent) { progressPercent.textContent = '100%'; }

                                setTimeout(function () {
                                    window.location.href = redirectUrl || ('/revistas/' + revistaId);
                                }, 1200);
                            } else if (data.error) {
                                mostrarError('Ocurrió un error al procesar las páginas del PDF.');
                            }
                        })
                        .catch(function () {
                            /* Fallo temporal de red en polling, se reintentará en el siguiente ciclo */
                        });
                }, 1200);
            }
        } else if (form && submit) {
            // Formulario tradicional sin modal (ej. editar)
            form.addEventListener('submit', function () {
                submit.disabled = true;
                submit.style.opacity = '.75';
                if (submitTxt) {
                    submitTxt.textContent = 'Procesando…';
                }
            });
        }

        // -------------------------------------------------------------
        // Polling para barras de progreso estándar en vistas de lista
        // -------------------------------------------------------------
        var barra = document.querySelector('.progress-bar-fill[data-revista-id]');
        if (barra) {
            var id = barra.getAttribute('data-revista-id');
            var actualizar = function () {
                fetch('/revista/' + encodeURIComponent(id) + '/progreso', {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (!data) { return; }
                        var pct = Math.round(data.progreso || 0);
                        barra.style.width = pct + '%';
                        var txt = document.querySelector('.revista-progreso');
                        if (txt) { txt.textContent = pct + '%'; }
                        if (data.completado || data.error) {
                            window.location.reload();
                        }
                    })
                    .catch(function () { /* ignorar */ });
            };
            var timer = window.setInterval(actualizar, 3000);
            actualizar();
            window.addEventListener('beforeunload', function () { window.clearInterval(timer); });
        }
    });
})();
