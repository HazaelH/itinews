/* =====================================================================
   Gestor de fondos del portal (panel ITINEWS)
   - Muestra/oculta campos según el origen (archivo | portada).
   - Muestra/oculta las fechas según la vigencia (siempre|rango|conmemorativa).
   - Selector de portadas en rejilla al elegir "Portada de revista".
   - Vista previa en modal propio del sistema.
   - Confirmaciones con modal propio en lugar del confirm() nativo.
   Sin dependencias.
   ===================================================================== */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    ready(function () {
        initConfirmaciones();
        initGuiaModal();
        initCambiosSinGuardar();
        initFondosIndexToolbar();
        initFormulario();
        initGestorPresets();
    });

    /* ---------- Modal de Guía explicativa "¿Cómo funciona?" ---------- */
    function initGuiaModal() {
        var modal = document.getElementById('fondoGuiaModal');
        if (!modal) return;

        function abrir() {
            modal.hidden = false;
            modal.style.removeProperty('display');
            var closeBtn = modal.querySelector('[data-guia-close]');
            if (closeBtn) closeBtn.focus();
        }

        function cerrar() {
            modal.hidden = true;
            modal.style.setProperty('display', 'none', 'important');
        }

        Array.prototype.slice.call(document.querySelectorAll('[data-fondo-guia-btn]')).forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                abrir();
            });
        });

        Array.prototype.slice.call(modal.querySelectorAll('[data-guia-close]')).forEach(function (el) {
            el.addEventListener('click', cerrar);
        });

        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && !modal.hidden) {
                cerrar();
            }
        });
    }

    /* ---------- Modal de Confirmación de Cambios sin Guardar (Modal Propio ITINEWS) ---------- */
    function initCambiosSinGuardar() {
        var form = document.getElementById('fondoForm');
        var modal = document.getElementById('fondoUnsavedModal');
        if (!form || !modal) return;

        var isDirty = false;
        var isSubmitting = false;
        var targetUrl = null;

        function setDirty() {
            isDirty = true;
        }

        // Exponer función globalmente para que presets y selectores marquen modificaciones
        window.marcarFondoDirty = setDirty;

        // Detectar cualquier cambio en inputs y controles del formulario
        form.addEventListener('input', setDirty);
        form.addEventListener('change', setDirty);

        // Al enviar el formulario, desactivar la advertencia
        form.addEventListener('submit', function () {
            isSubmitting = true;
            isDirty = false;
        });

        function abrir(url) {
            targetUrl = url;
            modal.hidden = false;
            modal.style.removeProperty('display');
            var stayBtn = modal.querySelector('[data-unsaved-stay]');
            if (stayBtn) stayBtn.focus();
        }

        function cerrar() {
            modal.hidden = true;
            modal.style.setProperty('display', 'none', 'important');
            targetUrl = null;
        }

        // Interceptar TODOS los clics de navegación hacia otras páginas
        document.addEventListener('click', function (e) {
            if (!isDirty || isSubmitting) return;

            var link = e.target.closest('a');
            if (!link) return;

            var href = link.getAttribute('href');
            // Ignorar enlaces sin destino, anclas locales o scripts
            if (!href || href === '#' || href.indexOf('javascript:') === 0 || href.charAt(0) === '#') return;
            // Ignorar si abre en una nueva pestaña o es descarga
            if (link.target === '_blank' || link.hasAttribute('download')) return;
            // Ignorar si está dentro de algún modal abierto
            if (link.closest('#fondoPreviewModal') || link.closest('#fondoGuiaModal') || link.closest('#fondoUnsavedModal') || link.closest('#fondoConfirmModal')) return;

            // Bloquear la navegación inmediata y abrir nuestro modal personalizado
            e.preventDefault();
            e.stopPropagation();
            abrir(link.href);
        }, true);

        // Botón "Permanecer aquí" y botón "X"
        Array.prototype.slice.call(modal.querySelectorAll('[data-unsaved-stay]')).forEach(function (btn) {
            btn.addEventListener('click', cerrar);
        });

        // Botón "Salir sin guardar" (reemplaza confirm() nativo)
        var leaveBtn = modal.querySelector('[data-unsaved-leave]');
        if (leaveBtn) {
            leaveBtn.addEventListener('click', function () {
                isDirty = false;
                cerrar();
                if (targetUrl) {
                    window.location.href = targetUrl;
                } else {
                    window.history.back();
                }
            });
        }

        // Cerrar con Escape
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && !modal.hidden) {
                cerrar();
            }
        });
    }

    /* ---------- Confirmaciones con modal propio ---------- */
    function initConfirmaciones() {
        var modal = document.getElementById('fondoConfirmModal');
        if (!modal) {
            return;
        }

        var msgEl = modal.querySelector('[data-confirm-msg]');
        var tituloEl = modal.querySelector('[data-confirm-titulo]');
        var okTxtEl = modal.querySelector('[data-confirm-ok-txt]');
        var okBtn = modal.querySelector('[data-confirm-ok]');
        var formPendiente = null;

        function cerrar() {
            modal.hidden = true;
            formPendiente = null;
        }

        function abrir(form) {
            formPendiente = form;
            if (msgEl) {
                msgEl.textContent = form.getAttribute('data-confirm') || '¿Confirmas esta acción?';
            }
            if (tituloEl && form.hasAttribute('data-confirm-titulo')) {
                tituloEl.textContent = form.getAttribute('data-confirm-titulo');
            }
            if (okTxtEl && form.hasAttribute('data-confirm-ok-txt')) {
                okTxtEl.textContent = form.getAttribute('data-confirm-ok-txt');
            }
            modal.hidden = false;
            okBtn.focus();
        }

        Array.prototype.slice.call(document.querySelectorAll('form[data-confirm]')).forEach(function (form) {
            form.addEventListener('submit', function (ev) {
                if (form.dataset.confirmado === '1') {
                    return;
                }
                ev.preventDefault();
                abrir(form);
            });
        });

        Array.prototype.slice.call(modal.querySelectorAll('[data-confirm-cancel]')).forEach(function (el) {
            el.addEventListener('click', cerrar);
        });

        okBtn.addEventListener('click', function () {
            if (!formPendiente) {
                return;
            }
            formPendiente.dataset.confirmado = '1';
            modal.hidden = true;
            // Evita re-disparar el submit handler
            formPendiente.submit();
        });

        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && !modal.hidden) {
                cerrar();
            }
        });

        // Exponer método programático para confirmar acciones en cualquier modal o componente
        window.fondoConfirmar = function (opciones, onOk) {
            opciones = opciones || {};
            if (msgEl) {
                msgEl.textContent = opciones.mensaje || '¿Confirmas esta acción?';
            }
            if (tituloEl) {
                tituloEl.textContent = opciones.titulo || 'Confirmar acción';
            }
            if (okTxtEl) {
                okTxtEl.textContent = opciones.okTexto || 'Sí, confirmar';
            }

            modal.hidden = false;
            modal.style.removeProperty('display');
            okBtn.focus();

            var completado = false;

            function limpiar() {
                okBtn.removeEventListener('click', handleOk);
                Array.prototype.slice.call(modal.querySelectorAll('[data-confirm-cancel]')).forEach(function (el) {
                    el.removeEventListener('click', handleCancel);
                });
            }

            function handleOk(e) {
                e.preventDefault();
                e.stopPropagation();
                if (completado) return;
                completado = true;
                limpiar();
                cerrar();
                if (typeof onOk === 'function') {
                    onOk();
                }
            }

            function handleCancel() {
                limpiar();
                cerrar();
            }

            okBtn.addEventListener('click', handleOk);
            Array.prototype.slice.call(modal.querySelectorAll('[data-confirm-cancel]')).forEach(function (el) {
                el.addEventListener('click', handleCancel);
            });
        };
    }

    /* ---------- Formulario dinámico + vista previa ---------- */
    function initFormulario() {
        var form = document.getElementById('fondoForm');
        var modal = document.getElementById('fondoPreviewModal');
        if (!form && !modal) {
            return;
        }

        // Elementos DOM clave declarados de forma segura
        var inicio = form ? form.querySelector('[data-fondo-inicio]') : null;
        var fin = form ? form.querySelector('[data-fondo-fin]') : null;
        var recChk = form ? form.querySelector('input[name="recurrente"][type="checkbox"]') : null;
        var tituloInput = form ? form.querySelector('#titulo') : null;
        var activePreviewTab = 'inicio';

        function radioValor(nombre) {
            if (!form) return '';
            var el = form.querySelector('input[name="' + nombre + '"]:checked');
            return el ? el.value : '';
        }

        /* ---------- Habilitar/deshabilitar bloques ocultos ---------- */
        function setBloqueActivo(el, activo) {
            if (!el) return;
            el.hidden = !activo;
            Array.prototype.slice.call(el.querySelectorAll('input, select, textarea, button')).forEach(function (c) {
                if (activo) {
                    c.removeAttribute('disabled');
                } else {
                    c.setAttribute('disabled', 'disabled');
                }
            });
        }

        /* ---------- Origen ---------- */
        function aplicarOrigen() {
            if (!form) return;
            var origen = radioValor('origen');
            Array.prototype.slice.call(form.querySelectorAll('[data-when-origen]')).forEach(function (el) {
                var vals = (el.dataset.whenOrigen || '').split(/\s+/).filter(Boolean);
                setBloqueActivo(el, vals.indexOf(origen) !== -1);
            });

            var hintOrigen = form.querySelector('[data-origen-hint]');
            if (hintOrigen) {
                hintOrigen.textContent = origen === 'portada'
                    ? 'El fondo usará una portada de las revistas activas que tienen imagen.'
                    : 'El fondo usará una imagen que subirás desde tu ordenador.';
            }

            actualizarPreview();
        }

        Array.prototype.slice.call(form.querySelectorAll('[data-fondo-origen]')).forEach(function (r) {
            r.addEventListener('change', function () {
                aplicarOrigen();
                actualizarPreview();
            });
        });

        /* ---------- Ubicación ---------- */
        function aplicarUbicacion() {
            if (!form) return;
            var ubicacion = radioValor('ubicacion');
            var hintUbic = form.querySelector('[data-ubicacion-hint]');
            if (hintUbic) {
                if (ubicacion === 'inicio') {
                    hintUbic.textContent = 'Se mostrará en la página de inicio.';
                } else if (ubicacion === 'revistas') {
                    hintUbic.textContent = 'Se mostrará en la sección de revistas.';
                } else if (ubicacion === 'ambos') {
                    hintUbic.textContent = 'Se mostrará tanto en la página de inicio como en la sección de revistas.';
                } else {
                    hintUbic.textContent = '';
                }
            }
            actualizarPreview();
        }

        Array.prototype.slice.call(form.querySelectorAll('[data-fondo-ubicacion]')).forEach(function (r) {
            r.addEventListener('change', function () {
                aplicarUbicacion();
                actualizarPreview();
            });
        });

        /* ---------- Vigencia ---------- */
        function aplicarVigencia() {
            if (!form) return;
            var v = radioValor('tipo_vigencia');
            Array.prototype.slice.call(form.querySelectorAll('[data-when-vigencia]')).forEach(function (el) {
                var vals = (el.dataset.whenVigencia || '').split(/\s+/).filter(Boolean);
                setBloqueActivo(el, vals.indexOf(v) !== -1);
            });

            var labelInicio = form.querySelector('[data-fecha-inicio-label]');
            if (labelInicio) {
                labelInicio.textContent = v === 'conmemorativa' ? 'Día conmemorativo' : 'Desde';
            }

            var hintVig = form.querySelector('[data-vigencia-hint]');
            if (hintVig) {
                if (v === 'siempre') {
                    hintVig.textContent = 'El fondo se mostrará mientras esté activo (sin fechas).';
                } else if (v === 'rango') {
                    hintVig.textContent = 'El fondo se mostrará entre las fechas elegidas (puede repetirse cada año).';
                } else if (v === 'conmemorativa') {
                    hintVig.textContent = 'Fecha conmemorativa (tiene prioridad sobre rangos).';
                } else {
                    hintVig.textContent = '';
                }
            }

            var presetConm = form.querySelector('[data-preset-conmemorativa]');
            var presetRango = form.querySelector('[data-preset-rango]');
            if (presetConm) presetConm.hidden = (v !== 'conmemorativa');
            if (presetRango) presetRango.hidden = (v !== 'rango');

            validarFechas();
            actualizarPreview();
        }

        Array.prototype.slice.call(form.querySelectorAll('[data-fondo-vigencia]')).forEach(function (r) {
            r.addEventListener('change', function () {
                aplicarVigencia();
                actualizarPreview();
            });
        });

        /* ---------- Helpers para Presets y Fechas ---------- */
        function marcarPresetActivo(btn) {
            Array.prototype.slice.call(form.querySelectorAll('.fondo-preset')).forEach(function (el) {
                el.classList.remove('is-active');
            });
            if (btn) {
                btn.classList.add('is-active');
            }
        }

        function autocompletarTitulo(nombre) {
            if (tituloInput && !tituloInput.value.trim() && nombre) {
                tituloInput.value = nombre;
            }
        }

        function validarFechas() {
            if (!inicio || !fin) return;
            var v = radioValor('tipo_vigencia');
            if (v === 'rango') {
                if (inicio.value) {
                    fin.min = inicio.value;
                    if (fin.value && fin.value < inicio.value) {
                        fin.value = inicio.value;
                    }
                } else {
                    fin.removeAttribute('min');
                }
            } else if (v === 'conmemorativa') {
                if (fin && inicio.value) {
                    fin.value = inicio.value;
                }
            }
        }

        /* ---------- Sincronización manual de fechas ---------- */
        if (inicio) {
            inicio.addEventListener('change', function () {
                if (radioValor('tipo_vigencia') === 'conmemorativa' && fin) {
                    fin.value = inicio.value;
                }
                validarFechas();
                marcarPresetActivo(null);
                actualizarPreview();
                if (window.actualizarCoincidenciasFondos) window.actualizarCoincidenciasFondos();
            });
            inicio.addEventListener('input', function () {
                validarFechas();
                actualizarPreview();
                if (window.actualizarCoincidenciasFondos) window.actualizarCoincidenciasFondos();
            });
        }
        if (fin) {
            fin.addEventListener('change', function () {
                validarFechas();
                marcarPresetActivo(null);
                actualizarPreview();
                if (window.actualizarCoincidenciasFondos) window.actualizarCoincidenciasFondos();
            });
            fin.addEventListener('input', function () {
                actualizarPreview();
                if (window.actualizarCoincidenciasFondos) window.actualizarCoincidenciasFondos();
            });
        }

        /* ---------- Vinculación dinámica de presets (conmemorativas y rangos) ---------- */
        function vincularPresetButtons() {
            // Presets conmemorativos
            Array.prototype.slice.call(form.querySelectorAll('[data-preset-conm]')).forEach(function (btn) {
                btn.onclick = function (e) {
                    e.preventDefault();
                    var dia = btn.getAttribute('data-dia');
                    var nombre = btn.getAttribute('data-nombre') || btn.textContent.trim();
                    if (!dia) return;

                    var y = new Date().getFullYear();
                    var mm = dia.slice(0, 2);
                    var dd = dia.slice(3, 5);
                    var val = y + '-' + mm + '-' + dd;

                    // Asegurar modo conmemorativo
                    var vigRadio = form.querySelector('input[name="tipo_vigencia"][value="conmemorativa"]');
                    if (vigRadio && !vigRadio.checked) {
                        vigRadio.checked = true;
                        aplicarVigencia();
                    }

                    if (inicio) {
                        inicio.value = val;
                    }
                    if (fin) {
                        fin.value = val;
                    }
                    if (recChk) {
                        recChk.checked = true;
                    }

                    marcarPresetActivo(btn);
                    autocompletarTitulo(nombre);
                    if (window.marcarFondoDirty) window.marcarFondoDirty();
                    actualizarPreview();
                    if (window.actualizarCoincidenciasFondos) window.actualizarCoincidenciasFondos();
                };
            });

            // Presets rangos (estaciones y meses)
            Array.prototype.slice.call(form.querySelectorAll('[data-preset-rango]')).forEach(function (btn) {
                btn.onclick = function (e) {
                    e.preventDefault();
                    var desde = btn.getAttribute('data-desde');
                    var hasta = btn.getAttribute('data-hasta');
                    var nombre = btn.getAttribute('data-nombre') || btn.textContent.trim();
                    if (!desde || !hasta) return;

                    var y = new Date().getFullYear();
                    var dmm = desde.slice(0, 2), ddd = desde.slice(3, 5);
                    var hmm = hasta.slice(0, 2), hdd = hasta.slice(3, 5);

                    // Si el rango cruza de año (ej. Invierno: dic a mar)
                    var yFin = y;
                    if (parseInt(hmm, 10) < parseInt(dmm, 10)) {
                        yFin = y + 1;
                    }

                    // Asegurar modo rango
                    var vigRadio = form.querySelector('input[name="tipo_vigencia"][value="rango"]');
                    if (vigRadio && !vigRadio.checked) {
                        vigRadio.checked = true;
                        aplicarVigencia();
                    }

                    if (inicio) {
                        inicio.value = y + '-' + dmm + '-' + ddd;
                    }
                    if (fin) {
                        fin.value = yFin + '-' + hmm + '-' + hdd;
                    }
                    validarFechas();

                    marcarPresetActivo(btn);
                    autocompletarTitulo(nombre);
                    if (window.marcarFondoDirty) window.marcarFondoDirty();
                    actualizarPreview();
                    if (window.actualizarCoincidenciasFondos) window.actualizarCoincidenciasFondos();
                };
            });
        }
        window.vincularPresetButtons = vincularPresetButtons;

        /* ---------- Portadas de revista ---------- */
        Array.prototype.slice.call(form.querySelectorAll('input[name="revista_id"]')).forEach(function (r) {
            r.addEventListener('change', function () {
                Array.prototype.slice.call(form.querySelectorAll('.portada-opcion')).forEach(function (po) {
                    var radio = po.querySelector('input[name="revista_id"]');
                    po.classList.toggle('is-selected', radio && radio.checked);
                });
                actualizarPreview();
            });
        });

        /* ---------- Archivo subido ---------- */
        var file = form.querySelector('[data-fondo-file]');
        if (file) {
            file.addEventListener('change', function () {
                var f = file.files && file.files[0];
                if (f && /^image\//.test(f.type)) {
                    var preview = form.querySelector('[data-fondo-preview]');
                    if (preview) {
                        var img = preview.querySelector('img');
                        if (!img) {
                            img = document.createElement('img');
                            img.alt = 'Vista previa del fondo';
                            preview.appendChild(img);
                        }
                        if (img._objUrl) {
                            URL.revokeObjectURL(img._objUrl);
                            img._objUrl = null;
                        }
                        if (file._objUrl) {
                            URL.revokeObjectURL(file._objUrl);
                            file._objUrl = null;
                        }
                        file._objUrl = URL.createObjectURL(f);
                        img.src = file._objUrl;
                        preview.classList.add('is-visible');
                    }
                }
                actualizarPreview();
            });
        }

        /* ---------- Pestañas de Vista Previa (Inicio vs Revistas) ---------- */
        function setPreviewTab(tab) {
            activePreviewTab = tab;
            if (!modal) return;

            Array.prototype.slice.call(modal.querySelectorAll('[data-preview-tab]')).forEach(function (t) {
                t.classList.toggle('is-active', t.dataset.previewTab === tab);
            });

            Array.prototype.slice.call(modal.querySelectorAll('[data-preview-stage]')).forEach(function (s) {
                var activo = (s.dataset.previewStage === tab);
                s.hidden = !activo;
                s.style.setProperty('display', activo ? 'flex' : 'none', 'important');
            });

            var subHint = modal.querySelector('[data-preview-sub-hint]');
            if (subHint) {
                subHint.textContent = (tab === 'inicio')
                    ? 'Simulación de la página de Inicio / Bienvenida'
                    : 'Simulación del Portal de Revistas con barra lateral';
            }
        }

        if (modal) {
            Array.prototype.slice.call(modal.querySelectorAll('[data-preview-tab]')).forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    setPreviewTab(btn.dataset.previewTab);
                });
            });
        }

        function sincronizarTabsConUbicacion() {
            if (!modal) return;
            var ub = radioValor('ubicacion');
            var tabsContainer = modal.querySelector('[data-preview-tabs]');

            if (ub === 'inicio') {
                if (tabsContainer) {
                    tabsContainer.hidden = true;
                    tabsContainer.style.setProperty('display', 'none', 'important');
                }
                setPreviewTab('inicio');
            } else if (ub === 'revistas') {
                if (tabsContainer) {
                    tabsContainer.hidden = true;
                    tabsContainer.style.setProperty('display', 'none', 'important');
                }
                setPreviewTab('revistas');
            } else { // 'ambos'
                if (tabsContainer) {
                    tabsContainer.hidden = false;
                    tabsContainer.style.setProperty('display', 'flex', 'important');
                }
                setPreviewTab(activePreviewTab || 'inicio');
            }
        }

        /* ---------- Abrir y cerrar Modal de Vista Previa ---------- */
        var btnPrev = form.querySelector('[data-fondo-preview-btn]');
        if (btnPrev && modal) {
            btnPrev.addEventListener('click', function () {
                actualizarPreview();
                modal.hidden = false;
                modal.style.removeProperty('display');
                var closeBtn = modal.querySelector('[data-fondo-preview-close]');
                if (closeBtn) closeBtn.focus();
            });
        }

        if (modal) {
            Array.prototype.slice.call(modal.querySelectorAll('[data-fondo-preview-close]')).forEach(function (el) {
                el.addEventListener('click', function () {
                    modal.hidden = true;
                    modal.style.setProperty('display', 'none', 'important');
                });
            });
            document.addEventListener('keydown', function (ev) {
                if (ev.key === 'Escape' && !modal.hidden) {
                    modal.hidden = true;
                    modal.style.setProperty('display', 'none', 'important');
                }
            });
        }

        /* ---------- Obtener imagen actual (archivo o portada) ---------- */
        function imagenActual() {
            if (!form) return '';
            var origen = radioValor('origen');
            if (origen === 'archivo') {
                var fileInput = form.querySelector('[data-fondo-file]');
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    if (!fileInput._objUrl) {
                        fileInput._objUrl = URL.createObjectURL(fileInput.files[0]);
                    }
                    return fileInput._objUrl;
                }
                var prevImg = form.querySelector('[data-fondo-preview] img');
                if (prevImg && (prevImg.currentSrc || prevImg.src)) {
                    return prevImg.currentSrc || prevImg.src;
                }
                return '';
            }
            if (origen === 'portada') {
                var r = form.querySelector('input[name="revista_id"]:checked');
                if (r) {
                    var thumb = r.closest('.portada-opcion');
                    var img = thumb ? thumb.querySelector('img') : null;
                    return (img && (img.currentSrc || img.src)) ? (img.currentSrc || img.src) : (r.dataset.url || '');
                }
                return '';
            }
            return '';
        }

        function textoVigencia() {
            if (!form) return 'Siempre activo';
            var v = radioValor('tipo_vigencia');
            var fiVal = inicio ? inicio.value : '';
            var ffVal = fin ? fin.value : '';

            if (v === 'siempre') {
                return 'Siempre activo';
            }
            if (v === 'conmemorativa') {
                var dia = fiVal || ffVal;
                var base = dia ? dia.replace(/-/g, '/') : 'Día por definir';
                return 'Conmemorativa: ' + base + ' (cada año)';
            }
            if (v === 'rango') {
                var di = fiVal ? fiVal.replace(/-/g, '/') : '?';
                var df = ffVal ? ffVal.replace(/-/g, '/') : '?';
                var rFlag = recChk ? recChk.checked : false;
                return 'Rango: ' + di + ' → ' + df + (rFlag ? ' (cada año)' : '');
            }
            return 'Siempre activo';
        }

        function ubicacionTexto() {
            var ub = radioValor('ubicacion');
            if (ub === 'inicio') return 'Inicio';
            if (ub === 'revistas') return 'Revistas';
            if (ub === 'ambos') return 'Inicio y Revistas';
            return 'Inicio y Revistas';
        }

        function tituloActual() {
            if (!form || !tituloInput) return 'Sin título';
            return tituloInput.value.trim() ? tituloInput.value.trim() : 'Sin título';
        }

        function actualizarPreview() {
            if (!modal || !form) return;
            var bg = modal.querySelector('[data-preview-bg]');
            var overlay = modal.querySelector('[data-preview-overlay]');
            var vacio = modal.querySelector('[data-preview-vacio]');
            var tit = modal.querySelector('[data-preview-titulo]');
            var ub = modal.querySelector('[data-preview-ubicacion]');
            var vig = modal.querySelector('[data-preview-vigencia]');
            var hint = modal.querySelector('[data-preview-hint]');

            var imgUrl = imagenActual();
            if (bg) {
                if (imgUrl) {
                    bg.style.backgroundImage = 'url("' + imgUrl + '")';
                    bg.style.opacity = '1';
                } else {
                    bg.style.backgroundImage = 'none';
                    bg.style.opacity = '0';
                }
            }

            if (tit) tit.textContent = tituloActual();
            if (ub) ub.textContent = ubicacionTexto();
            if (vig) vig.textContent = textoVigencia();

            var tiene = !!imgUrl;
            if (vacio) {
                vacio.hidden = tiene;
                vacio.style.setProperty('display', tiene ? 'none' : 'flex', 'important');
            }
            if (overlay) {
                overlay.hidden = !tiene;
                overlay.style.setProperty('display', tiene ? 'flex' : 'none', 'important');
            }

            // Actualizar simulaciones
            sincronizarTabsConUbicacion();

            if (hint) {
                var ubt = ubicacionTexto();
                hint.textContent = 'El fondo está asignado para: ' + ubt + '. Puedes alternar la vista arriba para comprobar la experiencia visual.';
            }
        }

        if (tituloInput) {
            tituloInput.addEventListener('input', actualizarPreview);
        }

        /* ---------- Helper de escape de cadenas HTML ---------- */
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        /* ---------- Selector intuitivo de Posición / Turno en el carrusel ---------- */
        function initOrdenSelector() {
            var ordenInput = form.querySelector('#orden');
            if (!ordenInput) return;

            var btnMenos = form.querySelector('#btnOrdenMenos');
            var btnMas = form.querySelector('#btnOrdenMas');
            var pills = Array.prototype.slice.call(form.querySelectorAll('.orden-pill'));
            var descSpan = form.querySelector('#ordenDescDinamica');

            function actualizarTexto(val) {
                if (!descSpan) return;
                if (val === 1) {
                    descSpan.innerHTML = 'Esta imagen será la <strong>primera (Turno 1)</strong> que verán los usuarios al ingresar al portal.';
                } else if (val === 2) {
                    descSpan.innerHTML = 'Esta imagen entrará en <strong>segundo lugar (Turno 2)</strong> en la rotación del carrusel.';
                } else if (val === 3) {
                    descSpan.innerHTML = 'Esta imagen entrará en <strong>tercer lugar (Turno 3)</strong> en la rotación del carrusel.';
                } else {
                    descSpan.innerHTML = 'Esta imagen rotará en la <strong>posición #' + val + '</strong> del carrusel.';
                }
            }

            function sincronizarPills(val) {
                pills.forEach(function (pill) {
                    var pVal = parseInt(pill.getAttribute('data-orden-val'), 10);
                    if (pVal === 4) {
                        pill.classList.toggle('is-active', val >= 4);
                    } else {
                        pill.classList.toggle('is-active', pVal === val);
                    }
                });
            }

            function setOrden(val, userAction) {
                val = parseInt(val, 10);
                if (isNaN(val) || val < 1) val = 1;
                if (val > 99) val = 99;
                ordenInput.value = val;
                sincronizarPills(val);
                actualizarTexto(val);
                if (userAction && window.marcarFondoDirty) {
                    window.marcarFondoDirty();
                }
            }

            if (btnMenos) {
                btnMenos.addEventListener('click', function (e) {
                    e.preventDefault();
                    var actual = parseInt(ordenInput.value, 10) || 1;
                    if (actual > 1) {
                        setOrden(actual - 1, true);
                    }
                });
            }

            if (btnMas) {
                btnMas.addEventListener('click', function (e) {
                    e.preventDefault();
                    var actual = parseInt(ordenInput.value, 10) || 1;
                    setOrden(actual + 1, true);
                });
            }

            pills.forEach(function (pill) {
                pill.addEventListener('click', function (e) {
                    e.preventDefault();
                    var pVal = parseInt(pill.getAttribute('data-orden-val'), 10) || 1;
                    setOrden(pVal, true);
                });
            });

            ordenInput.addEventListener('input', function () {
                var val = parseInt(ordenInput.value, 10) || 1;
                sincronizarPills(val);
                actualizarTexto(val);
            });

            ordenInput.addEventListener('change', function () {
                setOrden(ordenInput.value, true);
            });

            // Inicializar estado inicial
            setOrden(ordenInput.value || 1, false);
        }

        /* ---------- Detección en tiempo real de fondos ya existentes en el mismo periodo ---------- */
        function initCoincidenciasDetector() {
            var avisoBox = document.getElementById('avisoCoincidenciaFondo');
            var listaUl = document.getElementById('listaCoincidencias');
            var scriptData = document.getElementById('fondosExistentesData');
            if (!avisoBox || !listaUl || !scriptData) return;

            var fondosExistentes = [];
            try {
                fondosExistentes = JSON.parse(scriptData.textContent || '[]');
            } catch (err) {
                fondosExistentes = [];
            }

            function mmddInside(mmdd, startMmdd, endMmdd) {
                if (!mmdd || !startMmdd || !endMmdd) return false;
                if (startMmdd <= endMmdd) {
                    return mmdd >= startMmdd && mmdd <= endMmdd;
                } else {
                    return mmdd >= startMmdd || mmdd <= endMmdd;
                }
            }

            function rangesOverlap(s1, e1, s2, e2) {
                if (!s1 || !e1 || !s2 || !e2) return false;
                return mmddInside(s1, s2, e2) || mmddInside(e1, s2, e2) || mmddInside(s2, s1, e1) || mmddInside(e2, s1, e1);
            }

            function extraerMmDd(dateStr) {
                if (!dateStr || dateStr.length < 10) return '';
                return dateStr.slice(5, 10); // "YYYY-MM-DD" -> "MM-DD"
            }

            function actualizarCoincidencias() {
                var chkActivo = form.querySelector('input[name="activo"]');
                var estaActivo = chkActivo ? chkActivo.checked : true;
                if (!estaActivo) {
                    avisoBox.hidden = true;
                    avisoBox.style.setProperty('display', 'none', 'important');
                    return;
                }

                var tipoVig = radioValor('tipo_vigencia');
                var ubic = radioValor('ubicacion');
                var fiVal = inicio ? inicio.value : '';
                var ffVal = fin ? fin.value : '';

                var fiMmDd = extraerMmDd(fiVal);
                var ffMmDd = extraerMmDd(ffVal);

                var coincidencias = fondosExistentes.filter(function (f) {
                    // 1. Coincidencia de ubicación
                    var matchUbic = false;
                    if (ubic === 'ambos' || f.ubicacion === 'ambos') {
                        matchUbic = true;
                    } else if (ubic === f.ubicacion) {
                        matchUbic = true;
                    }
                    if (!matchUbic) return false;

                    // 2. Coincidencia de vigencia y fechas
                    if (tipoVig === 'siempre') {
                        return f.tipo_vigencia === 'siempre';
                    }

                    if (tipoVig === 'conmemorativa') {
                        if (!fiMmDd) return false;
                        if (f.tipo_vigencia === 'conmemorativa') {
                            var fDia = f.dia || extraerMmDd(f.fecha_inicio);
                            return fDia === fiMmDd;
                        }
                        if (f.tipo_vigencia === 'rango') {
                            var fDesde = f.desde || extraerMmDd(f.fecha_inicio);
                            var fHasta = f.hasta || extraerMmDd(f.fecha_fin);
                            return mmddInside(fiMmDd, fDesde, fHasta);
                        }
                    }

                    if (tipoVig === 'rango') {
                        if (!fiMmDd || !ffMmDd) return false;
                        if (f.tipo_vigencia === 'rango') {
                            var fDesdeR = f.desde || extraerMmDd(f.fecha_inicio);
                            var fHastaR = f.hasta || extraerMmDd(f.fecha_fin);
                            return rangesOverlap(fiMmDd, ffMmDd, fDesdeR, fHastaR);
                        }
                        if (f.tipo_vigencia === 'conmemorativa') {
                            var fDiaC = f.dia || extraerMmDd(f.fecha_inicio);
                            return mmddInside(fDiaC, fiMmDd, ffMmDd);
                        }
                    }

                    return false;
                });

                if (coincidencias.length > 0) {
                    listaUl.innerHTML = '';
                    coincidencias.forEach(function (f) {
                        var li = document.createElement('li');
                        var ubicTexto = f.ubicacion === 'ambos' ? 'Inicio y Revistas' : (f.ubicacion === 'inicio' ? 'Inicio' : 'Revistas');
                        var vigTexto = f.tipo_vigencia === 'conmemorativa' ? 'Fecha conmemorativa' : (f.tipo_vigencia === 'rango' ? 'Rango estacional' : 'Siempre activo');
                        var turno = f.orden || 1;
                        li.innerHTML = '<strong>' + escapeHtml(f.titulo) + '</strong> (' + ubicTexto + ' &bull; ' + vigTexto + ') &mdash; <span class="badge-coincidencia-turno">Tiene asignado el Turno #' + turno + '</span>';
                        listaUl.appendChild(li);
                    });
                    avisoBox.hidden = false;
                    avisoBox.style.removeProperty('display');
                } else {
                    avisoBox.hidden = true;
                    avisoBox.style.setProperty('display', 'none', 'important');
                }
            }

            window.actualizarCoincidenciasFondos = actualizarCoincidencias;

            // Escuchar cambios relevantes
            if (inicio) inicio.addEventListener('change', actualizarCoincidencias);
            if (fin) fin.addEventListener('change', actualizarCoincidencias);
            var chkActivo = form.querySelector('input[name="activo"]');
            if (chkActivo) chkActivo.addEventListener('change', actualizarCoincidencias);
            Array.prototype.slice.call(form.querySelectorAll('[data-fondo-vigencia]')).forEach(function (r) {
                r.addEventListener('change', actualizarCoincidencias);
            });
            Array.prototype.slice.call(form.querySelectorAll('[data-fondo-ubicacion]')).forEach(function (r) {
                r.addEventListener('change', actualizarCoincidencias);
            });

            // Ejecutar al cargar
            actualizarCoincidencias();
        }

        /* ---------- Inicialización ---------- */
        aplicarOrigen();
        aplicarVigencia();
        aplicarUbicacion();
        actualizarPreview();
        vincularPresetButtons();
        initOrdenSelector();
        initCoincidenciasDetector();

        // Marcadores visuales para portadas seleccionadas al cargar
        Array.prototype.slice.call(form.querySelectorAll('.portada-opcion')).forEach(function (po) {
            var radio = po.querySelector('input[name="revista_id"]');
            po.classList.toggle('is-selected', radio && radio.checked);
        });
    }

    /* ---------- Barra de Búsqueda, Filtrado y Ordenación (admin/fondos) ---------- */
    function initFondosIndexToolbar() {
        var toolbar = document.querySelector('.fondos-toolbar');
        var grid = document.querySelector('.fondos-grid');
        if (!toolbar || !grid) return;

        var inputBusqueda = document.getElementById('filtroFondoBusqueda');
        var clearBtn = document.getElementById('limpiarBusquedaBtn');
        var selectUbicacion = document.getElementById('filtroFondoUbicacion');
        var selectVigencia = document.getElementById('filtroFondoVigencia');
        var selectEstado = document.getElementById('filtroFondoEstado');
        var selectOrden = document.getElementById('filtroFondoOrden');
        var btnRestablecer = document.getElementById('btnRestablecerFiltros');
        var btnLimpiarVacio = document.getElementById('btnLimpiarFiltrosVacio');
        var counterEl = document.getElementById('fondosCounter');
        var noResultados = document.getElementById('fondosNoResultados');

        var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-fondo-item]'));
        var total = cards.length;

        function normalizarTexto(txt) {
            return (txt || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
        }

        function aplicarFiltrosYOrden() {
            var q = normalizarTexto(inputBusqueda ? inputBusqueda.value : '');
            var ubic = selectUbicacion ? selectUbicacion.value : 'todas';
            var vig = selectVigencia ? selectVigencia.value : 'todas';
            var est = selectEstado ? selectEstado.value : 'todos';
            var orden = selectOrden ? selectOrden.value : 'recientes';

            if (clearBtn) {
                clearBtn.hidden = !q;
            }

            var visibles = [];

            cards.forEach(function (card) {
                var titulo = normalizarTexto(card.getAttribute('data-titulo') || '');
                var cardUbic = card.getAttribute('data-ubicacion') || '';
                var cardVig = card.getAttribute('data-vigencia') || '';
                var cardActivo = card.getAttribute('data-activo') === '1';
                var cardHoy = card.getAttribute('data-hoy') === '1';

                var matchQ = !q || titulo.indexOf(q) !== -1;
                var matchUbic = (ubic === 'todas') || (cardUbic === ubic);
                var matchVig = (vig === 'todas') || (cardVig === vig);
                var matchEst = true;

                if (est === 'hoy') {
                    matchEst = cardHoy;
                } else if (est === 'activos') {
                    matchEst = cardActivo;
                } else if (est === 'inactivos') {
                    matchEst = !cardActivo;
                }

                if (matchQ && matchUbic && matchVig && matchEst) {
                    card.hidden = false;
                    card.style.removeProperty('display');
                    visibles.push(card);
                } else {
                    card.hidden = true;
                    card.style.setProperty('display', 'none', 'important');
                }
            });

            // Ordenación de tarjetas
            visibles.sort(function (a, b) {
                var idA = parseInt(a.getAttribute('data-id'), 10) || 0;
                var idB = parseInt(b.getAttribute('data-id'), 10) || 0;
                var ordA = parseInt(a.getAttribute('data-orden'), 10) || 0;
                var ordB = parseInt(b.getAttribute('data-orden'), 10) || 0;
                var titA = a.getAttribute('data-titulo') || '';
                var titB = b.getAttribute('data-titulo') || '';

                if (orden === 'recientes') {
                    return idB - idA;
                } else if (orden === 'antiguos') {
                    return idA - idB;
                } else if (orden === 'orden') {
                    if (ordA !== ordB) return ordA - ordB;
                    return idB - idA;
                } else if (orden === 'az') {
                    return titA.localeCompare(titB);
                } else if (orden === 'za') {
                    return titB.localeCompare(titA);
                }
                return 0;
            });

            // Re-anexar tarjetas en el orden establecido
            visibles.forEach(function (card) {
                grid.appendChild(card);
            });

            // Actualizar contador
            if (counterEl) {
                counterEl.innerHTML = 'Mostrando <strong>' + visibles.length + '</strong> de ' + total + ' fondos';
            }

            // Mensaje de sin resultados
            if (noResultados) {
                if (visibles.length === 0 && total > 0) {
                    noResultados.hidden = false;
                    noResultados.style.removeProperty('display');
                } else {
                    noResultados.hidden = true;
                    noResultados.style.setProperty('display', 'none', 'important');
                }
            }
        }

        function restablecer() {
            if (inputBusqueda) inputBusqueda.value = '';
            if (selectUbicacion) selectUbicacion.value = 'todas';
            if (selectVigencia) selectVigencia.value = 'todas';
            if (selectEstado) selectEstado.value = 'todos';
            if (selectOrden) selectOrden.value = 'recientes';
            aplicarFiltrosYOrden();
            if (inputBusqueda) inputBusqueda.focus();
        }

        if (inputBusqueda) {
            inputBusqueda.addEventListener('input', aplicarFiltrosYOrden);
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                inputBusqueda.value = '';
                aplicarFiltrosYOrden();
                inputBusqueda.focus();
            });
        }
        if (selectUbicacion) selectUbicacion.addEventListener('change', aplicarFiltrosYOrden);
        if (selectVigencia) selectVigencia.addEventListener('change', aplicarFiltrosYOrden);
        if (selectEstado) selectEstado.addEventListener('change', aplicarFiltrosYOrden);
        if (selectOrden) selectOrden.addEventListener('change', aplicarFiltrosYOrden);
        if (btnRestablecer) btnRestablecer.addEventListener('click', restablecer);
        if (btnLimpiarVacio) btnLimpiarVacio.addEventListener('click', restablecer);
    }

    /* ---------- Modal Gestor de Presets (Crear, Editar, Eliminar opciones de la cinta) ---------- */
    function initGestorPresets() {
        var modal = document.getElementById('fondoPresetGestorModal');
        if (!modal) return;

        var tipoActual = 'conmemorativa';
        var presetsData = { conmemorativa: [], rango: [] };

        // Elementos del modal
        var labelTipo = modal.querySelector('[data-gestor-tipo-label]');
        var modoSpan = modal.querySelector('[data-preset-form-modo]');
        var inputId = document.getElementById('presetId');
        var inputTipo = document.getElementById('presetTipo');
        var inputNombre = document.getElementById('presetNombre');

        var campoConm = modal.querySelector('[data-campo-preset="conmemorativa"]');
        var selectDiaDia = document.getElementById('presetDiaDia');
        var selectDiaMes = document.getElementById('presetDiaMes');

        var campoRango = modal.querySelector('[data-campo-preset="rango"]');
        var selectDesdeDia = document.getElementById('presetDesdeDia');
        var selectDesdeMes = document.getElementById('presetDesdeMes');
        var selectHastaDia = document.getElementById('presetHastaDia');
        var selectHastaMes = document.getElementById('presetHastaMes');

        var btnGuardar = document.getElementById('btnGuardarPresetItem');
        var btnCancelar = document.getElementById('btnCancelarEdicionPreset');
        var tbody = document.getElementById('tablaPresetsBody');

        function mesesNombres(numStr) {
            var m = {
                '01': 'Ene', '02': 'Feb', '03': 'Mar', '04': 'Abr',
                '05': 'May', '06': 'Jun', '07': 'Jul', '08': 'Ago',
                '09': 'Sep', '10': 'Oct', '11': 'Nov', '12': 'Dic'
            };
            return m[numStr] || numStr;
        }

        function mesesCompletos(numStr) {
            var m = {
                '01': 'Enero', '02': 'Febrero', '03': 'Marzo', '04': 'Abril',
                '05': 'Mayo', '06': 'Junio', '07': 'Julio', '08': 'Agosto',
                '09': 'Septiembre', '10': 'Octubre', '11': 'Noviembre', '12': 'Diciembre'
            };
            return m[numStr] || numStr;
        }

        function getCsrf() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            if (meta && meta.content) return meta.content;
            var inp = document.querySelector('input[name="_token"]');
            if (inp && inp.value) return inp.value;
            return '';
        }

        function escapeTxt(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Cargar datos iniciales desde los chips del DOM
        function leerPresetsDesdeDom() {
            presetsData.conmemorativa = [];
            var chipsC = document.querySelectorAll('#chipsConmemorativa [data-preset-conm]');
            Array.prototype.slice.call(chipsC).forEach(function (btn) {
                presetsData.conmemorativa.push({
                    id: parseInt(btn.getAttribute('data-id'), 10),
                    tipo: 'conmemorativa',
                    nombre: btn.getAttribute('data-nombre') || btn.textContent.trim(),
                    dia: btn.getAttribute('data-dia')
                });
            });

            presetsData.rango = [];
            var chipsR = document.querySelectorAll('#chipsRango [data-preset-rango]');
            Array.prototype.slice.call(chipsR).forEach(function (btn) {
                presetsData.rango.push({
                    id: parseInt(btn.getAttribute('data-id'), 10),
                    tipo: 'rango',
                    nombre: btn.getAttribute('data-nombre') || btn.textContent.trim(),
                    desde: btn.getAttribute('data-desde'),
                    hasta: btn.getAttribute('data-hasta')
                });
            });
        }
        leerPresetsDesdeDom();

        function abrir(tipo) {
            tipoActual = (tipo === 'rango') ? 'rango' : 'conmemorativa';
            if (labelTipo) {
                labelTipo.textContent = (tipoActual === 'rango')
                    ? 'Gestionar Rangos Predeterminados'
                    : 'Gestionar Fechas Conmemorativas';
            }

            if (inputTipo) inputTipo.value = tipoActual;
            if (campoConm) campoConm.hidden = (tipoActual !== 'conmemorativa');
            if (campoRango) campoRango.hidden = (tipoActual !== 'rango');

            resetForm();
            renderTabla();

            modal.hidden = false;
            modal.style.removeProperty('display');
            if (inputNombre) inputNombre.focus();
        }

        function cerrar() {
            modal.hidden = true;
            modal.style.setProperty('display', 'none', 'important');
        }

        function resetForm() {
            if (inputId) inputId.value = '';
            if (inputNombre) inputNombre.value = '';
            if (tipoActual === 'conmemorativa') {
                if (selectDiaDia) selectDiaDia.value = '15';
                if (selectDiaMes) selectDiaMes.value = '09';
                if (modoSpan) modoSpan.textContent = 'Nueva fecha conmemorativa';
            } else {
                if (selectDesdeDia) selectDesdeDia.value = '01';
                if (selectDesdeMes) selectDesdeMes.value = '01';
                if (selectHastaDia) selectHastaDia.value = '31';
                if (selectHastaMes) selectHastaMes.value = '01';
                if (modoSpan) modoSpan.textContent = 'Nuevo rango predeterminado';
            }
            if (btnCancelar) btnCancelar.hidden = true;
            if (btnGuardar) btnGuardar.innerHTML = '<i class="fas fa-floppy-disk" aria-hidden="true"></i> Guardar';
        }

        function renderTabla() {
            if (!tbody) return;
            tbody.innerHTML = '';
            var lista = presetsData[tipoActual] || [];

            if (lista.length === 0) {
                var trVacio = document.createElement('tr');
                trVacio.innerHTML = '<td colspan="3" style="text-align:center; padding:1.5rem; color:#64748b;">No hay opciones creadas todavía en esta sección.</td>';
                tbody.appendChild(trVacio);
                return;
            }

            lista.forEach(function (preset) {
                var tr = document.createElement('tr');
                var fechaHtml = '';

                if (preset.tipo === 'conmemorativa') {
                    var mm = (preset.dia || '').slice(0, 2);
                    var dd = (preset.dia || '').slice(3, 5);
                    fechaHtml = '<span class="preset-tag-date"><i class="fas fa-star" aria-hidden="true"></i> ' + parseInt(dd, 10) + ' de ' + mesesCompletos(mm) + '</span>';
                } else {
                    var dmm = (preset.desde || '').slice(0, 2), ddd = (preset.desde || '').slice(3, 5);
                    var hmm = (preset.hasta || '').slice(0, 2), hdd = (preset.hasta || '').slice(3, 5);
                    fechaHtml = '<span class="preset-tag-range"><i class="fas fa-calendar-range" aria-hidden="true"></i> ' + parseInt(ddd, 10) + ' ' + mesesNombres(dmm) + ' &rarr; ' + parseInt(hdd, 10) + ' ' + mesesNombres(hmm) + '</span>';
                }

                tr.innerHTML = '<td><strong>' + escapeTxt(preset.nombre) + '</strong></td>' +
                               '<td>' + fechaHtml + '</td>' +
                               '<td style="text-align: right; white-space: nowrap;">' +
                                   '<button type="button" class="btn-preset-tbl edit" data-preset-edit-id="' + preset.id + '" title="Editar"><i class="fas fa-pen" aria-hidden="true"></i> Editar</button> ' +
                                   '<button type="button" class="btn-preset-tbl delete" data-preset-del-id="' + preset.id + '" title="Eliminar"><i class="fas fa-trash-can" aria-hidden="true"></i></button>' +
                               '</td>';

                // Botón Editar
                var editBtn = tr.querySelector('[data-preset-edit-id]');
                if (editBtn) {
                    editBtn.addEventListener('click', function () {
                        cargarParaEdicion(preset);
                    });
                }

                // Botón Eliminar
                var delBtn = tr.querySelector('[data-preset-del-id]');
                if (delBtn) {
                    delBtn.addEventListener('click', function () {
                        confirmarEliminarPreset(preset);
                    });
                }

                tbody.appendChild(tr);
            });
        }

        function cargarParaEdicion(preset) {
            inputId.value = preset.id;
            inputNombre.value = preset.nombre;
            if (modoSpan) modoSpan.textContent = 'Editando: ' + preset.nombre;
            btnGuardar.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i> Actualizar';
            if (btnCancelar) btnCancelar.hidden = false;

            if (preset.tipo === 'conmemorativa') {
                var mm = (preset.dia || '').slice(0, 2);
                var dd = (preset.dia || '').slice(3, 5);
                if (selectDiaDia) selectDiaDia.value = dd;
                if (selectDiaMes) selectDiaMes.value = mm;
            } else {
                var dmm = (preset.desde || '').slice(0, 2), ddd = (preset.desde || '').slice(3, 5);
                var hmm = (preset.hasta || '').slice(0, 2), hdd = (preset.hasta || '').slice(3, 5);
                if (selectDesdeDia) selectDesdeDia.value = ddd;
                if (selectDesdeMes) selectDesdeMes.value = dmm;
                if (selectHastaDia) selectHastaDia.value = hdd;
                if (selectHastaMes) selectHastaMes.value = hmm;
            }

            inputNombre.focus();
        }

        function sincronizarChipsEnFormulario(tipo, lista) {
            var containerId = (tipo === 'rango') ? 'chipsRango' : 'chipsConmemorativa';
            var cont = document.getElementById(containerId);
            if (!cont) return;

            cont.innerHTML = '';
            lista.forEach(function (preset) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'fondo-preset';
                btn.setAttribute('data-id', preset.id);
                btn.setAttribute('data-nombre', preset.nombre);
                btn.textContent = preset.nombre;

                if (preset.tipo === 'conmemorativa') {
                    btn.setAttribute('data-preset-conm', '');
                    btn.setAttribute('data-dia', preset.dia);
                } else {
                    btn.setAttribute('data-preset-rango', '');
                    btn.setAttribute('data-desde', preset.desde);
                    btn.setAttribute('data-hasta', preset.hasta);
                }

                cont.appendChild(btn);
            });

            if (typeof window.vincularPresetButtons === 'function') {
                window.vincularPresetButtons();
            }
        }

        // Guardar preset (Crear o Actualizar)
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function () {
                var nombre = inputNombre.value.trim();
                if (!nombre) {
                    inputNombre.focus();
                    return;
                }

                var payload = {
                    id: inputId.value ? parseInt(inputId.value, 10) : null,
                    tipo: tipoActual,
                    nombre: nombre
                };

                if (tipoActual === 'conmemorativa') {
                    payload.dia = selectDiaMes.value + '-' + selectDiaDia.value;
                } else {
                    payload.desde = selectDesdeMes.value + '-' + selectDesdeDia.value;
                    payload.hasta = selectHastaMes.value + '-' + selectHastaDia.value;
                }

                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Guardando...';

                fetch('/admin/fondos/presets', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrf(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(function (res) {
                    if (!res.ok) throw new Error('Error al guardar el preset');
                    return res.json();
                })
                .then(function (data) {
                    btnGuardar.disabled = false;
                    if (data && data.success && data.presets) {
                        presetsData[tipoActual] = data.presets;
                        renderTabla();
                        sincronizarChipsEnFormulario(tipoActual, data.presets);
                        resetForm();
                    }
                })
                .catch(function (err) {
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="fas fa-floppy-disk" aria-hidden="true"></i> Guardar';
                    alert('Ocurrió un error al guardar la opción: ' + err.message);
                });
            });
        }

        // Cancelar edición
        if (btnCancelar) {
            btnCancelar.addEventListener('click', resetForm);
        }

        // Confirmar y eliminar preset
        function confirmarEliminarPreset(preset) {
            var msg = '¿Estás seguro de que deseas eliminar "' + preset.nombre + '"? Ya no aparecerá en la cinta de opciones rápidas.';
            if (typeof window.fondoConfirmar === 'function') {
                window.fondoConfirmar({
                    titulo: 'Eliminar opción predeterminada',
                    mensaje: msg,
                    okTexto: 'Sí, eliminar'
                }, function () {
                    ejecutarEliminarPreset(preset);
                });
            } else if (confirm(msg)) {
                ejecutarEliminarPreset(preset);
            }
        }

        function ejecutarEliminarPreset(preset) {
            fetch('/admin/fondos/presets/' + preset.id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json'
                }
            })
            .then(function (res) {
                if (!res.ok) throw new Error('Error al eliminar');
                return res.json();
            })
            .then(function (data) {
                if (data && data.success && data.presets) {
                    presetsData[preset.tipo] = data.presets;
                    renderTabla();
                    sincronizarChipsEnFormulario(preset.tipo, data.presets);
                    resetForm();
                }
            })
            .catch(function (err) {
                alert('No se pudo eliminar la opción: ' + err.message);
            });
        }

        // Botones de apertura en la interfaz del formulario
        Array.prototype.slice.call(document.querySelectorAll('[data-abrir-preset-gestor]')).forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var tipo = btn.getAttribute('data-abrir-preset-gestor') || 'conmemorativa';
                abrir(tipo);
            });
        });

        // Botones de cierre del modal
        Array.prototype.slice.call(modal.querySelectorAll('[data-preset-gestor-close]')).forEach(function (el) {
            el.addEventListener('click', cerrar);
        });

        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && !modal.hidden) {
                cerrar();
            }
        });
    }
})();
