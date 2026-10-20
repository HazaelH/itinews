/**
 * Revistas publicadas (dashboard ITINEWS).
 *
 * Añade interactividad al panel de "Tus Revistas Publicadas":
 *   - Buscador en vivo y filtro por estado (afecta tabla y tarjetas).
 *   - Contador de resultados y estado "sin coincidencias".
 *   - Orden por columnas (título, estado, fecha, páginas).
 *   - Cambio de vista: tabla <-> tarjetas (se recuerda en localStorage).
 *   - Seguimiento del progreso de las revistas en proceso.
 *
 * Marca window.__ITIN_REVISTAS_UI__ para que admin-dashboard.js no duplique
 * el manejo de búsqueda/filtro/progreso.
 */
(function () {
    'use strict';

    window.__ITIN_REVISTAS_UI__ = true;

    var LS_KEY = 'itin_rev_vista';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    ready(function () {
        var panel = document.getElementById('revPanel');
        if (!panel) {
            return;
        }

        var search = document.getElementById('searchRevistas');
        var filter = document.getElementById('filterStatus');
        var filterVis = document.getElementById('filterVisibilidad');
        var limpiar = document.getElementById('revLimpiar');
        var resultados = document.getElementById('revResultados');
        var vistaTabla = document.getElementById('revVistaTabla');
        var vistaTarjetas = document.getElementById('revVistaTarjetas');
        var sinResultados = document.getElementById('revSinResultados');
        var tbody = document.getElementById('revistasTableBody');
        var botonesVista = panel.querySelectorAll('.rev-vista-btn');
        var ths = panel.querySelectorAll('.rev-th-orden');

        var items = Array.prototype.slice.call(panel.querySelectorAll('[data-revista-item]'));
        if (!items.length) {
            return;
        }

        var vistaActual = 'tabla';
        var visibles = items.length;

        // -------------------------------------------------------------
        // Filtrado
        // -------------------------------------------------------------
        function coincide(item) {
            var q = search ? search.value.toLowerCase().trim() : '';
            var estado = filter ? filter.value : '';
            var vis = filterVis ? filterVis.value : '';

            var texto = (item.dataset.titulo || '') + ' ' + (item.dataset.descripcion || '');
            var okTexto = q === '' || texto.indexOf(q) !== -1;
            var okEstado = estado === '' || item.dataset.status === estado;
            var okVis = vis === '' || item.dataset.activo === vis;

            return okTexto && okEstado && okVis;
        }

        function sincronizarVista() {
            var esTarjetas = vistaActual === 'tarjetas';
            var vacio = visibles === 0;

            if (vistaTabla) {
                vistaTabla.hidden = vacio || esTarjetas;
            }
            if (vistaTarjetas) {
                vistaTarjetas.hidden = vacio || !esTarjetas;
            }
            if (sinResultados) {
                sinResultados.hidden = !vacio;
            }

            botonesVista.forEach(function (b) {
                var activo = b.dataset.vista === vistaActual;
                b.classList.toggle('is-active', activo);
                b.setAttribute('aria-pressed', activo ? 'true' : 'false');
            });
        }

        function aplicar() {
            // Contamos sobre las filas de la tabla (una por revista) para no
            // duplicar el conteo entre la vista de tabla y la de tarjetas.
            var fuente = tbody
                ? Array.prototype.slice.call(tbody.querySelectorAll('[data-revista-item]'))
                : items;

            visibles = 0;
            fuente.forEach(function (item) {
                if (coincide(item)) {
                    visibles++;
                }
            });

            items.forEach(function (item) {
                item.style.display = coincide(item) ? '' : 'none';
            });

            var filtrando = (search && search.value.trim() !== '') ||
                (filter && filter.value !== '') ||
                (filterVis && filterVis.value !== '');
            if (resultados) {
                if (filtrando) {
                    resultados.hidden = false;
                    resultados.textContent = visibles + ' de ' + fuente.length;
                } else {
                    resultados.hidden = true;
                }
            }
            if (limpiar) {
                limpiar.hidden = !(search && search.value !== '');
            }

            sincronizarVista();
        }

        if (search) {
            search.addEventListener('input', aplicar);
        }
        if (filter) {
            filter.addEventListener('change', aplicar);
        }
        if (filterVis) {
            filterVis.addEventListener('change', aplicar);
        }
        if (limpiar) {
            limpiar.addEventListener('click', function () {
                if (search) {
                    search.value = '';
                    search.focus();
                }
                if (filter) {
                    filter.value = '';
                }
                if (filterVis) {
                    filterVis.value = '';
                }
                aplicar();
            });
        }

        // -------------------------------------------------------------
        // Orden por columnas
        // -------------------------------------------------------------
        var orden = { clave: null, dir: 'asc' };

        function valor(item, clave) {
            if (clave === 'paginas') {
                return parseInt(item.dataset.paginas || '0', 10) || 0;
            }
            if (clave === 'estado') {
                return item.dataset.status || '';
            }
            return item.dataset[clave] || '';
        }

        function ordenar(clave, dir) {
            [tbody, vistaTarjetas].forEach(function (cont) {
                if (!cont) {
                    return;
                }
                var nodos = Array.prototype.slice.call(cont.querySelectorAll('[data-revista-item]'));
                nodos.sort(function (a, b) {
                    var va = valor(a, clave);
                    var vb = valor(b, clave);
                    var r;
                    if (typeof va === 'number' && typeof vb === 'number') {
                        r = va - vb;
                    } else {
                        r = String(va).localeCompare(String(vb), 'es', { numeric: true });
                    }
                    return dir === 'asc' ? r : -r;
                });
                nodos.forEach(function (n) {
                    cont.appendChild(n);
                });
            });
        }

        ths.forEach(function (th) {
            th.addEventListener('click', function () {
                var clave = th.dataset.sort;
                orden.dir = (orden.clave === clave && orden.dir === 'asc') ? 'desc' : 'asc';
                orden.clave = clave;
                ordenar(clave, orden.dir);

                ths.forEach(function (otro) {
                    otro.classList.remove('is-asc', 'is-desc');
                    otro.setAttribute('aria-sort', 'none');
                    var ic = otro.querySelector('i');
                    if (ic) {
                        ic.className = 'fas fa-sort';
                    }
                });

                th.classList.add(orden.dir === 'asc' ? 'is-asc' : 'is-desc');
                th.setAttribute('aria-sort', orden.dir === 'asc' ? 'ascending' : 'descending');
                var icono = th.querySelector('i');
                if (icono) {
                    icono.className = 'fas ' + (orden.dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                }
            });
        });

        // -------------------------------------------------------------
        // Cambio de vista
        // -------------------------------------------------------------
        function setVista(vista) {
            vistaActual = vista === 'tarjetas' ? 'tarjetas' : 'tabla';
            try {
                localStorage.setItem(LS_KEY, vistaActual);
            } catch (e) { /* almacenamiento no disponible */ }
            sincronizarVista();
        }

        botonesVista.forEach(function (b) {
            b.addEventListener('click', function () {
                setVista(b.dataset.vista);
            });
        });

        try {
            if (localStorage.getItem(LS_KEY) === 'tarjetas') {
                vistaActual = 'tarjetas';
            }
        } catch (e) { /* almacenamiento no disponible */ }

        // -------------------------------------------------------------
        // Seguimiento de progreso de revistas en proceso
        // -------------------------------------------------------------
        function pollProgreso() {
            var barras = panel.querySelectorAll('.progress-bar-fill[data-revista-id]');
            if (!barras.length) {
                return;
            }

            var ids = [];
            var vistos = {};
            Array.prototype.forEach.call(barras, function (barra) {
                var id = barra.getAttribute('data-revista-id');
                if (id && !vistos[id]) {
                    vistos[id] = true;
                    ids.push(id);
                }
            });

            var completado = false;
            var restantes = ids.length;

            ids.forEach(function (id) {
                fetch('/revista/' + encodeURIComponent(id) + '/progreso', {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (r) {
                        return r.ok ? r.json() : null;
                    })
                    .then(function (data) {
                        if (!data) {
                            return;
                        }
                        var pct = Math.round(data.progreso || 0);
                        panel.querySelectorAll('.progress-bar-fill[data-revista-id="' + id + '"]').forEach(function (barra) {
                            barra.style.width = pct + '%';
                            var item = barra.closest('[data-revista-item]');
                            var txt = item ? item.querySelector('.revista-progreso') : null;
                            if (txt) {
                                txt.textContent = pct + '%';
                            }
                        });
                        if (data.completado || data.error) {
                            completado = true;
                        }
                    })
                    .catch(function () { /* ignorar */ })
                    .finally(function () {
                        restantes--;
                        if (restantes <= 0 && completado) {
                            window.clearInterval(timer);
                            window.setTimeout(function () {
                                window.location.reload();
                            }, 1500);
                        }
                    });
            });
        }

        var timer = null;
        if (panel.querySelector('.progress-bar-fill[data-revista-id]')) {
            pollProgreso();
            timer = window.setInterval(pollProgreso, 4000);
        }

        // -------------------------------------------------------------
        // Modales de Confirmación (Eliminar y Reprocesar) y Progreso
        // -------------------------------------------------------------
        var modalDelete = document.getElementById('dashboardConfirmDeleteModal');
        var modalReprocess = document.getElementById('dashboardConfirmReprocessModal');
        var modalProgreso = document.getElementById('dashboardModalProgresoReprocesar');

        var formEliminarPendiente = null;
        var formReprocesarPendiente = null;

        // Modal Eliminación
        if (modalDelete) {
            var deleteTituloEl = document.getElementById('deleteModalRevistaTitulo');
            var btnConfirmDelete = document.getElementById('btnConfirmDelete');

            var cerrarModalDelete = function () {
                modalDelete.hidden = true;
                formEliminarPendiente = null;
            };

            Array.prototype.slice.call(modalDelete.querySelectorAll('[data-delete-cancel]')).forEach(function (btn) {
                btn.addEventListener('click', cerrarModalDelete);
            });

            if (btnConfirmDelete) {
                btnConfirmDelete.addEventListener('click', function () {
                    if (formEliminarPendiente) {
                        formEliminarPendiente.dataset.confirmado = '1';
                        btnConfirmDelete.disabled = true;
                        btnConfirmDelete.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
                        formEliminarPendiente.submit();
                    }
                });
            }

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form || !form.matches('[data-eliminar-form]')) return;
                if (form.dataset.confirmado === '1') return;

                e.preventDefault();
                formEliminarPendiente = form;
                if (deleteTituloEl) {
                    deleteTituloEl.textContent = form.getAttribute('data-revista-titulo') || 'esta revista';
                }
                modalDelete.hidden = false;
                if (btnConfirmDelete) btnConfirmDelete.focus();
            });
        }

        // Modal Reprocesar
        if (modalReprocess && modalProgreso) {
            var reprocessTituloEl = document.getElementById('reprocessModalRevistaTitulo');
            var btnConfirmReprocess = document.getElementById('btnConfirmReprocess');

            var cerrarModalReprocess = function () {
                modalReprocess.hidden = true;
                formReprocesarPendiente = null;
            };

            Array.prototype.slice.call(modalReprocess.querySelectorAll('[data-reprocess-cancel]')).forEach(function (btn) {
                btn.addEventListener('click', cerrarModalReprocess);
            });

            // Elementos del Modal de Progreso
            var progBadge = document.getElementById('reprocessProgressBadge');
            var progBadgeIcon = document.getElementById('reprocessProgressBadgeIcon');
            var progTitle = document.getElementById('reprocessProgressTitle');
            var progSub = document.getElementById('reprocessProgressSub');
            var progFill = document.getElementById('reprocessProgressFill');
            var progStatus = document.getElementById('reprocessProgressStatus');
            var progPercent = document.getElementById('reprocessProgressPercent');
            var progDetailText = document.getElementById('reprocessDetailText');
            var stepPrep = document.getElementById('reprocessStepPrep');
            var stepConvert = document.getElementById('reprocessStepConvert');
            var stepFinish = document.getElementById('reprocessStepFinish');
            var errorBox = document.getElementById('reprocessErrorBox');
            var errorMsg = document.getElementById('reprocessErrorMsg');
            var modalFooter = document.getElementById('reprocessModalFooter');
            var closeBtn = document.getElementById('reprocessModalCloseBtn');

            var pollTimer = null;
            var isProcessing = false;

            var warnBeforeUnload = function (ev) {
                if (isProcessing) {
                    ev.preventDefault();
                    ev.returnValue = 'La revista se está reprocesando. ¿Seguro que deseas salir?';
                    return ev.returnValue;
                }
            };

            var mostrarErrorReproceso = function (mensaje) {
                isProcessing = false;
                window.removeEventListener('beforeunload', warnBeforeUnload);
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
                if (progBadge) progBadge.className = 'upload-modal-badge badge-error';
                if (progBadgeIcon) progBadgeIcon.className = 'fas fa-triangle-exclamation';
                if (progTitle) progTitle.textContent = 'Ocurrió un inconveniente';
                if (progSub) progSub.textContent = 'No se pudo completar el reprocesamiento.';
                if (errorBox) errorBox.hidden = false;
                if (errorMsg) errorMsg.textContent = mensaje || 'Error al reprocesar la revista.';
                if (modalFooter) modalFooter.hidden = false;
            };

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    modalProgreso.hidden = true;
                    window.location.reload();
                });
            }

            if (btnConfirmReprocess) {
                btnConfirmReprocess.addEventListener('click', function () {
                    var form = formReprocesarPendiente;
                    if (!form) return;

                    cerrarModalReprocess();

                    var revistaId = form.getAttribute('data-revista-id');
                    var revistaTitulo = form.getAttribute('data-revista-titulo') || 'la revista';

                    // Inicializar Modal de Progreso
                    isProcessing = true;
                    window.addEventListener('beforeunload', warnBeforeUnload);

                    modalProgreso.hidden = false;
                    if (progBadge) progBadge.className = 'upload-modal-badge';
                    if (progBadgeIcon) progBadgeIcon.className = 'fas fa-rotate fa-spin';
                    if (progTitle) progTitle.textContent = 'Reprocesando: ' + revistaTitulo;
                    if (progSub) progSub.textContent = 'Optimizando páginas WebP en alta resolución...';
                    if (progFill) progFill.style.width = '0%';
                    if (progPercent) progPercent.textContent = '0%';
                    if (progStatus) progStatus.textContent = 'Iniciando limpieza y preparación...';
                    if (progDetailText) progDetailText.textContent = 'Conectando con el motor de procesamiento...';

                    if (stepPrep) stepPrep.className = 'upload-step active';
                    if (stepConvert) stepConvert.className = 'upload-step';
                    if (stepFinish) stepFinish.className = 'upload-step';

                    if (errorBox) errorBox.hidden = true;
                    if (modalFooter) modalFooter.hidden = true;

                    // 1. Enviar petición AJAX para iniciar el reproceso
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.addEventListener('load', function () {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            var resp = null;
                            try { resp = JSON.parse(xhr.responseText); } catch (e) {}
                            if (resp && resp.success) {
                                // Procesado con éxito
                            } else {
                                mostrarErrorReproceso((resp && resp.error) ? resp.error : 'No se pudo iniciar el reprocesamiento.');
                            }
                        } else {
                            var errMsg = 'Ocurrió un error en el servidor al intentar reprocesar.';
                            try {
                                var errResp = JSON.parse(xhr.responseText);
                                if (errResp.error) errMsg = errResp.error;
                            } catch (e) {}
                            mostrarErrorReproceso(errMsg);
                        }
                    });

                    xhr.addEventListener('error', function () {
                        mostrarErrorReproceso('Error de red al intentar reprocesar la revista.');
                    });

                    var formData = new FormData(form);
                    xhr.send(formData);

                    // 2. Iniciar Polling de progreso
                    pollTimer = setInterval(function () {
                        fetch('/revista/' + encodeURIComponent(revistaId) + '/progreso', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(function (r) { return r.ok ? r.json() : null; })
                            .then(function (data) {
                                if (!data) return;

                                var total = data.total_paginas || 0;
                                var procesadas = data.paginas_procesadas || 0;
                                var pct = Math.round(data.progreso || 0);

                                if (total > 0) {
                                    if (stepPrep) stepPrep.className = 'upload-step done';
                                    if (stepConvert) stepConvert.className = 'upload-step active';

                                    if (progDetailText) {
                                        progDetailText.textContent = 'Página ' + procesadas + ' de ' + total + ' páginas';
                                    }
                                    if (progStatus) {
                                        progStatus.textContent = 'Optimizando página ' + Math.min(procesadas + 1, total) + '...';
                                    }
                                } else {
                                    if (progStatus) {
                                        progStatus.textContent = 'Limpiando páginas anteriores y preparando documento...';
                                    }
                                }

                                if (progFill) progFill.style.width = pct + '%';
                                if (progPercent) progPercent.textContent = pct + '%';

                                if (data.completado) {
                                    clearInterval(pollTimer);
                                    pollTimer = null;
                                    isProcessing = false;
                                    window.removeEventListener('beforeunload', warnBeforeUnload);

                                    if (stepConvert) stepConvert.className = 'upload-step done';
                                    if (stepFinish) stepFinish.className = 'upload-step done';

                                    if (progBadge) progBadge.className = 'upload-modal-badge badge-success';
                                    if (progBadgeIcon) progBadgeIcon.className = 'fas fa-check';

                                    if (progTitle) progTitle.textContent = '¡Revista lista!';
                                    if (progSub) progSub.textContent = 'El PDF se ha reprocesado y optimizado con éxito.';
                                    if (progStatus) progStatus.textContent = 'Actualizando el listado...';
                                    if (progFill) progFill.style.width = '100%';
                                    if (progPercent) progPercent.textContent = '100%';

                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 1400);
                                } else if (data.error) {
                                    mostrarErrorReproceso('Ocurrió un error durante la extracción y optimización de páginas.');
                                }
                            })
                            .catch(function () {
                                /* Fallo transitorio de red durante polling */
                            });
                    }, 1200);
                });
            }

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form || !form.matches('[data-reprocesar-form]')) return;

                e.preventDefault();
                formReprocesarPendiente = form;
                if (reprocessTituloEl) {
                    reprocessTituloEl.textContent = form.getAttribute('data-revista-titulo') || 'esta revista';
                }
                modalReprocess.hidden = false;
                if (btnConfirmReprocess) btnConfirmReprocess.focus();
            });
        }

        sincronizarVista();
        aplicar();
    });
})();
