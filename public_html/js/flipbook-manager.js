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
                destacada: element.dataset.destacada === '1',
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
        const revistasFav = this.revistasData.filter(r => this.favoritos.has(r.id.toString()));
        const count = revistasFav.length;

        let gridHTML = '';
        if (count > 0) {
            gridHTML = revistasFav.map(r => this.generarTarjetaEditorial(r)).join('');
        }

        return `
            <div class="seccion-header">
                <div>
                    <h2 class="seccion-titulo"><i class="fas fa-heart" style="color: #c42040;"></i> Mis Revistas Favoritas</h2>
                    <span class="stats-section-subtitle">Tu selección personalizada para acceso inmediato</span>
                </div>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i> Volver a Todas las Revistas
                </button>
            </div>

            <div class="editorial-section-container">
                ${count === 0 ? `
                    <div class="editorial-empty-card">
                        <div class="editorial-empty-icon" style="color: #c42040;">
                            <i class="fas fa-heart-broken"></i>
                        </div>
                        <h3>No tienes revistas guardadas aún</h3>
                        <p>Cuando encuentres una edición que te interese, haz clic en el icono de corazón en su portada para tenerla siempre a la mano.</p>
                        <button class="btn-volver" style="margin: 0 auto; display: inline-flex;" onclick="flipbookManager.volverAGaleria()">
                            <i class="fas fa-compass"></i> Explorar Catálogo de Revistas
                        </button>
                    </div>
                ` : `
                    <div class="editorial-grid">
                        ${gridHTML}
                    </div>
                    <div class="stats-section-header" style="margin-top: 20px; justify-content: center;">
                        <span class="stats-section-subtitle"><i class="fas fa-bookmark"></i> Tienes <strong>${count}</strong> ${count === 1 ? 'revista guardada' : 'revistas guardadas'} en tus favoritos</span>
                    </div>
                `}
            </div>
        `;
    }

    getContenidoRecientes() {
        const recientesIds = this.estadisticas.historial.map(h => {
            const revista = this.revistasData.find(r => r.titulo === h.revista);
            return revista ? revista.id : null;
        }).filter(id => id !== null);

        const idsUnicos = [...new Set(recientesIds)];
        const revistasRecientes = idsUnicos
            .map(id => this.revistasData.find(r => r.id == id))
            .filter(r => r !== undefined);

        const count = revistasRecientes.length;

        let gridHTML = '';
        if (count > 0) {
            gridHTML = revistasRecientes.map(r => {
                const detalle = (this.estadisticas.revistasDetalle && this.estadisticas.revistasDetalle[r.id.toString()]) || {};
                const tiempoVal = (detalle.tiempo && detalle.tiempo > 0) 
                    ? detalle.tiempo 
                    : (this.estadisticas.tiempoLectura > 0 && count > 0 ? Math.round(this.estadisticas.tiempoLectura / count) : 0);
                const tiempo = this.formatearTiempoLectura(tiempoVal);
                const footerMeta = `
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <span class="meta-pill pill-tiempo"><i class="fas fa-clock"></i> ${tiempo}</span>
                        <span class="meta-pill"><i class="fas fa-check-circle"></i> Leída recientemente</span>
                    </div>
                `;
                return this.generarTarjetaEditorial(r, { footerMeta });
            }).join('');
        }

        return `
            <div class="seccion-header">
                <div>
                    <h2 class="seccion-titulo"><i class="fas fa-history" style="color: #1f5b4f;"></i> Recientemente Vistas</h2>
                    <span class="stats-section-subtitle">Continúa donde te quedaste en tus últimas sesiones</span>
                </div>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i> Volver a Todas las Revistas
                </button>
            </div>

            <div class="editorial-section-container">
                ${count === 0 ? `
                    <div class="editorial-empty-card">
                        <div class="editorial-empty-icon" style="color: #1f5b4f;">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <h3>Aún no has abierto revistas</h3>
                        <p>Las revistas académicas que vayas consultando en el visor interactivo aparecerán aquí organizadas cronológicamente.</p>
                        <button class="btn-volver" style="margin: 0 auto; display: inline-flex;" onclick="flipbookManager.volverAGaleria()">
                            <i class="fas fa-book-open"></i> Comenzar a Leer
                        </button>
                    </div>
                ` : `
                    <div class="editorial-grid">
                        ${gridHTML}
                    </div>
                    <div class="stats-section-header" style="margin-top: 20px; justify-content: center;">
                        <span class="stats-section-subtitle"><i class="fas fa-clock-rotate-left"></i> Has consultado <strong>${count}</strong> ${count === 1 ? 'revista recientemente' : 'revistas recientemente'}</span>
                    </div>
                `}
            </div>
        `;
    }

    getContenidoDestacadas() {
        // Priorizar las revistas marcadas como destacadas por el administrador
        let destacadas = this.revistasData.filter(r => r.destacada);
        
        // Si hay menos de 6 destacadas, completar con las más recientes
        if (destacadas.length < 6) {
            const adicionales = this.revistasData
                .filter(r => !destacadas.includes(r))
                .slice(0, 6 - destacadas.length);
            destacadas.push(...adicionales);
        }

        const gridHTML = destacadas.map((r, idx) => {
            const footerMeta = `<span class="meta-pill pill-destacada"><i class="fas fa-fire"></i> Destacada #${idx + 1}</span>`;
            return this.generarTarjetaEditorial(r, { footerMeta });
        }).join('');

        return `
            <div class="seccion-header">
                <div>
                    <h2 class="seccion-titulo"><i class="fas fa-award" style="color: #b45309;"></i> Revistas Destacadas</h2>
                    <span class="stats-section-subtitle">Las ediciones con mayor impacto e interés en nuestra comunidad académica</span>
                </div>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i> Volver a Todas las Revistas
                </button>
            </div>

            <div class="editorial-section-container">
                <div class="editorial-grid">
                    ${gridHTML}
                </div>
            </div>
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
        const favoritosCount = this.favoritos.size;
        const tiempoTotalFormato = this.formatearTiempoLectura(this.estadisticas.tiempoLectura);
        const totalVistas = this.estadisticas.revistasVistas ? this.estadisticas.revistasVistas.size : 0;

        // Consolidar lista de revistas leídas combinando revistasDetalle y revistasData
        const detalleMap = this.estadisticas.revistasDetalle || {};
        const revistasIds = new Set([
            ...(this.estadisticas.revistasVistas ? [...this.estadisticas.revistasVistas] : []),
            ...Object.keys(detalleMap)
        ]);

        // Si hay tiempo total pero las revistas tienen 0 tiempo registrado, calcular proporcional
        let sumaTiempos = 0;
        Object.values(detalleMap).forEach(d => { sumaTiempos += (d.tiempo || 0); });
        const fallbackTiempo = (sumaTiempos === 0 && this.estadisticas.tiempoLectura > 0 && revistasIds.size > 0)
            ? Math.round(this.estadisticas.tiempoLectura / revistasIds.size)
            : 0;

        const listaRevistasLeidas = [];
        revistasIds.forEach(id => {
            const dataGaleria = this.revistasData.find(r => r.id == id);
            const detalle = detalleMap[id] || {};
            const tiempoFinal = (detalle.tiempo && detalle.tiempo > 0) ? detalle.tiempo : fallbackTiempo;

            listaRevistasLeidas.push({
                id: id,
                titulo: dataGaleria?.titulo || detalle.titulo || 'Revista Académica',
                portada: dataGaleria?.portada || detalle.portada || '',
                urlVisor: dataGaleria?.urlVisor || detalle.urlVisor || `/revistas/${id}`,
                anio: dataGaleria?.anio || detalle.anio || '',
                tiempo: tiempoFinal,
                paginas: detalle.paginas || 0,
                visitas: detalle.visitas || 1,
                ultimaLectura: detalle.ultimaLectura || this.estadisticas.ultimaVisita || null
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
                                    ${rev.anio ? `<span class="meta-pill"><i class="fas fa-calendar-alt"></i> Edición ${rev.anio}</span>` : ''}
                                    <span class="meta-pill pill-tiempo"><i class="fas fa-clock"></i> <strong>${tiempoRevista}</strong> dedicados</span>
                                    <span class="meta-pill pill-fecha"><i class="fas fa-history"></i> Leída: ${fechaStr}</span>
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
                <div>
                    <h2 class="seccion-titulo"><i class="fas fa-chart-line" style="color: #1f5b4f;"></i> Mis Estadísticas de Lectura</h2>
                    <span class="stats-section-subtitle">Métricas personales de tus sesiones de lectura en el portal</span>
                </div>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i> Volver a Todas las Revistas
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
                            <h3 class="stats-spotlight-title">${topRevista.titulo}</h3>
                            <p class="stats-spotlight-desc">
                                Has invertido <strong>${this.formatearTiempoLectura(topRevista.tiempo)}</strong> leyendo esta publicación académica.
                            </p>
                            <a href="${topRevista.urlVisor}" class="stats-btn-continuar" style="padding: 9px 18px; font-size: 0.92rem;">
                                <i class="fas fa-book-open"></i> Continuar Leyendo
                            </a>
                        </div>
                    </div>
                ` : ''}

                <!-- LISTA DE REVISTAS EXPLORADAS CON MARCO BLANCO -->
                <div class="stats-section-header">
                    <div class="stats-section-title">
                        <i class="fas fa-list-check"></i> Revistas que has explorado (${listaRevistasLeidas.length})
                    </div>
                    <span class="stats-section-subtitle">Tiempo dedicado y avance de lectura</span>
                </div>

                ${listaRevistasLeidas.length > 0 ? `
                    <div class="stats-revistas-grid">
                        ${revistasCardsHTML}
                    </div>
                ` : `
                    <div class="editorial-empty-card">
                        <div class="editorial-empty-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Aún no has explorado ninguna revista</h3>
                        <p>
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
                    
                    <button class="btn-reiniciar-solido" onclick="flipbookManager.abrirModalReinicio()">
                        <i class="fas fa-trash-alt"></i> Reiniciar estadísticas
                    </button>
                </div>
            </div>
        `;
    }

    abrirModalReinicio() {
        let modal = document.getElementById('modalConfirmReinicio');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'modalConfirmReinicio';
            modal.className = 'custom-confirm-overlay';
            modal.innerHTML = `
                <div class="custom-confirm-card">
                    <div class="custom-confirm-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>¿Reiniciar tus estadísticas?</h3>
                    <p>Se borrarán tus métricas de tiempo invertido, páginas leídas y registro de revistas vistas. Esta acción no se puede deshacer.</p>
                    <div class="custom-confirm-actions">
                        <button class="btn-confirm-cancelar" onclick="flipbookManager.cerrarModalReinicio()">Cancelar</button>
                        <button class="btn-confirm-aceptar" onclick="flipbookManager.confirmarReinicioEstadisticas()">Sí, reiniciar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } else {
            modal.style.display = 'flex';
        }
    }

    cerrarModalReinicio() {
        const modal = document.getElementById('modalConfirmReinicio');
        if (modal) modal.style.display = 'none';
    }

    confirmarReinicioEstadisticas() {
        this.cerrarModalReinicio();
        this.resetearEstadisticas();
    }

    getContenidoAcerca() {
        return `
            <div class="seccion-header">
                <div>
                    <h2 class="seccion-titulo"><i class="fas fa-university" style="color: #1f5b4f;"></i> Acerca del Portal ITINEWS</h2>
                    <span class="stats-section-subtitle">Plataforma digital académica y de divulgación tecnológica</span>
                </div>
                <button class="btn-volver" onclick="flipbookManager.volverAGaleria()">
                    <i class="fas fa-arrow-left"></i> Volver a Todas las Revistas
                </button>
            </div>

            <div class="editorial-section-container">
                <!-- HERO INSTITUCIONAL -->
                <div class="acerca-hero-card">
                    <div class="acerca-logo-wrap">
                        <img src="/img/logo.jpg" alt="Logo UPVT" class="acerca-logo-img">
                    </div>
                    <div>
                        <span class="stats-spotlight-badge" style="background: #7a1830; margin-bottom: 8px;">
                            <i class="fas fa-graduation-cap"></i> Universidad Politécnica del Valle de Toluca
                        </span>
                        <h3 class="acerca-hero-title">
                            División de Ingeniería en Tecnologías de la Información e Innovación Digital
                        </h3>
                        <p class="acerca-hero-desc">
                            El <strong>Portal ITINEWS</strong> es el repositorio oficial interactivo creado para preservar, difundir y compartir las publicaciones y artículos de investigación desarrollados por docentes, estudiantes y egresados de nuestra casa de estudios.
                        </p>
                    </div>
                </div>

                <!-- CARACTERÍSTICAS EDITORIALES -->
                <div class="acerca-features-grid">
                    <div class="acerca-feat-card">
                        <div class="acerca-feat-icon feat-1">
                            <i class="fas fa-book-open-reader"></i>
                        </div>
                        <h4 class="acerca-feat-title feat-title-1">Visor Flipbook 3D</h4>
                        <p class="acerca-feat-desc">
                            Experiencia inmersiva con cambio de página realista, soporte táctil responsivo, zoom de alta fidelidad y modo pantalla completa.
                        </p>
                    </div>

                    <div class="acerca-feat-card">
                        <div class="acerca-feat-icon feat-2">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h4 class="acerca-feat-title feat-title-2">Estadísticas en Tiempo Real</h4>
                        <p class="acerca-feat-desc">
                            Métricas de lectura personalizadas que registran páginas leídas, tiempo invertido por revista y revistas preferidas en tu navegador.
                        </p>
                    </div>

                    <div class="acerca-feat-card">
                        <div class="acerca-feat-icon feat-3">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h4 class="acerca-feat-title feat-title-3">Tecnología de Vanguardia</h4>
                        <p class="acerca-feat-desc">
                            Optimización WebP ultrarrápida, arquitectura SPA ligera, motor StPageFlip, Laravel y persistencia local sin cookies invasivas.
                        </p>
                    </div>
                </div>

                <!-- ACCIÓN -->
                <div class="stats-section-header" style="justify-content: center; text-align: center; padding: 25px;">
                    <div>
                        <h4 class="acerca-cta-title">¿Listo para explorar el conocimiento UPVT?</h4>
                        <p class="acerca-cta-desc">Accede a todas las ediciones organizadas por cuatrimestre y año académico.</p>
                        <button class="stats-btn-continuar" style="padding: 10px 24px; font-size: 0.95rem; margin: 0 auto; display: inline-flex;" onclick="flipbookManager.volverAGaleria()">
                            <i class="fas fa-compass"></i> Explorar Catálogo Completo
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    generarTarjetaEditorial(revista, opciones = {}) {
        const esFavorito = this.favoritos.has(revista.id.toString());
        const footerMeta = opciones.footerMeta || '';

        return `
            <article class="editorial-card" data-revista-id="${revista.id}">
                <div class="editorial-cover-wrap" onclick="window.location.href='${revista.urlVisor}'">
                    ${revista.portada 
                        ? `<img src="${revista.portada}" alt="Portada de ${revista.titulo}" loading="lazy">` 
                        : `<div style="width:100%; height:100%; background: #1f5b4f; display:flex; align-items:center; justify-content:center; color:#fff; font-size: 2.5rem;"><i class="fas fa-book"></i></div>`}
                    ${revista.anio ? `<span class="editorial-badge-anio">Edición ${revista.anio}</span>` : ''}
                    <button class="editorial-btn-fav ${esFavorito ? 'active' : ''}" 
                            title="${esFavorito ? 'Quitar de favoritos' : 'Añadir a favoritos'}"
                            onclick="event.stopPropagation(); flipbookManager.toggleFavoritoConAnimacion('${revista.id}', this)">
                        <i class="${esFavorito ? 'fas' : 'far'} fa-heart"></i>
                    </button>
                </div>
                <div class="editorial-body">
                    <div>
                        <h3 class="editorial-title" title="${revista.titulo}">${revista.titulo}</h3>
                        <p class="editorial-desc">${revista.descripcion || 'Edición digital oficial de la División de Ingeniería en Tecnologías de la Información de la UPVT.'}</p>
                    </div>
                    ${footerMeta ? `<div style="margin-bottom: 12px;">${footerMeta}</div>` : ''}
                    <div class="editorial-footer">
                        <a href="${revista.urlVisor}" class="editorial-btn-leer">
                            <i class="fas fa-book-open"></i> Leer Revista
                        </a>
                    </div>
                </div>
            </article>
        `;
    }

    toggleFavoritoConAnimacion(revistaId, btnEl) {
        this.toggleFavorito(revistaId);
        if (btnEl) {
            const esFav = this.favoritos.has(revistaId.toString());
            btnEl.classList.toggle('active', esFav);
            const icon = btnEl.querySelector('i');
            if (icon) {
                icon.className = esFav ? 'fas fa-heart' : 'far fa-heart';
            }
        }
        if (this.seccionActiva === 'favoritos') {
            this.mostrarSeccion('favoritos');
        }
    }

    generarGaleriaFavoritos() {
        return this.getContenidoFavoritos();
    }

    generarGaleriaRecientes() {
        return this.getContenidoRecientes();
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

                // Migración: Reparto equitativo de tiempo histórico si no estaba desglosado
                if (this.estadisticas.tiempoLectura > 0 && this.estadisticas.revistasVistas.size > 0) {
                    let totalTiempoDetalles = 0;
                    Object.values(this.estadisticas.revistasDetalle).forEach(d => {
                        totalTiempoDetalles += (d.tiempo || 0);
                    });

                    if (totalTiempoDetalles === 0) {
                        const tiempoPorRevista = Math.round(this.estadisticas.tiempoLectura / this.estadisticas.revistasVistas.size);
                        this.estadisticas.revistasVistas.forEach(id => {
                            const revIdStr = id.toString();
                            const revData = this.revistasData.find(r => r.id == id);
                            if (!this.estadisticas.revistasDetalle[revIdStr]) {
                                this.estadisticas.revistasDetalle[revIdStr] = {
                                    id: revIdStr,
                                    titulo: revData?.titulo || 'Revista Académica',
                                    portada: revData?.portada || '',
                                    urlVisor: revData?.urlVisor || `/revistas/${id}`,
                                    anio: revData?.anio || '',
                                    tiempo: tiempoPorRevista,
                                    paginas: 0,
                                    visitas: 1,
                                    ultimaLectura: this.estadisticas.ultimaVisita || new Date().toISOString()
                                };
                            } else {
                                this.estadisticas.revistasDetalle[revIdStr].tiempo = tiempoPorRevista;
                            }
                        });
                        this.guardarEnLocalStorage();
                    }
                }
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
            this.cargarRevistasDesdeHTML();
            this.cargarDesdeLocalStorage();
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