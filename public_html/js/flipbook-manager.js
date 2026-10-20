class FlipbookManager {
    constructor() {
        this.filtroActivo = 'all';
        this.isMobile = this.detectMobile();
        this.favoritos = new Set();
        this.revistasData = [];
        this.estadisticas = {
            revistasVistas: new Set(),
            paginasLeidas: 0,
            tiempoLectura: 0,
            ultimaVisita: null,
            historial: [],
            revistasDetalle: {}
        };

        this.seccionesContainer = document.getElementById('secciones-especiales');
        this.contenidoInicio = document.getElementById('contenido-inicio'); 
        this.seccionActiva = null;

        this.inicializar = this.inicializar.bind(this);
        this.mostrarPreview = this.mostrarPreview.bind(this);
        this.handleGaleriaClick = this.handleGaleriaClick.bind(this);
        this.toggleFavorito = this.toggleFavorito.bind(this);
        this.filtrarPorAnio = this.filtrarPorAnio.bind(this);
        this.cargarRevistasDesdeHTML = this.cargarRevistasDesdeHTML.bind(this);
        this.configurarTema = this.configurarTema.bind(this);
        this.registrarVisita = this.registrarVisita.bind(this);
        this.resetearEstadisticas = this.resetearEstadisticas.bind(this);
        this.buscarRevistas = this.buscarRevistas.bind(this);
        this.mostrarRevistaDelMes = this.mostrarRevistaDelMes.bind(this);
    }

    detectMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    cargarRevistasDesdeHTML() {
        const revistaElements = document.querySelectorAll('.revista');
        this.revistasData = [];
        
        revistaElements.forEach((element, index) => {
            this.revistasData.push({
                id: element.dataset.idRevista,
                titulo: element.dataset.titulo,
                descripcion: element.dataset.descripcion,
                portada: element.dataset.portada,
                urlVisor: element.dataset.urlVisor,
                anio: element.dataset.estacion,
                element: element
            });
        });
        
        this.actualizarContadorRevistas();
        this.mostrarRevistaDelMes();
    }

    actualizarContadorRevistas() {
        const countElement = document.getElementById('count-all');
        if (countElement) {
            countElement.textContent = this.revistasData.length;
        }
    }

    // ============================================
    // REVISTA DEL MES
    // ============================================
    mostrarRevistaDelMes() {
        const container = document.getElementById('revista-del-mes');
        if (!container || this.revistasData.length === 0) return;

        const indice = Math.floor(Math.random() * this.revistasData.length);
        const revista = this.revistasData[indice];

        container.innerHTML = `
            <div class="revista-destacada-card">
                <div class="revista-destacada-badge">
                    <i class="fas fa-star"></i> Revista Destacada del Mes
                </div>
                <div class="revista-destacada-content">
                    <div class="revista-destacada-cover">
                        <img src="${revista.portada}" alt="${revista.titulo}" 
                             onclick="flipbookManager.registrarVisita('${revista.id}'); flipbookManager.mostrarPreviewFromData('${revista.id}')">
                    </div>
                    <div class="revista-destacada-info">
                        <h3>${revista.titulo}</h3>
                        <span class="revista-destacada-anio">${revista.anio}</span>
                        <p>${revista.descripcion ? revista.descripcion.substring(0, 150) + '...' : 'Descubre esta increíble revista académica.'}</p>
                        <button class="btn-leer" onclick="flipbookManager.registrarVisita('${revista.id}'); window.location.href='${revista.urlVisor}'">
                            <i class="fas fa-book-open"></i> Leer Ahora
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.style.display = 'block';
    }

    mostrarPreviewFromData(revistaId) {
        const revista = this.revistasData.find(r => r.id == revistaId);
        if (revista && revista.element) {
            this.mostrarPreview(revista.element);
        }
    }

    // ============================================
    // BÚSQUEDA DE REVISTAS - CORREGIDA
    // ============================================
    buscarRevistas(termino) {
        const revistas = document.querySelectorAll('.revista');
        let encontrados = 0;
        const terminoLower = termino.toLowerCase().trim();
    
        revistas.forEach(revista => {
            // Buscar en TODO el texto visible de la revista
            const textoCompleto = revista.textContent.toLowerCase();
            
            if (!terminoLower || textoCompleto.includes(terminoLower)) {
                revista.style.display = 'block';
                revista.style.opacity = '1';
                revista.style.transform = 'scale(1)';
                encontrados++;
            } else {
                revista.style.opacity = '0';
                revista.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    revista.style.display = 'none';
                }, 300);
            }
        });
    
        // Actualizar contador
        const resultadosSpan = document.getElementById('resultados-busqueda');
        if (resultadosSpan) {
            resultadosSpan.textContent = terminoLower ? `${encontrados} resultado${encontrados !== 1 ? 's' : ''}` : '';
        }
        
        console.log(`Búsqueda: "${termino}" → ${encontrados} resultados`);
    }

    // ============================================
    // FAVORITOS
    // ============================================
    toggleFavorito(revistaId, event) {
        if (event) event.stopPropagation();

        const hayUsuario = document.querySelector('meta[name="user-id"]');
        const btn = document.querySelector(`.favorito-btn[data-revista-id="${revistaId}"]`);

        if (this.favoritos.has(revistaId)) {
            this.favoritos.delete(revistaId);
            if (hayUsuario) this.removerFavoritoBD(revistaId);
            if (btn) {
                btn.classList.remove('favorito');
                btn.innerHTML = '<i class="far fa-heart"></i>';
                btn.setAttribute('title', 'Añadir a favoritos');
            }
        } else {
            this.favoritos.add(revistaId);
            if (hayUsuario) this.guardarFavoritoBD(revistaId);
            if (btn) {
                btn.classList.add('favorito');
                btn.innerHTML = '<i class="fas fa-heart"></i>';
                btn.setAttribute('title', 'Quitar de favoritos');
                // Animación de corazón
                btn.style.animation = 'none';
                btn.offsetHeight;
                btn.style.animation = 'heartBeat 0.3s ease';
            }
        }

        this.actualizarInterfazFavoritos();
        this.guardarEnLocalStorage();

        if (this.seccionActiva === 'favoritos') {
            this.mostrarSeccion('favoritos');
        }
    }

    async guardarFavoritoBD(revistaId) {
        try {
            await fetch('/api/favoritos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ revista_id: revistaId })
            });
        } catch (error) {
            console.error('Error guardando favorito:', error);
        }
    }

    async removerFavoritoBD(revistaId) {
        try {
            await fetch('/api/favoritos/' + revistaId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
        } catch (error) {
            console.error('Error removiendo favorito:', error);
        }
    }

    // Carga los favoritos guardados en la base de datos (usuario autenticado).
    async cargarFavoritosDesdeBD() {
        try {
            const respuesta = await fetch('/api/mis-favoritos', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            if (!respuesta.ok) return;

            const datos = await respuesta.json();
            if (!Array.isArray(datos)) return;

            datos.forEach(favorito => {
                if (favorito && favorito.revista_id != null) {
                    this.favoritos.add(favorito.revista_id.toString());
                }
            });

            this.actualizarInterfazFavoritos();
            this.guardarEnLocalStorage();
        } catch (error) {
            console.error('Error cargando favoritos desde BD:', error);
        }
    }

    actualizarInterfazFavoritos() {
        const favoritosCount = document.getElementById('favoritosCount');
        if (favoritosCount) favoritosCount.textContent = this.favoritos.size;

        const statsCount = document.getElementById('favoritos-count');
        if (statsCount) statsCount.textContent = this.favoritos.size;

        document.querySelectorAll('.favorito-btn').forEach(btn => {
            const revistaEl = btn.closest('.revista');
            if (revistaEl) {
                const revistaId = revistaEl.dataset.idRevista;
                if (this.favoritos.has(revistaId.toString())) {
                    btn.classList.add('favorito');
                    btn.innerHTML = '<i class="fas fa-heart"></i>';
                    btn.setAttribute('title', 'Quitar de favoritos');
                } else {
                    btn.classList.remove('favorito');
                    btn.innerHTML = '<i class="far fa-heart"></i>';
                    btn.setAttribute('title', 'Añadir a favoritos');
                }
            }
        });
    }

    // ============================================
    // FILTROS POR AÑO
    // ============================================
    filtrarPorAnio(anio) {
        const revistas = document.querySelectorAll('.revista');
        revistas.forEach(revista => {
            const revistaAnio = revista.dataset.estacion;
            if (anio === 'all' || revistaAnio === anio) {
                revista.style.display = 'block';
                setTimeout(() => {
                    revista.style.opacity = '1';
                    revista.style.transform = 'scale(1)';
                }, 100);
            } else {
                revista.style.opacity = '0';
                revista.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    revista.style.display = 'none';
                }, 300);
            }
        });
    }

    // ============================================
    // REGISTRO DE VISITAS
    // ============================================
    registrarVisita(revistaId) {
        this.estadisticas.revistasVistas.add(revistaId.toString());
        this.estadisticas.ultimaVisita = new Date().toISOString();
        
        const revista = this.revistasData.find(r => r.id == revistaId);
        
        if (!this.estadisticas.revistasDetalle) {
            this.estadisticas.revistasDetalle = {};
        }
        
        const prev = this.estadisticas.revistasDetalle[revistaId.toString()] || {};
        this.estadisticas.revistasDetalle[revistaId.toString()] = {
            id: revistaId.toString(),
            titulo: revista?.titulo || prev.titulo || 'Revista ITINEWS',
            portada: revista?.portada || prev.portada || '',
            urlVisor: revista?.urlVisor || prev.urlVisor || `/revistas/${revistaId}`,
            anio: revista?.anio || prev.anio || '',
            tiempo: prev.tiempo || 0,
            paginas: prev.paginas || 0,
            visitas: (prev.visitas || 0) + 1,
            ultimaLectura: new Date().toISOString()
        };

        if (revista) {
            if (this.estadisticas.historial.length === 0 || this.estadisticas.historial[0].revista !== revista.titulo) {
                this.estadisticas.historial.unshift({
                    revista: revista.titulo,
                    fecha: new Date().toISOString(),
                    tipo: 'vista'
                });
            }
        }

        if (this.estadisticas.historial.length > 10) {
            this.estadisticas.historial = this.estadisticas.historial.slice(0, 10);
        }

        this.guardarEnLocalStorage();
        
        if (this.seccionActiva === 'recientes') {
            setTimeout(() => this.mostrarSeccion('recientes'), 100);
        }
    }

    resetearEstadisticas() {
        this.estadisticas = {
            revistasVistas: new Set(),
            paginasLeidas: 0,
            tiempoLectura: 0,
            ultimaVisita: null,
            historial: [],
            revistasDetalle: {}
        };
        this.guardarEnLocalStorage();
        
        if (this.seccionActiva === 'estadisticas') {
            this.mostrarSeccion('estadisticas');
        }
        if (this.seccionActiva === 'recientes') {
            this.mostrarSeccion('recientes');
        }
    }

    // ============================================
    // SECCIONES
    // ============================================
    mostrarSeccion(tipo) {
        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        const botonActivo = document.querySelector(`[data-tipo="${tipo}"]`);
        if (botonActivo) {
            botonActivo.classList.add('active');
        }

        if (this.contenidoInicio) {
            this.contenidoInicio.style.display = 'none';
        }
        
        const revistaDelMes = document.getElementById('revista-del-mes');
        const searchBar = document.querySelector('.search-bar-container');
        if (revistaDelMes) revistaDelMes.style.display = 'none';
        if (searchBar) searchBar.style.display = 'none';
        
        if (this.seccionesContainer) {
            this.seccionesContainer.style.display = 'block';
        }

        let contenido = '';
        
        switch(tipo) {
            case 'favoritos':
                contenido = this.getContenidoFavoritos();
                this.seccionActiva = 'favoritos';
                break;
            case 'recientes':
                contenido = this.getContenidoRecientes();
                this.seccionActiva = 'recientes';
                break;
            case 'destacadas':
                contenido = this.getContenidoDestacadas();
                this.seccionActiva = 'destacadas';
                break;
            case 'estadisticas':
                this.cargarDesdeLocalStorage();
                contenido = this.getContenidoEstadisticas();
                this.seccionActiva = 'estadisticas';
                break;
            case 'acerca':
                contenido = this.getContenidoAcerca();
                this.seccionActiva = 'acerca';
                break;
            default:
                this.volverAGaleria();
                return;
        }

        if (this.seccionesContainer) {
            this.seccionesContainer.innerHTML = contenido;
            this.reconfigurarEventosRevistas();
        }

        setTimeout(() => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 50);
    }

    volverAGaleria() {
        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        if (this.contenidoInicio) {
            this.contenidoInicio.style.display = 'block';
            this.contenidoInicio.style.opacity = '0';
            setTimeout(() => this.contenidoInicio.style.opacity = '1', 50);
        }
        
        const revistaDelMes = document.getElementById('revista-del-mes');
        const searchBar = document.querySelector('.search-bar-container');
        if (revistaDelMes) revistaDelMes.style.display = 'block';
        if (searchBar) searchBar.style.display = 'block';
        
        if (this.seccionesContainer) {
            this.seccionesContainer.style.display = 'none';
            this.seccionesContainer.innerHTML = '';
        }
        
        this.seccionActiva = null;

        setTimeout(() => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 50);
    }

    getContenidoFavoritos() {
        const favoritosCount = document.getElementById('favoritosCount')?.textContent || '0';
        const favoritosHTML = this.generarGaleriaFavoritos();
        
        return `
            <div class="seccion-header">
                <h2 class="seccion-titulo">Mis Favoritos</h2>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Todas las Revistas
                </button>
            </div>
            
            ${favoritosCount === '0' ? `
                <div class="tarjeta-seccion tarjeta-favoritos">
                    <div class="tarjeta-icono">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="tarjeta-contenido">
                        <h3>Mis Favoritos</h3>
                        <p>Aún no tienes revistas en favoritos. ¡Comienza a explorar y marca tus revistas preferidas!</p>
                        <span class="tarjeta-accion" onclick="flipbookManager.volverAGaleria()">
                            Explorar revistas <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            ` : `
                <div class="galeria" id="galeria-favoritos">
                    ${favoritosHTML}
                </div>
                <div class="contador-seccion">${favoritosCount} revistas en favoritos</div>
            `}
        `;
    }

    getContenidoRecientes() {
        const recientesHTML = this.generarGaleriaRecientes();
        const count = this.estadisticas.historial.length;
        
        return `
            <div class="seccion-header">
                <h2 class="seccion-titulo">Recientemente Vistas</h2>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Todas las Revistas
                </button>
            </div>
            
            ${count === 0 ? `
                <div class="tarjeta-seccion tarjeta-recientes">
                    <div class="tarjeta-icono">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="tarjeta-contenido">
                        <h3>Recientemente Vistas</h3>
                        <p>Aún no has visto ninguna revista. ¡Comienza a explorar nuestro catálogo!</p>
                        <span class="tarjeta-accion" onclick="flipbookManager.volverAGaleria()">
                            Explorar revistas <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            ` : `
                <div class="galeria" id="galeria-recientes">
                    ${recientesHTML}
                </div>
                <div class="contador-seccion">${count} revistas vistas recientemente</div>
            `}
        `;
    }

    getContenidoDestacadas() {
        const destacadas = Array.from(this.estadisticas.revistasVistas)
            .slice(0, 6)
            .map(id => this.revistasData.find(r => r.id == id))
            .filter(r => r !== undefined);
        
        if (destacadas.length < 6) {
            const adicionales = this.revistasData
                .filter(r => !destacadas.includes(r))
                .slice(0, 6 - destacadas.length);
            destacadas.push(...adicionales);
        }
        
        let html = '';
        destacadas.forEach(revista => {
            html += this.generarTarjetaRevista(revista);
        });

        return `
            <div class="seccion-header">
                <h2 class="seccion-titulo">Revistas Destacadas</h2>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Todas las Revistas
                </button>
            </div>
            <div class="galeria" id="galeria-destacadas">
                ${html || '<p class="sin-contenido">No hay revistas destacadas</p>'}
            </div>
            <div class="contador-seccion">Las revistas más populares de nuestra colección</div>
        `;
    }

    formatearTiempoLectura(segundos) {
        if (!segundos || segundos <= 0) return '0 min';
        const s = Math.round(segundos);
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const seg = s % 60;
        if (h > 0) {
            return `${h}h ${m}m`;
        }
        if (m > 0) {
            return `${m} min`;
        }
        return `${seg} seg`;
    }

    getContenidoEstadisticas() {
        const favoritosCount = document.getElementById('favoritosCount')?.textContent || '0';
        const tiempoTotalFormato = this.formatearTiempoLectura(this.estadisticas.tiempoLectura);
        const totalVistas = this.estadisticas.revistasVistas ? this.estadisticas.revistasVistas.size : 0;

        // Consolidar lista de revistas leídas combinando revistasDetalle y revistasData
        const detalleMap = this.estadisticas.revistasDetalle || {};
        const revistasIds = new Set([
            ...(this.estadisticas.revistasVistas ? [...this.estadisticas.revistasVistas] : []),
            ...Object.keys(detalleMap)
        ]);

        const listaRevistasLeidas = [];
        revistasIds.forEach(id => {
            const dataGaleria = this.revistasData.find(r => r.id == id);
            const detalle = detalleMap[id] || {};
            listaRevistasLeidas.push({
                id: id,
                titulo: dataGaleria?.titulo || detalle.titulo || 'Revista Académica',
                portada: dataGaleria?.portada || detalle.portada || '',
                urlVisor: dataGaleria?.urlVisor || detalle.urlVisor || `/revistas/${id}`,
                anio: dataGaleria?.anio || detalle.anio || '',
                tiempo: detalle.tiempo || 0,
                paginas: detalle.paginas || 0,
                visitas: detalle.visitas || 1,
                ultimaLectura: detalle.ultimaLectura || null
            });
        });

        // Ordenar por tiempo de lectura descendente (la que más tiempo dedicó primero)
        listaRevistasLeidas.sort((a, b) => (b.tiempo || 0) - (a.tiempo || 0));

        // Revista más leída
        const topRevista = (listaRevistasLeidas.length > 0 && (listaRevistasLeidas[0].tiempo > 0 || listaRevistasLeidas[0].visitas > 1))
            ? listaRevistasLeidas[0]
            : null;

        // Renderizar lista o empty state
        let revistasCardsHTML = '';
        if (listaRevistasLeidas.length > 0) {
            revistasCardsHTML = listaRevistasLeidas.map(rev => {
                const tiempoRevista = this.formatearTiempoLectura(rev.tiempo);
                let fechaStr = 'Recientemente';
                if (rev.ultimaLectura) {
                    try {
                        const d = new Date(rev.ultimaLectura);
                        fechaStr = d.toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' });
                    } catch(e) {}
                }

                return `
                    <div class="stats-revista-card">
                        <div class="stats-revista-thumb">
                            ${rev.portada 
                                ? `<img src="${rev.portada}" alt="Portada de ${rev.titulo}">` 
                                : `<div style="width:100%; height:100%; background: #1f5b4f; display:flex; align-items:center; justify-content:center; color:#fff;"><i class="fas fa-book"></i></div>`}
                        </div>
                        <div class="stats-revista-details">
                            <div>
                                <h4 class="stats-revista-title" title="${rev.titulo}">${rev.titulo}</h4>
                                <div class="stats-revista-meta">
                                    ${rev.anio ? `<span><i class="fas fa-calendar-alt"></i> Edición ${rev.anio}</span>` : ''}
                                    <span><i class="fas fa-clock"></i> <strong>${tiempoRevista}</strong> dedicados</span>
                                    <span><i class="fas fa-history"></i> Leída: ${fechaStr}</span>
                                </div>
                            </div>
                            <a href="${rev.urlVisor}" class="stats-btn-continuar">
                                <i class="fas fa-book-open"></i> Continuar Leyendo
                            </a>
                        </div>
                    </div>
                `;
            }).join('');
        }

        return `
            <div class="seccion-header">
                <h2 class="seccion-titulo"><i class="fas fa-chart-line"></i> Mis Estadísticas de Lectura</h2>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Todas las Revistas
                </button>
            </div>

            <div class="stats-dashboard">
                <!-- KPI CARDS -->
                <div class="stats-summary-grid">
                    <div class="stats-kpi-card">
                        <div class="stats-kpi-icon icon-vistas">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="stats-kpi-info">
                            <h4>Revistas Vistas</h4>
                            <div class="kpi-num">${totalVistas}</div>
                        </div>
                    </div>

                    <div class="stats-kpi-card">
                        <div class="stats-kpi-icon icon-tiempo">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="stats-kpi-info">
                            <h4>Tiempo Total</h4>
                            <div class="kpi-num">${tiempoTotalFormato}</div>
                        </div>
                    </div>

                    <div class="stats-kpi-card">
                        <div class="stats-kpi-icon icon-paginas">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stats-kpi-info">
                            <h4>Páginas Leídas</h4>
                            <div class="kpi-num">${this.estadisticas.paginasLeidas || 0}</div>
                        </div>
                    </div>

                    <div class="stats-kpi-card">
                        <div class="stats-kpi-icon icon-favoritos">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stats-kpi-info">
                            <h4>Tus Favoritos</h4>
                            <div class="kpi-num">${favoritosCount}</div>
                        </div>
                    </div>
                </div>

                <!-- SPOTLIGHT: REVISTA MÁS LEÍDA -->
                ${topRevista ? `
                    <div class="stats-spotlight-card">
                        <div class="stats-spotlight-cover">
                            ${topRevista.portada 
                                ? `<img src="${topRevista.portada}" alt="${topRevista.titulo}">` 
                                : `<div style="width:100%; height:100%; background: #1f5b4f; display:flex; align-items:center; justify-content:center; color:#fff;"><i class="fas fa-book"></i></div>`}
                        </div>
                        <div class="stats-spotlight-info">
                            <span class="stats-spotlight-badge">
                                <i class="fas fa-trophy"></i> Revista a la que más tiempo has dedicado
                            </span>
                            <h3 style="margin: 0 0 6px 0; font-size: 1.35rem; color: #164236;">${topRevista.titulo}</h3>
                            <p style="margin: 0 0 12px 0; color: var(--text-secondary); font-size: 0.92rem;">
                                Has invertido <strong>${this.formatearTiempoLectura(topRevista.tiempo)}</strong> leyendo esta publicación académica.
                            </p>
                            <a href="${topRevista.urlVisor}" class="stats-btn-continuar" style="padding: 9px 18px; font-size: 0.92rem;">
                                <i class="fas fa-book-open"></i> Continuar Leyendo
                            </a>
                        </div>
                    </div>
                ` : ''}

                <!-- LISTA DE REVISTAS EXPLORADAS -->
                <div class="stats-section-title">
                    <i class="fas fa-list-check"></i> Revistas que has explorado (${listaRevistasLeidas.length})
                </div>

                ${listaRevistasLeidas.length > 0 ? `
                    <div class="stats-revistas-grid">
                        ${revistasCardsHTML}
                    </div>
                ` : `
                    <div class="tarjeta-seccion" style="text-align: center; padding: 40px 20px;">
                        <i class="fas fa-book-open" style="font-size: 3rem; color: #1f5b4f; opacity: 0.6; margin-bottom: 15px;"></i>
                        <h3 style="margin-bottom: 8px;">Aún no has explorado ninguna revista</h3>
                        <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 20px auto;">
                            Abre cualquier revista del catálogo para comenzar tu lectura. Guardaremos tu tiempo, páginas y avance automáticamente.
                        </p>
                        <button class="btn-volver" style="margin: 0 auto; display: inline-flex;" onclick="flipbookManager.volverAGaleria()">
                            <i class="fas fa-search"></i> Explorar Catálogo de Revistas
                        </button>
                    </div>
                `}

                <!-- ACCIONES INFERIORES -->
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; margin-top: 25px; padding: 15px 0;">
                    <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                        <i class="fas fa-arrow-left"></i> Explorar más revistas
                    </button>
                    
                    <button onclick="if(confirm('¿Estás seguro de que deseas reiniciar tus estadísticas de lectura?')) flipbookManager.resetearEstadisticas()" 
                            style="background: transparent; border: 1px solid rgba(231, 76, 60, 0.4); color: #e74c3c; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-trash-alt"></i> Reiniciar estadísticas
                    </button>
                </div>
            </div>
        `;
    }

    getContenidoAcerca() {
        return `
            <div class="seccion-header">
                <h2 class="seccion-titulo">Acerca del Portal</h2>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Todas las Revistas
                </button>
            </div>
            <div class="tarjeta-seccion tarjeta-acerca">
                <div class="tarjeta-icono">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="tarjeta-contenido">
                    <h3>Acerca del Portal</h3>
                    <p>El Portal ITINEWS es una plataforma dedicada a la difusión de revistas académicas realizadas por la División de Ingeniería en Tecnologías de la Información e Innovación Digital de la Universidad Politécnica del Valle de Toluca.</p>

                    <h3><i class="fas fa-star"></i> Características</h3>
                        <ul>
                            <li>- Visualizador de revistas interactivo</li>
                            <li>- Zoom y navegación avanzada</li>
                            <li>- Modo pantalla completa</li>
                            <li>- Seguimiento de estadísticas de lectura</li>
                            <li>- Interfaz responsive</li>
                            <li>- Modo oscuro/claro</li>
                            <li>- Sistema de favoritos</li>
                        </ul>
                    <h3><i class="fas fa-tools"></i> Tecnologías</h3>
                        <p>Desarrollado con HTML5, CSS3, JavaScript y la librería StPageFlip para una experiencia de lectura óptima.</p>
                    <span class="tarjeta-accion" onclick="flipbookManager.volverAGaleria()">
                        Comenzar a explorar <i class="fas fa-arrow-right"></i>
                    </span>
                </div>
            </div>
        `;
    }

    generarGaleriaFavoritos() {
        let html = '';
        this.revistasData.forEach(revista => {
            if (this.favoritos.has(revista.id.toString())) {
                html += this.generarTarjetaRevista(revista);
            }
        });
        return html || '<p class="sin-contenido">No hay revistas en favoritos</p>';
    }

    generarGaleriaRecientes() {
        let html = '';
        const recientesIds = this.estadisticas.historial.map(h => {
            const revista = this.revistasData.find(r => r.titulo === h.revista);
            return revista ? revista.id : null;
        }).filter(id => id !== null);

        const idsUnicos = [...new Set(recientesIds)];
        idsUnicos.forEach(revistaId => {
            const revista = this.revistasData.find(r => r.id == revistaId);
            if (revista) html += this.generarTarjetaRevista(revista);
        });
        return html || '<p class="sin-contenido">No hay revistas recientes</p>';
    }

    generarTarjetaRevista(revista) {
        const esFavorito = this.favoritos.has(revista.id.toString());
        return `
            <div class="revista" 
                 data-id-revista="${revista.id}" 
                 data-titulo="${revista.titulo}" 
                 data-descripcion="${revista.descripcion}"
                 data-portada="${revista.portada}"
                 data-url-visor="${revista.urlVisor}"
                 data-estacion="${revista.anio}">
                
                <div class="revista-img-container">
                    <img src="${revista.portada}" 
                         alt="Portada de ${revista.titulo}" 
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="imagen-error" style="display: none;">
                        <i class="fas fa-image"></i>
                        <span>Imagen no disponible</span>
                    </div>
                </div>
                
                <p>${revista.titulo}</p>
                <span class="estacion-badge">${revista.anio}</span>
                
                <button class="favorito-btn ${esFavorito ? 'favorito' : ''}" 
                        title="${esFavorito ? 'Quitar de favoritos' : 'Añadir a favoritos'}" 
                        data-revista-id="${revista.id}">
                    <i class="${esFavorito ? 'fas' : 'far'} fa-heart"></i>
                </button>
            </div>
        `;
    }

    // ============================================
    // TEMA
    // ============================================
    configurarTema() {
        const themeToggle = document.getElementById('btnTema');
        const body = document.body;
        
        if (!themeToggle) return;
        
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark-mode') {
            body.classList.add('dark-mode');
            body.classList.remove('light-mode');
            this.updateThemeIcon(true);
        }
        
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            body.classList.toggle('light-mode');
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark-mode' : 'light-mode');
            this.updateThemeIcon(isDark);
        });
    }

    updateThemeIcon(isDark) {
        const themeToggle = document.getElementById('btnTema');
        if (!themeToggle) return;
        const icon = themeToggle.querySelector('i');
        const text = themeToggle.querySelector('span');
        if (isDark) {
            icon.className = 'fas fa-sun';
            text.textContent = 'Modo Claro';
        } else {
            icon.className = 'fas fa-moon';
            text.textContent = 'Modo Oscuro';
        }
    }

    // ============================================
    // EVENTOS Y PREVIEW
    // ============================================
    handleGaleriaClick(e) {
        const revistaEl = e.target.closest('.revista');
        if (revistaEl && !e.target.closest('.favorito-btn')) {
            const revistaId = revistaEl.dataset.idRevista;
            this.registrarVisita(revistaId);
            this.mostrarPreview(revistaEl);
        }
    }

    reconfigurarEventosRevistas() {
        const galeriasDinamicas = document.querySelectorAll('#galeria-favoritos, #galeria-recientes, #galeria-destacadas');
        galeriasDinamicas.forEach(galeria => {
            galeria.addEventListener('click', (e) => {
                const revistaEl = e.target.closest('.revista');
                if (revistaEl && !e.target.closest('.favorito-btn')) {
                    const revistaId = revistaEl.dataset.idRevista;
                    this.registrarVisita(revistaId);
                    this.mostrarPreview(revistaEl);
                }
                const favoritoBtn = e.target.closest('.favorito-btn');
                if (favoritoBtn) {
                    const revistaId = favoritoBtn.getAttribute('data-revista-id');
                    this.toggleFavorito(revistaId, e);
                }
            });
        });
    }

    configurarSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebar = document.getElementById('sidebar');
        
        if (sidebarToggle && sidebarOverlay && sidebar) {
            const toggleSidebar = () => {
                const isActive = sidebar.classList.contains('active');
                if (isActive) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                } else {
                    sidebar.classList.add('active');
                    sidebarOverlay.classList.add('active');
                    document.body.classList.add('sidebar-open');
                }
            };
            sidebarToggle.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }
        this.configurarBotonesSidebar();
    }

    configurarBotonesSidebar() {
        document.addEventListener('click', (e) => {
            const button = e.target.closest('.quick-btn');
            if (button) {
                e.preventDefault();
                e.stopPropagation();
                const tipo = button.dataset.tipo;
                this.mostrarSeccion(tipo);
            }
        });
    }

    configurarFiltros() {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const anio = btn.getAttribute('data-filter');
                if (anio) {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    this.filtroActivo = anio;

                    // Si el usuario está en alguna sección especial (Mis Estadísticas, Favoritos, etc.), volver a la galería primero
                    if (this.seccionActiva) {
                        this.volverAGaleria();
                    }
                    this.filtrarPorAnio(anio);

                    // Si estamos en móvil y el sidebar está abierto, cerrarlo
                    const sidebar = document.getElementById('sidebar');
                    const sidebarOverlay = document.getElementById('sidebarOverlay');
                    if (sidebar && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                        document.body.classList.remove('sidebar-open');
                    }
                }
            });
        });
    }

    mostrarPreview(revistaElement) {
        const previewContainer = document.getElementById('preview-container');
        const previewImg = document.getElementById('preview-img');
        const previewTitle = document.getElementById('preview-title');
        const previewDesc = document.getElementById('preview-desc');
        const startReadingBtn = document.getElementById('start-reading-btn');
        const previewBadge = document.getElementById('preview-badge');

        if (!previewContainer) return;

        // Asegurar que el modal esté directo en el body para evitar trampas de transform o scroll
        if (previewContainer.parentElement !== document.body) {
            document.body.appendChild(previewContainer);
        }

        const titulo = revistaElement.dataset.titulo || 'Sin título';
        const descripcion = revistaElement.dataset.descripcion;
        const portada = revistaElement.dataset.portada;
        const urlVisor = revistaElement.dataset.urlVisor;
        const anio = revistaElement.dataset.estacion;
        
        previewImg.src = portada;
        previewImg.alt = `Portada de ${titulo}`;
        previewTitle.textContent = titulo;
        previewDesc.textContent = (descripcion && descripcion.trim() !== '') 
            ? descripcion 
            : 'Descripción no disponible por el momento. Te invitamos a explorar las páginas de esta edición.';
        startReadingBtn.href = urlVisor;

        if (previewBadge) {
            previewBadge.textContent = anio ? `Revista Académica • ${anio}` : 'Revista Académica';
        }

        // Bloquear scroll del fondo para evitar saltos y dobles scrolls en móviles
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';

        // Resetear desplazamientos internos del modal
        previewContainer.scrollTop = 0;
        const detailsBody = previewContainer.querySelector('.preview-details-body');
        if (detailsBody) detailsBody.scrollTop = 0;
        const previewContent = previewContainer.querySelector('.preview-content');
        if (previewContent) previewContent.scrollTop = 0;

        const cerrarPreview = () => {
            previewContainer.style.display = 'none';
            previewContainer.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
            document.removeEventListener('keydown', handleEscape);
        };
        const handleEscape = (e) => {
            if (e.key === 'Escape') cerrarPreview();
        };

        const btnCerrar = document.getElementById('preview-cerrar');
        if (btnCerrar) {
            btnCerrar.onclick = (e) => {
                e.stopPropagation();
                cerrarPreview();
            };
        }
        
        previewContainer.onclick = (e) => {
            if (e.target === previewContainer) cerrarPreview();
        };

        document.addEventListener('keydown', handleEscape);
        previewContainer.setAttribute('aria-hidden', 'false');
        previewContainer.style.display = 'flex';
        setTimeout(() => previewContainer.style.opacity = '1', 10);
    }

    // ============================================
    // LOCAL STORAGE
    // ============================================
    guardarEnLocalStorage() {
        try {
            localStorage.setItem('favoritos', JSON.stringify([...this.favoritos]));
            localStorage.setItem('estadisticas', JSON.stringify({
                ...this.estadisticas,
                revistasVistas: [...this.estadisticas.revistasVistas],
                revistasDetalle: this.estadisticas.revistasDetalle || {}
            }));
        } catch (e) { console.error('Error guardando en localStorage:', e); }
    }

    cargarDesdeLocalStorage() {
        try {
            const favoritosGuardados = localStorage.getItem('favoritos');
            if (favoritosGuardados) this.favoritos = new Set(JSON.parse(favoritosGuardados));

            const estadisticasGuardadas = localStorage.getItem('estadisticas');
            if (estadisticasGuardadas) {
                const stats = JSON.parse(estadisticasGuardadas);
                this.estadisticas = {
                    revistasVistas: new Set(stats.revistasVistas || []),
                    paginasLeidas: stats.paginasLeidas || 0,
                    tiempoLectura: stats.tiempoLectura || 0,
                    ultimaVisita: stats.ultimaVisita || null,
                    historial: stats.historial || [],
                    revistasDetalle: stats.revistasDetalle || {}
                };
            }
        } catch (e) { console.error('Error cargando localStorage:', e); }
    }

    // ============================================
    // BOTÓN VOLVER ARRIBA
    // ============================================
    configurarBotonScrollTop() {
        const btnScrollTop = document.getElementById('btnScrollTop');
        if (!btnScrollTop) return;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                btnScrollTop.classList.add('visible');
            } else {
                btnScrollTop.classList.remove('visible');
            }
        });

        btnScrollTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ============================================
    // CONFIGURAR BÚSQUEDA
    // ============================================
    configurarBusqueda() {
        const searchInput = document.getElementById('searchRevistas');
        if (!searchInput) return;

        // Búsqueda en tiempo real al escribir
        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.buscarRevistas(e.target.value);
            }, 300);
        });

        // Limpiar búsqueda con Escape
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.value = '';
                this.buscarRevistas('');
                searchInput.blur();
            }
        });

        // Si viene un término de búsqueda en la URL, aplicarlo
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        if (searchParam) {
            searchInput.value = searchParam;
            setTimeout(() => {
                this.buscarRevistas(searchParam);
            }, 500);
        }
    }

    // ============================================
    // INICIALIZACIÓN
    // ============================================
    async inicializar() {
        try {
            this.cargarDesdeLocalStorage();
            this.cargarRevistasDesdeHTML();
            this.configurarSidebar();
            this.configurarFiltros();
            this.configurarTema();
            this.configurarBotonScrollTop();
            this.configurarBusqueda();
            
            if (document.querySelector('meta[name="user-id"]') && typeof this.cargarFavoritosDesdeBD === 'function') {
                await this.cargarFavoritosDesdeBD();
            }

            const galeria = document.getElementById('galeria');
            if (galeria) {
                galeria.addEventListener('click', this.handleGaleriaClick);
            }

            document.querySelectorAll('.favorito-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const revistaId = btn.getAttribute('data-revista-id');
                    this.toggleFavorito(revistaId, e);
                });
            });

            this.actualizarInterfazFavoritos();
            console.log('[FLIPBOOK] Manager inicializado correctamente');

        } catch (error) {
            console.error('[FLIPBOOK] Error en inicialización:', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        window.flipbookManager = new FlipbookManager();
        window.flipbookManager.inicializar();
    } catch (error) {
        console.error('Error crítico creando FlipbookManager:', error);
    }
});