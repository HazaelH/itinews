/**
 * Dashboard del admin (ITINEWS).
 *
 * Resume la actividad del portal: KPIs, visitas por día, revistas más abiertas,
 * dispositivos y horas pico. Reutiliza el endpoint /admin/metricas/data y el
 * módulo compartido window.ItinGraficas.
 */
(function () {
    'use strict';

    var DATA_URL = window.ITIN_METRICAS_DATA_URL || '/admin/metricas/data';
    var DEVICE_LABELS = { desktop: 'Escritorio', mobile: 'Móvil', tablet: 'Tablet', bot: 'Bot', unknown: 'Desconocido' };

    function el(id) { return document.getElementById(id); }
    function G() { return window.ItinGraficas; }

    function fmtHoras(seg) {
        seg = Math.round(seg || 0);
        var m = Math.floor(seg / 60);
        var s = seg % 60;
        if (m < 60) { return m + 'm ' + s + 's'; }
        return Math.floor(m / 60) + 'h ' + (m % 60) + 'm';
    }

    function renderKpis(k) {
        var items = [
            { label: 'Visitas a revistas', valor: k.visitas_revista, icono: 'fa-book-open', clase: '' },
            { label: 'Aperturas del portal', valor: k.visitas_portal, icono: 'fa-door-open', clase: 'teal' },
            { label: 'Visitantes únicos', valor: k.visitantes_unicos, icono: 'fa-users', clase: 'vino' },
            { label: 'Tiempo promedio', valor: fmtHoras(k.tiempo_promedio), icono: 'fa-clock', clase: 'dorado' },
            { label: 'Páginas por sesión', valor: k.paginas_promedio, icono: 'fa-file-lines', clase: 'teal' }
        ];

        el('dashKpis').innerHTML = items.map(function (i) {
            return '<div class="kpi-card ' + i.clase + '">' +
                '<i class="fas ' + i.icono + ' kpi-icon" aria-hidden="true"></i>' +
                '<span class="kpi-valor">' + i.valor + '</span>' +
                '<span class="kpi-label">' + i.label + '</span>' +
                '</div>';
        }).join('');
    }

    function cargar() {
        var g = G();
        var dias = el('dashRango') ? el('dashRango').value : 30;

        fetch(DATA_URL + '?dias=' + encodeURIComponent(dias), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (r) {
                if (!r.ok) { throw new Error('HTTP ' + r.status); }
                return r.json();
            })
            .then(function (data) {
                renderKpis(data.kpis);

                g.linea('dashVisitas', {
                    labels: data.visitas_por_dia.labels,
                    valores: data.visitas_por_dia.valores,
                    nombre: 'Visitas'
                }, { color: '#1f5b4f' });

                g.dona('dashDispositivos', (data.dispositivos || []).map(function (d) {
                    return { label: DEVICE_LABELS[d.label] || d.label, total: d.total };
                }));

                g.barras('dashHoras', data.horas_pico || [], { intervalo: 0 });

                g.listaMiniaturas('dashRevistas', (data.revistas_mas_vistas || []).map(function (r) {
                    return { titulo: r.titulo, total: r.total, imagen: r.portada };
                }), { tipoImagen: 'portada', color: '#1f5b4f' });

                g.resizeAll();
            })
            .catch(function (err) {
                var cont = el('dashKpis');
                if (cont) {
                    cont.innerHTML = '<div class="lista-vacia">No se pudieron cargar las métricas (' + err.message + ').</div>';
                }
            });
    }

    function arrancar() {
        if (!el('dashKpis')) { return; }

        if (typeof echarts === 'undefined' || !window.ItinGraficas) {
            el('dashKpis').innerHTML =
                '<div class="lista-vacia">No se pudo cargar la librería de gráficas (ECharts).</div>';
            return;
        }

        if (el('dashRango')) { el('dashRango').addEventListener('change', cargar); }

        cargar();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', arrancar);
    } else {
        arrancar();
    }
})();
