<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    <div class="panel-page">
        <div class="panel-wrap">
            <header class="panel-head">
                <div>
                    <h1>Métricas y Estadísticas</h1>
                    <p>Comportamiento de los visitantes del portal. Datos anónimos y agregados.</p>
                </div>

                <div class="panel-filtros">
                    <label for="metricasRango">Rango:</label>
                    <select id="metricasRango">
                        <option value="7">Últimos 7 días</option>
                        <option value="30" selected>Últimos 30 días</option>
                        <option value="90">Últimos 90 días</option>
                        <option value="365">Último año</option>
                    </select>
                    <button type="button" id="metricasRefresh" class="btn-panel"><i class="fas fa-rotate"></i> Actualizar</button>
                </div>
            </header>

            <div id="metricasEstado" class="panel-estado"></div>

            <section class="panel-kpis" id="kpiGrid"></section>

            <section class="panel-grid">
                <div class="panel-card">
                    <h3>Revistas más abiertas</h3>
                    <div class="lista-mini" id="listaRevistas"></div>
                </div>

                <div class="panel-card">
                    <h3>Participación por revista</h3>
                    <div class="panel-chart" id="chartTreemap"></div>
                </div>

                <div class="panel-card">
                    <h3>Dispositivos</h3>
                    <div class="panel-chart" id="chartDispositivos"></div>
                </div>

                <div class="panel-card">
                    <h3>Sistemas operativos</h3>
                    <div class="panel-chart" id="chartSistemas"></div>
                </div>

                <div class="panel-card">
                    <h3>Navegadores</h3>
                    <div class="panel-chart" id="chartNavegadores"></div>
                </div>

                <div class="panel-card">
                    <h3>Páginas leídas por sesión</h3>
                    <div class="panel-chart" id="chartHistograma"></div>
                </div>

                <div class="panel-card wide">
                    <h3>Visitas al portal por día</h3>
                    <div class="panel-chart" id="chartVisitasDia"></div>
                </div>

                <div class="panel-card wide">
                    <h3>Tiempo promedio de visualización por día (segundos)</h3>
                    <div class="panel-chart" id="chartTiempoDia"></div>
                </div>

                <div class="panel-card wide">
                    <h3>Horas pico de uso</h3>
                    <div class="panel-chart" id="chartHoras"></div>
                </div>

                <div class="panel-card">
                    <h3>Revistas en favoritos</h3>
                    <div class="lista-mini" id="listaFavoritos"></div>
                </div>

                <div class="panel-card wide">
                    <h3>Páginas más leídas por revista</h3>
                    <p class="panel-card-sub">Revistas ordenadas por lecturas y sus páginas más vistas en el periodo.</p>
                    <div class="pag-lista" id="listaPaginasRevistas"></div>
                </div>
            </section>

            <p class="panel-nota">
                Los datos se recolectan de forma anónima: la IP se almacena únicamente como hash y los
                visitantes se identifican con un token aleatorio. Los bots se excluyen de las gráficas.
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        window.ITIN_METRICAS_DATA_URL = "{{ route('admin.metricas.data') }}";
        window.ITIN_MINIATURA_URL = "{{ route('admin.media.miniatura') }}";
    </script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.1/dist/echarts.min.js"></script>
    <script src="{{ asset('js/graficas-itinews.js') }}"></script>
    <script src="{{ asset('js/metricas-admin.js') }}"></script>
    @endpush
</x-app-layout>
