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

        sincronizarVista();
        aplicar();
    });
})();
