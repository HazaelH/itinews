/**
 * Panel de métricas del admin (ITINEWS).
 *
 * Consume /admin/metricas/data y dibuja las gráficas con ECharts a través del
 * módulo compartido window.ItinGraficas, además de las listas con miniaturas.
 */
(function () {
    'use strict';

    var DATA_URL = window.ITIN_METRICAS_DATA_URL || '/admin/metricas/data';
    var DEVICE_LABELS = { desktop: 'Escritorio', mobile: 'Móvil', tablet: 'Tablet', bot: 'Bot', unknown: 'Desconocido' };

    var ultimoDato = null;

    function el(id) { return document.getElementById(id); }
    function G() { return window.ItinGraficas; }

    function fmtHoras(seg) {
        seg = Math.round(seg || 0);
        var m = Math.floor(seg / 60);
        var s = seg % 60;
        if (m < 60) { return m + 'm ' + s + 's'; }
        return Math.floor(m / 60) + 'h ' + (m % 60) + 'm';
    }

    // ------------------------------------------------------------------
    // KPIs
    // ------------------------------------------------------------------
    function renderKpis(k) {
        var items = [
            { label: 'Visitas a revistas', valor: k.visitas_revista, icono: 'fa-book-open', clase: '' },
            { label: 'Aperturas del portal', valor: k.visitas_portal, icono: 'fa-door-open', clase: 'teal' },
            { label: 'Visitantes únicos', valor: k.visitantes_unicos, icono: 'fa-users', clase: 'vino' },
            { label: 'Visitantes recurrentes', valor: k.visitantes_recurrentes, icono: 'fa-user-clock', clase: 'vino' },
            { label: 'Tiempo promedio', valor: fmtHoras(k.tiempo_promedio), icono: 'fa-clock', clase: 'dorado' },
            { label: 'Páginas por sesión', valor: k.paginas_promedio, icono: 'fa-file-lines', clase: 'teal' },
            { label: '% finalización', valor: (k.tasa_finalizacion || 0) + '%', icono: 'fa-flag-checkered', clase: '' }
        ];

        el('kpiGrid').innerHTML = items.map(function (i) {
            return '<div class="kpi-card ' + i.clase + '">' +
                '<i class="fas ' + i.icono + ' kpi-icon" aria-hidden="true"></i>' +
                '<span class="kpi-valor">' + i.valor + '</span>' +
                '<span class="kpi-label">' + i.label + '</span>' +
                '</div>';
        }).join('');
    }

    // ------------------------------------------------------------------
    // Listas con miniatura
    // ------------------------------------------------------------------
    function renderListas(data) {
        var g = G();

        g.listaMiniaturas('listaRevistas', (data.revistas_mas_vistas || []).map(function (r) {
            return { titulo: r.titulo, total: r.total, imagen: r.portada };
        }), { tipoImagen: 'portada', color: '#1f5b4f' });

        g.listaMiniaturas('listaFavoritos', (data.favoritos || []).map(function (r) {
            return { titulo: r.titulo, total: r.total, imagen: r.portada };
        }), { tipoImagen: 'portada', color: '#7a1830' });

        var paginasCont = el('listaPaginasRevistas');
        if (paginasCont) {
            var grupos = data.paginas_mas_vistas || [];
            if (!grupos.length) {
                paginasCont.innerHTML = '<div class="lista-vacia">Sin datos en este rango</div>';
            } else {
                paginasCont.innerHTML = grupos.map(function (r) {
                    var portada = r.portada
                        ? '<img loading="lazy" alt="" src="' + g.miniaturaUrl(r.portada, 46) + '">'
                        : '<div style="width:46px;height:62px;border-radius:6px;background:#eee;display:flex;align-items:center;justify-content:center;color:#b9ac9b;"><i class="fas fa-book"></i></div>';

                    var paginas = (r.paginas || []).slice(0, 8).map(function (p) {
                        var pct = r.total ? Math.round((p.total / r.total) * 100) : 0;
                        return '<span class="pag-chip" title="' + p.total + ' lecturas">' +
                            '<i class="fas fa-bookmark" aria-hidden="true"></i>' +
                            'Pág. ' + p.pagina + ' · <strong>' + p.total + '</strong>' +
                            '</span>';
                    }).join('');

                    return '<div class="pag-rev">' +
                        '<div class="pag-rev-portada">' + portada + '</div>' +
                        '<div class="pag-rev-info">' +
                        '<span class="pag-rev-titulo" title="' + (r.revista_titulo || '') + '">' + (r.revista_titulo || ('Revista #' + r.revista_id)) + '</span>' +
                        '<span class="pag-rev-total">' + r.total + ' lecturas · ' + (r.paginas ? r.paginas.length : 0) + ' páginas diferentes</span>' +
                        '<div class="pag-chips">' + (paginas || '<span class="pag-chip">Sin páginas registradas</span>') + '</div>' +
                        '</div>' +
                        '</div>';
                }).join('');
            }
        }
    }

    // ------------------------------------------------------------------
    // Carga y render
    // ------------------------------------------------------------------
    function cargar() {
        var g = G();
        var dias = el('metricasRango') ? el('metricasRango').value : 30;
        el('metricasEstado').textContent = 'Cargando…';

        fetch(DATA_URL + '?dias=' + encodeURIComponent(dias), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (r) {
                if (!r.ok) { throw new Error('HTTP ' + r.status); }
                return r.json();
            })
            .then(function (data) {
                ultimoDato = data;
                el('metricasEstado').textContent =
                    'Mostrando del ' + data.rango.desde + ' al ' + data.rango.hasta + '.';

                renderKpis(data.kpis);

                g.treemap('chartTreemap', (data.revistas_mas_vistas || []).map(function (r) {
                    return { name: r.titulo, value: r.total };
                }));

                g.dona('chartDispositivos', (data.dispositivos || []).map(function (d) {
                    return { label: DEVICE_LABELS[d.label] || d.label, total: d.total };
                }));

                g.barrasH('chartSistemas', data.sistemas || [], { mostrarValor: true });
                g.barrasH('chartNavegadores', data.navegadores || [], { mostrarValor: true });
                g.barras('chartHistograma', data.histograma_paginas || [], {});

                g.linea('chartVisitasDia', {
                    labels: data.visitas_por_dia.labels,
                    valores: data.visitas_por_dia.valores,
                    nombre: 'Visitas'
                }, { color: '#1f5b4f' });

                g.linea('chartTiempoDia', {
                    labels: data.tiempo_por_dia.labels,
                    valores: data.tiempo_por_dia.valores,
                    nombre: 'Segundos'
                }, { color: '#7a1830' });

                g.barras('chartHoras', data.horas_pico || [], { intervalo: 0 });

                renderListas(data);

                g.resizeAll();
            })
            .catch(function (err) {
                el('metricasEstado').textContent = 'Error al cargar las métricas: ' + err.message;
            });
    }

    // ------------------------------------------------------------------
    // Arranque
    // ------------------------------------------------------------------
    function arrancar() {
        if (!el('kpiGrid')) { return; }

        if (typeof echarts === 'undefined' || !window.ItinGraficas) {
            el('metricasEstado').textContent =
                'No se pudo cargar la librería de gráficas (ECharts). Revisa tu conexión a internet.';
            return;
        }

        if (el('metricasRefresh')) { el('metricasRefresh').addEventListener('click', cargar); }
        if (el('metricasRango')) { el('metricasRango').addEventListener('change', cargar); }

        cargar();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', arrancar);
    } else {
        arrancar();
    }
})();
