/* =====================================================================
   Gráficas ITINEWS
   Módulo compartido (métricas + dashboard) sobre ECharts.
   Expone window.ItinGraficas con constructores de gráficas y de listas
   con miniaturas. Requiere que ECharts esté cargado antes.
   ===================================================================== */
window.ItinGraficas = (function () {
    'use strict';

    var PALETA = [
        '#1f5b4f', // verde marca
        '#7a1830', // vino marca
        '#2f8f7f', // teal
        '#d1544a', // coral
        '#c9a227', // dorado
        '#6b3fa0', // ciruela
        '#d98c1f', // ámbar
        '#3a7ca5', // azul
        '#9a1f3d', // vino claro
        '#5aa469', // verde claro
        '#b5651d', // cobre
        '#4a4e69'  // gris azulado
    ];

    var charts = {};

    // -----------------------------------------------------------------
    // Utilidades
    // -----------------------------------------------------------------
    function escapar(valor) {
        return String(valor === null || valor === undefined ? '' : valor).replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        });
    }

    function color(indice) {
        var i = (typeof indice === 'number' && isFinite(indice)) ? indice : 0;
        return PALETA[((i % PALETA.length) + PALETA.length) % PALETA.length];
    }

    function aclarar(hex, factor) {
        var c = hex.replace('#', '');
        var r = parseInt(c.substr(0, 2), 16);
        var g = parseInt(c.substr(2, 2), 16);
        var b = parseInt(c.substr(4, 2), 16);
        r = Math.round(r + (255 - r) * factor);
        g = Math.round(g + (255 - g) * factor);
        b = Math.round(b + (255 - b) * factor);
        return 'rgb(' + r + ',' + g + ',' + b + ')';
    }

    function degradado(hex, vertical) {
        var esVertical = vertical !== false;
        var colorClaro = aclarar(hex, 0.45);
        return new echarts.graphic.LinearGradient(
            esVertical ? 0 : 0,
            esVertical ? 0 : 0,
            esVertical ? 0 : 1,
            esVertical ? 1 : 0,
            [
                { offset: 0, color: colorClaro },
                { offset: 1, color: hex }
            ]
        );
    }

    function init(id) {
        var el = document.getElementById(id);
        if (!el || typeof echarts === 'undefined') {
            return null;
        }
        if (!charts[id]) {
            charts[id] = echarts.init(el);
        }
        return charts[id];
    }

    function sinDatos(chart) {
        if (!chart) {
            return;
        }
        chart.clear();
        chart.setOption({
            graphic: {
                type: 'text',
                left: 'center',
                top: 'middle',
                style: { text: 'Sin datos en este rango', fill: '#9a9a9a', fontSize: 13 }
            }
        });
    }

    function tooltipBase() {
        return {
            trigger: 'item',
            backgroundColor: 'rgba(31,58,50,.94)',
            borderColor: 'transparent',
            textStyle: { color: '#fff', fontSize: 12 }
        };
    }

    // -----------------------------------------------------------------
    // Barras verticales (una serie, color por categoría)
    // -----------------------------------------------------------------
    function barras(id, items, opts) {
        var chart = init(id);
        if (!chart) {
            return;
        }
        opts = opts || {};
        if (!items || !items.length) {
            sinDatos(chart);
            return;
        }

        var labels = items.map(function (i) { return i.label; });
        var data = items.map(function (i, idx) {
            return {
                value: i.total,
                itemStyle: { color: degradado(opts.colorUnico || color(idx)) }
            };
        });

        chart.clear();
        chart.setOption({
            tooltip: Object.assign(tooltipBase(), { trigger: 'axis', axisPointer: { type: 'shadow' } }),
            grid: { left: 8, right: 14, top: 20, bottom: 8, containLabel: true },
            xAxis: {
                type: 'category',
                data: labels,
                axisTick: { show: false },
                axisLine: { lineStyle: { color: '#ddd' } },
                axisLabel: {
                    color: '#666',
                    fontSize: 11,
                    interval: opts.intervalo === undefined ? 0 : opts.intervalo,
                    rotate: opts.rotar || 0
                }
            },
            yAxis: {
                type: 'value',
                splitLine: { lineStyle: { color: '#f0f0f0' } },
                axisLabel: { color: '#999' }
            },
            series: [{
                type: 'bar',
                data: data,
                barMaxWidth: 42,
                itemStyle: { borderRadius: [5, 5, 0, 0] },
                label: opts.mostrarValor
                    ? { show: true, position: 'top', color: '#7a1830', fontWeight: 'bold', fontSize: 11 }
                    : { show: false }
            }]
        });
    }

    // -----------------------------------------------------------------
    // Barras horizontales
    // -----------------------------------------------------------------
    function barrasH(id, items, opts) {
        var chart = init(id);
        if (!chart) {
            return;
        }
        opts = opts || {};
        if (!items || !items.length) {
            sinDatos(chart);
            return;
        }

        var ordenados = items.slice().reverse();
        var labels = ordenados.map(function (i) { return i.label; });
        var data = ordenados.map(function (i, idx) {
            return {
                value: i.total,
                itemStyle: { color: degradado(color(idx), false) }
            };
        });

        chart.clear();
        chart.setOption({
            tooltip: Object.assign(tooltipBase(), { trigger: 'axis', axisPointer: { type: 'shadow' } }),
            grid: { left: 8, right: 30, top: 14, bottom: 8, containLabel: true },
            xAxis: {
                type: 'value',
                splitLine: { lineStyle: { color: '#f0f0f0' } },
                axisLabel: { color: '#999' }
            },
            yAxis: {
                type: 'category',
                data: labels,
                axisTick: { show: false },
                axisLine: { lineStyle: { color: '#ddd' } },
                axisLabel: { color: '#555', fontSize: 11 }
            },
            series: [{
                type: 'bar',
                data: data,
                barMaxWidth: 24,
                itemStyle: { borderRadius: [0, 5, 5, 0] },
                label: opts.mostrarValor
                    ? { show: true, position: 'right', color: '#7a1830', fontWeight: 'bold', fontSize: 11 }
                    : { show: false }
            }]
        });
    }

    // -----------------------------------------------------------------
    // Dona
    // -----------------------------------------------------------------
    function dona(id, items, opts) {
        var chart = init(id);
        if (!chart) {
            return;
        }
        opts = opts || {};
        if (!items || !items.length) {
            sinDatos(chart);
            return;
        }

        var data = items.map(function (i, idx) {
            return { name: i.label, value: i.total, itemStyle: { color: color(idx) } };
        });

        chart.clear();
        chart.setOption({
            color: PALETA,
            tooltip: tooltipBase(),
            legend: {
                bottom: 0,
                icon: 'circle',
                textStyle: { color: '#666', fontSize: 11 }
            },
            series: [{
                type: 'pie',
                radius: ['46%', '70%'],
                center: ['50%', '44%'],
                avoidLabelOverlap: true,
                itemStyle: { borderColor: '#fff', borderWidth: 2 },
                label: { color: '#555', fontSize: 11, formatter: '{b}\n{d}%' },
                data: data
            }]
        });
    }

    // -----------------------------------------------------------------
    // Treemap
    // -----------------------------------------------------------------
    function treemap(id, items, opts) {
        var chart = init(id);
        if (!chart) {
            return;
        }
        opts = opts || {};
        if (!items || !items.length) {
            sinDatos(chart);
            return;
        }

        var data = items.map(function (i, idx) {
            return {
                name: i.name,
                value: i.value,
                itemStyle: { color: color(idx) }
            };
        });

        chart.clear();
        chart.setOption({
            tooltip: Object.assign(tooltipBase(), {
                formatter: function (p) { return p.name + ': <b>' + p.value + '</b>'; }
            }),
            series: [{
                type: 'treemap',
                roam: false,
                nodeClick: false,
                breadcrumb: { show: false },
                label: { color: '#fff', fontSize: 12, fontWeight: 'bold' },
                upperLabel: { show: false },
                itemStyle: { borderColor: '#fff', borderWidth: 2, gapWidth: 2 },
                data: data
            }]
        });
    }

    // -----------------------------------------------------------------
    // Línea / área
    // -----------------------------------------------------------------
    function linea(id, serie, opts) {
        var chart = init(id);
        if (!chart) {
            return;
        }
        opts = opts || {};
        var colorSerie = opts.color || PALETA[0];

        if (!serie || !serie.valores || !serie.valores.length) {
            sinDatos(chart);
            return;
        }

        chart.clear();
        chart.setOption({
            tooltip: Object.assign(tooltipBase(), { trigger: 'axis' }),
            grid: { left: 8, right: 16, top: 20, bottom: 8, containLabel: true },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: serie.labels,
                axisTick: { show: false },
                axisLine: { lineStyle: { color: '#ddd' } },
                axisLabel: { color: '#666', fontSize: 11, interval: opts.intervalo === undefined ? 'auto' : opts.intervalo }
            },
            yAxis: {
                type: 'value',
                splitLine: { lineStyle: { color: '#f0f0f0' } },
                axisLabel: { color: '#999' }
            },
            series: [{
                name: serie.nombre || '',
                type: 'line',
                smooth: true,
                symbolSize: 6,
                data: serie.valores,
                lineStyle: { width: 3, color: colorSerie },
                itemStyle: { color: colorSerie },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(' + hexRGB(colorSerie) + ',0.35)' },
                        { offset: 1, color: 'rgba(' + hexRGB(colorSerie) + ',0.02)' }
                    ])
                }
            }]
        });
    }

    function hexRGB(hex) {
        var c = hex.replace('#', '');
        return parseInt(c.substr(0, 2), 16) + ',' + parseInt(c.substr(2, 2), 16) + ',' + parseInt(c.substr(4, 2), 16);
    }

    // -----------------------------------------------------------------
    // Listas con miniatura
    // -----------------------------------------------------------------
    function miniaturaUrl(path, ancho) {
        if (!path) {
            return '';
        }
        var base = window.ITIN_MINIATURA_URL || '/admin/media/miniatura';
        return base + '?p=' + encodeURIComponent(path) + '&w=' + (ancho || 80);
    }

    function listaMiniaturas(elId, items, opts) {
        var cont = document.getElementById(elId);
        if (!cont) {
            return;
        }
        opts = opts || {};
        if (!items || !items.length) {
            cont.innerHTML = '<div class="lista-vacia">Sin datos en este rango</div>';
            return;
        }

        var max = 1;
        items.forEach(function (i) {
            if ((i.total || 0) > max) {
                max = i.total;
            }
        });

        var colorBarra = opts.color || PALETA[0];
        var esPagina = opts.tipoImagen === 'pagina';
        var anchoMini = opts.anchoMiniatura || (esPagina ? 140 : 80);
        var claseThumb = esPagina ? 'thumb thumb-pagina' : 'thumb';

        cont.innerHTML = items.map(function (it) {
            var pct = Math.round(((it.total || 0) / max) * 100);
            var img = it.imagen
                ? '<img class="' + claseThumb + '" loading="lazy" alt="" src="' + miniaturaUrl(it.imagen, anchoMini) + '">'
                : '<div class="' + claseThumb + '"></div>';
            var meta = it.subtitulo ? '<div class="meta">' + escapar(it.subtitulo) + '</div>' : '';
            return '<div class="fila">' + img +
                '<div class="info">' +
                '<div class="titulo" title="' + escapar(it.titulo) + '">' + escapar(it.titulo) + '</div>' +
                meta +
                '<div class="barra"><span style="width:' + pct + '%;background:' + colorBarra + '"></span></div>' +
                '</div>' +
                '<div class="valor">' + escapar(it.total) + '</div>' +
                '</div>';
        }).join('');
    }

    // -----------------------------------------------------------------
    // Redimensionado
    // -----------------------------------------------------------------
    function resizeAll() {
        Object.keys(charts).forEach(function (id) {
            if (charts[id]) {
                charts[id].resize();
            }
        });
    }

    if (typeof window !== 'undefined') {
        window.addEventListener('resize', function () {
            resizeAll();
        });
    }

    return {
        PALETA: PALETA,
        color: color,
        init: init,
        barras: barras,
        barrasH: barrasH,
        dona: dona,
        treemap: treemap,
        linea: linea,
        miniaturaUrl: miniaturaUrl,
        listaMiniaturas: listaMiniaturas,
        resizeAll: resizeAll
    };
})();
