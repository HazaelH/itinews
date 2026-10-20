class FlipbookViewer {
    constructor() {
        this.flipbookElement = null;
        this.pageFlipInstance = null;
        this.imageList = [];

        this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        this.isFullscreen = false;
        this.currentPage = 0;
        this.currentZoom = 1.0;
        this.isZoomMode = false;
        this.canvasPan = { x: 0, y: 0 };
        this.isDragging = false;
        this.startPos = { x: 0, y: 0 };
        this.zoomOverlay = document.getElementById('zoom-overlay');
        this.spinner = document.getElementById('visor-spinner');

        this.initialPinchDistance = 0;
        this.initialZoomAtPinch = 1.0;
        this.isPinching = false;
        this.preventFlipInteractions = false;

        this.buttonsTimeout = null;
        this.isInitializing = false;
        this.revistaId = null;

        this.stats = {
            paginasLeidas: 0,
            tiempoLectura: 0,
            revistasVistas: new Set(),
            historial: []
        };
        this.paginasVistasSesion = new Set();
        this.lastTimeCheck = Date.now();
        this.updateInterval = null;

        this.thumbnailsPanel = null;
        this.progresoBarra = null;
        this.doubleClickTimer = null;

        // binds
        this.inicializar = this.inicializar.bind(this);
        this.handleFlipbookClick = this.handleFlipbookClick.bind(this);
        this.toggleFullscreen = this.toggleFullscreen.bind(this);
        this.handleResize = this.handleResize.bind(this);
        this.handleKeydown = this.handleKeydown.bind(this);
        this.handleMouseEvents = this.handleMouseEvents.bind(this);
        this.applyZoom = this.applyZoom.bind(this);
        this.handleMouseDown = this.handleMouseDown.bind(this);
        this.handleMouseMove = this.handleMouseMove.bind(this);
        this.handleMouseUp = this.handleMouseUp.bind(this);
        this.handleWheel = this.handleWheel.bind(this);
        this.handleTouchStart = this.handleTouchStart.bind(this);
        this.handleTouchMove = this.handleTouchMove.bind(this);
        this.handleTouchEnd = this.handleTouchEnd.bind(this);
        this.resetButtonsTimeout = this.resetButtonsTimeout.bind(this);
        this.hideButtons = this.hideButtons.bind(this);
        this.showButtons = this.showButtons.bind(this);
        this.manejarFullscreenChange = this.manejarFullscreenChange.bind(this);
        this.actualizarEstadisticas = this.actualizarEstadisticas.bind(this);
        this.actualizarProgreso = this.actualizarProgreso.bind(this);
        this.toggleThumbnails = this.toggleThumbnails.bind(this);
        this.cerrarThumbnails = this.cerrarThumbnails.bind(this);
        this.sincronizarConManager = this.sincronizarConManager.bind(this);
        this.resetZoom = this.resetZoom.bind(this);
    }

    async inicializar() {
        await this.waitForDOM();
        this.cargarEstadisticasLS();

        const flipbookEl = document.getElementById('flipbook');
        const appContainer = document.getElementById('app-container');

        if (!flipbookEl || typeof St === 'undefined') {
            console.error("Elemento flipbook o librería St no encontrados");
            if(this.spinner) this.spinner.innerHTML = "<p>Error al cargar el visor</p>";
            return;
        }

        this.flipbookElement = flipbookEl;
        this.revistaId = appContainer?.dataset.revistaId;

        if (this.revistaId) {
            this.stats.revistasVistas.add(this.revistaId);
            this.guardarEstadisticasLS();
            this.sincronizarConManager();
        }

        try {
            const imagesFromData = appContainer?.dataset.images;
            if (imagesFromData) {
                this.imageList = JSON.parse(imagesFromData);
            } else if (window.revistaImages && Array.isArray(window.revistaImages)) {
                this.imageList = window.revistaImages;
            }

            if (!this.imageList || this.imageList.length === 0) {
                throw new Error("La lista de imágenes está vacía");
            }
        } catch (e) {
            console.error("Error cargando imágenes:", e);
            if(this.spinner) this.spinner.innerHTML = "<p>No hay contenido para mostrar</p>";
            return;
        }

        this.crearBarraProgreso();
        this.crearBotonThumbnails();

        this.construirInstancia(0);

        window.addEventListener('resize', this.handleResize);
        document.addEventListener('fullscreenchange', this.manejarFullscreenChange);
        document.addEventListener('webkitfullscreenchange', this.manejarFullscreenChange);

        this.configurarEventosUI();
        this.configurarPinchZoom();
        this.resetButtonsTimeout();

        window.addEventListener('beforeunload', () => {
            this.actualizarEstadisticas();
            this.sincronizarConManager();
            this.cerrarMetricas();
        });

        window.addEventListener('pagehide', () => {
            this.actualizarEstadisticas();
            this.cerrarMetricas();
        });

        // Permite que metricas.js cierre la visita al ocultarse la pestaña.
        window.__itinCerrarVisita = () => {
            this.actualizarEstadisticas();
            this.cerrarMetricas();
        };

        this.iniciarMetricas();
    }

    // ============================================
    // MÉTRICAS ANÓNIMAS (servidor)
    // ============================================
    iniciarMetricas() {
        if (!this.revistaId || !window.ItinMetricas) return;

        const promesa = window.ItinMetricas.startRevista(this.revistaId);
        if (promesa && typeof promesa.then === 'function') {
            promesa.then(() => window.ItinMetricas.page(this.currentPage + 1));
        }
    }

    reportarPagina(numeroPagina) {
        if (window.ItinMetricas) {
            window.ItinMetricas.page(numeroPagina);
        }
    }

    cerrarMetricas() {
        if (!this.revistaId || !window.ItinMetricas) return;

        const paginas = this.paginasVistasSesion ? this.paginasVistasSesion.size : this.stats.paginasLeidas;
        let total = this.imageList ? this.imageList.length : 0;
        try {
            if (this.pageFlipInstance) total = this.pageFlipInstance.getPageCount();
        } catch (e) { /* usa imageList */ }

        window.ItinMetricas.close(this.stats.tiempoLectura, paginas, total);
    }

    sincronizarConManager() {
        if (window.flipbookManager && this.revistaId) {
            window.flipbookManager.registrarVisita(this.revistaId);
            if (window.flipbookManager.estadisticas) {
                window.flipbookManager.estadisticas.paginasLeidas = Math.max(
                    window.flipbookManager.estadisticas.paginasLeidas || 0,
                    this.stats.paginasLeidas
                );
                window.flipbookManager.estadisticas.tiempoLectura = Math.max(
                    window.flipbookManager.estadisticas.tiempoLectura || 0,
                    this.stats.tiempoLectura
                );
                window.flipbookManager.guardarEnLocalStorage();
            }
        }
    }

    crearBarraProgreso() {
        const header = document.querySelector('.visor-header');
        if (!header) return;

        const progresoContainer = document.createElement('div');
        progresoContainer.className = 'progreso-lectura-container';
        progresoContainer.innerHTML = `
            <div class="progreso-lectura-bar">
                <div id="progreso-lectura" class="progreso-lectura-fill" style="width: 0%;"></div>
            </div>
            <span id="progreso-porcentaje" class="progreso-porcentaje">0%</span>
        `;
        header.appendChild(progresoContainer);
        this.progresoBarra = document.getElementById('progreso-lectura');
    }

    actualizarProgreso() {
        if (!this.pageFlipInstance) return;
        try {
            const total = this.pageFlipInstance.getPageCount();
            if (total <= 0) return;

            const visibles = this.getPaginasVisibles();
            const maxPaginaVisible = Math.max(...visibles) + 1;

            let porcentaje;
            if (maxPaginaVisible >= total || visibles.includes(total - 1)) {
                porcentaje = 100;
            } else {
                porcentaje = Math.min(100, Math.round((maxPaginaVisible / total) * 100));
            }
            
            const barra = document.getElementById('progreso-lectura');
            const texto = document.getElementById('progreso-porcentaje');
            if (barra) barra.style.width = `${porcentaje}%`;
            if (texto) texto.textContent = `${porcentaje}%`;
        } catch (e) {}
    }

    crearBotonThumbnails() {
        const controls = document.querySelector('.visor-controls');
        if (!controls) return;

        const btnThumbnails = document.createElement('button');
        btnThumbnails.id = 'btn-thumbnails';
        btnThumbnails.className = 'visor-btn';
        btnThumbnails.title = 'Ver miniaturas (T)';
        btnThumbnails.innerHTML = '<i class="fas fa-th"></i>';
        btnThumbnails.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleThumbnails();
        });
        controls.appendChild(btnThumbnails);
    }

    // ============================================
    // THUMBNAILS - CORREGIDO (z-index alto, oculta visor)
    // ============================================
    toggleThumbnails() {
        if (this.thumbnailsPanel) {
            this.cerrarThumbnails();
            return;
        }

        // Ocultar el visor para que no tape las miniaturas
        const flipbook = document.getElementById('flipbook');
        if (flipbook) flipbook.style.display = 'none';

        this.thumbnailsPanel = document.createElement('div');
        this.thumbnailsPanel.className = 'thumbnails-panel';
        this.thumbnailsPanel.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(30, 31, 35, 0.97);
            z-index: 9999;
            overflow-y: auto;
            padding: 60px 20px 20px;
        `;
        
        // Botón cerrar
        const closeBtn = document.createElement('button');
        closeBtn.className = 'thumbnails-close-btn';
        closeBtn.style.cssText = `
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 10000;
            background: #7a1830;
            color: #fff;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        `;
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.cerrarThumbnails();
        });
        this.thumbnailsPanel.appendChild(closeBtn);

        // Título
        const title = document.createElement('h3');
        title.style.cssText = `
            color: #fff;
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.2rem;
            font-weight: 700;
        `;
        title.innerHTML = `<i class="fas fa-file-lines"></i> ${this.imageList.length} páginas - Haz clic para ir`;
        this.thumbnailsPanel.appendChild(title);

        // Grid de miniaturas
        const grid = document.createElement('div');
        grid.style.cssText = `
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            max-width: 1000px;
            margin: 0 auto;
            padding-bottom: 40px;
        `;
        
        const paginaActual = this.pageFlipInstance?.getCurrentPageIndex() || 0;
        
        this.imageList.forEach((img, i) => {
            const thumb = document.createElement('div');
            thumb.style.cssText = `
                cursor: pointer;
                border-radius: 10px;
                overflow: hidden;
                border: 3px solid ${i === paginaActual ? '#7a1830' : 'transparent'};
                transition: all 0.3s ease;
                background: #2d2d2d;
                position: relative;
            `;
            thumb.innerHTML = `
                <img src="${img}" alt="Página ${i + 1}" loading="lazy" style="width: 100%; height: auto; display: block;">
                <span style="
                    position: absolute;
                    bottom: 8px;
                    right: 8px;
                    background: rgba(0,0,0,0.8);
                    color: #fff;
                    padding: 4px 10px;
                    border-radius: 12px;
                    font-size: 0.75rem;
                    font-weight: 700;
                ">${i + 1}</span>
            `;
            thumb.addEventListener('click', (e) => {
                e.stopPropagation();
                if (this.pageFlipInstance) {
                    this.pageFlipInstance.flip(i);
                }
                this.cerrarThumbnails();
            });
            
            // Hover effect
            thumb.addEventListener('mouseenter', () => {
                if (i !== paginaActual) thumb.style.borderColor = '#1f5b4f';
                thumb.style.transform = 'translateY(-5px)';
                thumb.style.boxShadow = '0 10px 25px rgba(0,0,0,0.3)';
            });
            thumb.addEventListener('mouseleave', () => {
                if (i !== paginaActual) thumb.style.borderColor = 'transparent';
                thumb.style.transform = 'translateY(0)';
                thumb.style.boxShadow = 'none';
            });
            
            grid.appendChild(thumb);
        });

        this.thumbnailsPanel.appendChild(grid);
        document.body.appendChild(this.thumbnailsPanel);

        // Scroll hasta la página actual
        setTimeout(() => {
            const active = this.thumbnailsPanel?.querySelector(`div:nth-child(${paginaActual + 2})`);
            if (active) {
                active.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 200);
    }

    cerrarThumbnails() {
        if (this.thumbnailsPanel) {
            this.thumbnailsPanel.remove();
            this.thumbnailsPanel = null;
        }
        // Mostrar el visor de nuevo
        const flipbook = document.getElementById('flipbook');
        if (flipbook) flipbook.style.display = '';
    }

    // ============================================
    // DOBLE CLIC CORREGIDO - No cambia páginas
    // ============================================
    handleFlipbookClick(e) {
        if (e.target.closest('button') || e.target.closest('.zoom-hd-btn') || e.target.closest('.zoom-hd-close-btn') || e.target.closest('.zoom-hd-nav-btn')) {
            return;
        }

        // Prevenir que el clic normal cambie la página cuando estamos en zoom
        if (this.isZoomMode) {
            return;
        }

        // Detectar doble clic manualmente para evitar cambio de página
        if (this.doubleClickTimer) {
            // Es doble clic
            clearTimeout(this.doubleClickTimer);
            this.doubleClickTimer = null;
            
            e.preventDefault();
            e.stopImmediatePropagation();
            
            if (Math.abs(this.currentZoom - 1.0) < 0.01) {
                this.currentZoom = 2.0;
            } else {
                this.resetZoom();
            }
            this.updateZoomState();
        } else {
            // Es primer clic - esperar para ver si hay segundo clic
            this.doubleClickTimer = setTimeout(() => {
                this.doubleClickTimer = null;
                // Es un clic simple, permitir cambio de página
            }, 300);
        }
    }

    resetZoom() {
        this.currentZoom = 1.0;
        this.canvasPan = { x: 0, y: 0 };
        if (this.zoomOverlay) {
            delete this.zoomOverlay.dataset.spread;
        }
        this.updateZoomState();
    }

    parchearCanvasTransparente() {
        if (!this.pageFlipInstance) return;
        try {
            const render = this.pageFlipInstance.getRender ? this.pageFlipInstance.getRender() : null;
            if (render && render.clear) {
                render.clear = function() {
                    if (this.ctx && this.canvas) {
                        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    }
                };
                if (render.ctx && render.canvas) {
                    render.ctx.clearRect(0, 0, render.canvas.width, render.canvas.height);
                }
            }
        } catch (e) {
            console.warn("No se pudo parchear CanvasRender:", e);
        }
    }

    precargarPaginasAdyacentes(paginaActual) {
        const paginasAPrecargar = [
            paginaActual + 1,
            paginaActual + 2,
            paginaActual - 1,
            paginaActual - 2
        ];

        paginasAPrecargar.forEach(pag => {
            if (pag >= 0 && pag < this.imageList.length) {
                const img = new Image();
                img.src = this.imageList[pag];
            }
        });
    }

    construirInstancia(paginaInicial = 0) {
        if (this.isInitializing) return;
        this.isInitializing = true;

        if (this.pageFlipInstance) {
            try { this.pageFlipInstance.destroy(); } catch(e) {}
        }

        this.flipbookElement.innerHTML = '';

        const baseWidth = this.isMobile ? 1600 : 1800;
        const baseHeight = this.isMobile ? 2263 : 2545;

        const settings = {
            size: 'stretch',
            width: baseWidth,
            height: baseHeight,
            minWidth: 320,
            maxWidth: 3600,
            minHeight: 450,
            maxHeight: 5000,
            drawShadow: !this.isMobile,
            flippingTime: 650,
            usePortrait: this.isMobile,
            showCover: true,
            startPage: paginaInicial,
            autoSize: true,
            maxShadowOpacity: 0.5,
            mobileScrollSupport: true,
            clickEventForward: true,
            useMouseEvents: true,
            disableFlipByClick: false,
            swipeDistance: 15
        };

        try {
            this.pageFlipInstance = new St.PageFlip(this.flipbookElement, settings);
            this.parchearCanvasTransparente();
            this.pageFlipInstance.loadFromImages(this.imageList);

            this.pageFlipInstance.on('init', () => {
                this.parchearCanvasTransparente();
                this.isInitializing = false;

                const canvas = this.flipbookElement.querySelector('canvas');
                if (canvas) {
                    canvas.style.imageRendering = '-webkit-optimize-contrast';
                    canvas.style.imageRendering = 'high-quality';
                }

                if (this.spinner) {
                    this.spinner.style.opacity = '0';
                    setTimeout(() => {
                        this.spinner.style.display = 'none';
                    }, 500);
                }

                this.actualizarContador();
                this.actualizarProgreso();
                this.precargarPaginasAdyacentes(paginaInicial);
                this.configurarEventosCanvas();

                this.currentZoom = 1.0;

                setTimeout(() => {
                    this.parchearCanvasTransparente();
                    this.updateZoomState();
                    if(this.pageFlipInstance) this.pageFlipInstance.updateBounds();
                }, 100);

                this.iniciarTrackingEstadisticas();
            });

            this.pageFlipInstance.on('flip', (e) => {
                this.parchearCanvasTransparente();
                this.currentPage = e.data;

                if (!this.paginasVistasSesion.has(this.currentPage)) {
                    this.paginasVistasSesion.add(this.currentPage);
                    this.stats.paginasLeidas++;
                }

                this.actualizarContador();
                this.actualizarProgreso();
                this.precargarPaginasAdyacentes(e.data);
                this.resetButtonsTimeout();
                this.actualizarEstadisticas();
                this.reportarPagina(e.data + 1);

                if (this.isZoomMode) {
                    this.updateZoomState();
                }
            });

        } catch (e) {
            console.error("Error construyendo libro:", e);
            this.isInitializing = false;
            if(this.spinner) this.spinner.innerHTML = "<p>Error al inicializar el libro</p>";
        }
    }

    cargarEstadisticasLS() {
        try {
            const saved = localStorage.getItem('estadisticas');
            if (saved) {
                const parsed = JSON.parse(saved);
                this.stats = {
                    paginasLeidas: parsed.paginasLeidas || 0,
                    tiempoLectura: parsed.tiempoLectura || 0,
                    revistasVistas: new Set(parsed.revistasVistas || []),
                    historial: parsed.historial || []
                };
            }
        } catch (e) {
            console.error("Error cargando estadísticas:", e);
        }
    }

    guardarEstadisticasLS() {
        try {
            const dataToSave = {
                ...this.stats,
                revistasVistas: [...this.stats.revistasVistas]
            };
            localStorage.setItem('estadisticas', JSON.stringify(dataToSave));
            this.sincronizarConManager();
        } catch (e) {
            console.error("Error guardando estadísticas:", e);
        }
    }

    iniciarTrackingEstadisticas() {
        this.lastTimeCheck = Date.now();
        if (!this.paginasVistasSesion.has(this.currentPage)) {
            this.paginasVistasSesion.add(this.currentPage);
        }

        if (!this.updateInterval) {
            this.updateInterval = setInterval(() => {
                this.actualizarEstadisticas();
            }, 5000);
        }
    }

    actualizarEstadisticas() {
        if (!this.revistaId) return;
        const now = Date.now();
        const deltaSeconds = (now - this.lastTimeCheck) / 1000;
        if (deltaSeconds > 0) {
            this.stats.tiempoLectura += deltaSeconds;
            this.lastTimeCheck = now;
        }
        this.guardarEstadisticasLS();
    }

    manejarFullscreenChange() {
        const isNowFullscreen = !!document.fullscreenElement;
        setTimeout(() => {
            if (this.pageFlipInstance) {
                this.pageFlipInstance.updateBounds();
                const canvas = document.querySelector('#flipbook canvas');
                if(canvas) {
                    canvas.style.display = 'none';
                    canvas.offsetHeight;
                    canvas.style.display = 'block';
                }
            }
        }, 100);

        if (isNowFullscreen !== this.isFullscreen) {
            this.isFullscreen = isNowFullscreen;
            const container = document.getElementById('app-container');
            const btnIcon = document.querySelector('#btn-fullscreen i');

            if (isNowFullscreen) {
                container.classList.add('is-fullscreen');
                if (btnIcon) btnIcon.className = 'fas fa-compress';
            } else {
                container.classList.remove('is-fullscreen');
                if (btnIcon) btnIcon.className = 'fas fa-expand';
            }
        }
    }

    toggleFullscreen() {
        const container = document.getElementById('app-container');
        if (!document.fullscreenElement) {
            container.requestFullscreen().catch(console.error);
        } else {
            document.exitFullscreen().catch(console.error);
        }
    }

    handleResize() {
        if (this.resizeTimer) clearTimeout(this.resizeTimer);
        this.resizeTimer = setTimeout(() => {
            this.parchearCanvasTransparente();
            if (!this.isInitializing) {
                if(this.pageFlipInstance) this.pageFlipInstance.updateBounds();
            }
            if (this.thumbnailsPanel) {
                this.cerrarThumbnails();
            }
            if (this.isZoomMode) {
                this.updateZoomState();
            }
        }, 300);
    }

    configurarEventosUI() {
        document.addEventListener('keydown', this.handleKeydown);
        document.getElementById('prev')?.addEventListener('click', () => {
            if (this.pageFlipInstance && !this.isInitializing) this.pageFlipInstance.flipPrev();
        });
        document.getElementById('next')?.addEventListener('click', () => {
            if (this.pageFlipInstance && !this.isInitializing) this.pageFlipInstance.flipNext();
        });
        document.getElementById('btn-fullscreen')?.addEventListener('click', this.toggleFullscreen);
        document.getElementById('zoom-in')?.addEventListener('click', () => this.applyZoom(0.1));
        document.getElementById('zoom-out')?.addEventListener('click', () => this.applyZoom(-0.1));

        ['mousemove', 'click', 'touchstart'].forEach(e =>
            document.addEventListener(e, this.handleMouseEvents, { passive: true })
        );
    }

    configurarPinchZoom() {
        const stage = document.querySelector('.visor-stage');
        if (!stage) return;

        stage.style.touchAction = 'none';

        stage.addEventListener('touchstart', (ev) => {
            if (this.preventFlipInteractions || this.isPinching || this.isZoomMode) {
                ev.stopImmediatePropagation();
            }
        }, { capture: true, passive: false });

        stage.addEventListener('touchstart', this.handleTouchStart, { passive: false });
        stage.addEventListener('touchmove', this.handleTouchMove, { passive: false });
        stage.addEventListener('touchend', this.handleTouchEnd);

        if (this.zoomOverlay) {
            this.zoomOverlay.style.touchAction = 'none';
            this.zoomOverlay.addEventListener('touchstart', this.handleTouchStart, { passive: false });
            this.zoomOverlay.addEventListener('touchmove', this.handleTouchMove, { passive: false });
            this.zoomOverlay.addEventListener('touchend', this.handleTouchEnd);
        }
    }

    handleTouchStart(e) {
        if (e.touches.length === 2) {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.isPinching = true;
            this.preventFlipInteractions = true;
            this.initialZoomAtPinch = this.currentZoom;
            this.initialPinchDistance = Math.hypot(
                e.touches[0].pageX - e.touches[1].pageX,
                e.touches[0].pageY - e.touches[1].pageY
            );
            this.updateZoomState();
            return;
        }

        if (e.touches.length === 1 && this.isZoomMode) {
            if (e.target.closest('#btnCerrarZoomHD') || e.target.closest('.zoom-hd-btn') || e.target.closest('.zoom-hd-nav-btn')) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            const t = e.touches[0];
            this.isDragging = true;
            this.startPos = { x: t.clientX, y: t.clientY };
            if (this.zoomOverlay) this.zoomOverlay.classList.add('grabbing');
            return;
        }

        if (this.isPinching || this.preventFlipInteractions) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }

    handleTouchMove(e) {
        if (this.isPinching && e.touches.length === 2) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const currentDistance = Math.hypot(
                e.touches[0].pageX - e.touches[1].pageX,
                e.touches[0].pageY - e.touches[1].pageY
            );
            const scale = currentDistance / this.initialPinchDistance;
            const newZoom = this.initialZoomAtPinch * scale;
            this.currentZoom = Math.max(0.5, Math.min(newZoom, 4.0));
            this.updateZoomState();
            return;
        }

        if (this.isDragging && e.touches.length === 1) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const t = e.touches[0];
            const dx = t.clientX - this.startPos.x;
            const dy = t.clientY - this.startPos.y;
            this.startPos = { x: t.clientX, y: t.clientY };
            this.canvasPan.x += dx;
            this.canvasPan.y += dy;
            this.updateZoomState();
            return;
        }

        if (this.isPinching || this.preventFlipInteractions) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }

    handleTouchEnd(e) {
        if (this.isPinching && e.touches.length < 2) {
            this.isPinching = false;
            if (Math.abs(this.currentZoom - 1.0) < 0.1) {
                this.currentZoom = 1.0;
            }
            setTimeout(() => {
                this.preventFlipInteractions = false;
            }, 250);
            this.updateZoomState();
        }

        if (this.isDragging && e.touches.length === 0) {
            this.isDragging = false;
            if (this.zoomOverlay) this.zoomOverlay.style.cursor = 'grab';
        }
    }

    handleMouseEvents() { this.resetButtonsTimeout(); }

    resetButtonsTimeout() {
        this.showButtons();
        if (this.buttonsTimeout) clearTimeout(this.buttonsTimeout);
        const hideDelay = this.isMobile ? 2000 : 4000;
        this.buttonsTimeout = setTimeout(this.hideButtons, hideDelay);
    }

    hideButtons() {
        document.querySelector('.visor-controls')?.classList.add('hidden-ui');
        document.querySelector('.visor-header')?.classList.add('hidden-ui');
    }

    showButtons() {
        document.querySelector('.visor-controls')?.classList.remove('hidden-ui');
        document.querySelector('.visor-header')?.classList.remove('hidden-ui');
    }

    waitForDOM() {
        return new Promise(r => {
            if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', r);
            else r();
        });
    }

    actualizarContador() {
        if (!this.pageFlipInstance) return;
        try {
            const total = this.pageFlipInstance.getPageCount();
            const current = this.pageFlipInstance.getCurrentPageIndex() + 1;
            const el = document.getElementById('contadorPaginas');
            if (el) el.innerText = `${current} / ${total}`;
        } catch (e) {}
    }

    handleKeydown(e) {
        this.resetButtonsTimeout();
        if (!this.pageFlipInstance || this.isInitializing) return;
        
        if (this.thumbnailsPanel) {
            if (e.key === 'Escape') this.cerrarThumbnails();
            return;
        }

        switch (e.key) {
            case "ArrowLeft": this.pageFlipInstance.flipPrev(); break;
            case "ArrowRight": this.pageFlipInstance.flipNext(); break;
            case "Escape":
                if(this.isFullscreen) this.toggleFullscreen();
                else if(this.isZoomMode) this.resetZoom();
                break;
            case "+": this.applyZoom(0.1); break;
            case "-": this.applyZoom(-0.1); break;
            case "f":
            case "F": this.toggleFullscreen(); break;
            case "t":
            case "T": this.toggleThumbnails(); break;
            case "0":
                this.resetZoom();
                break;
        }
    }

    configurarEventosCanvas() {
        const stage = document.querySelector('.visor-stage');
        if (stage) {
            stage.addEventListener('wheel', this.handleWheel, { passive: false });
            stage.addEventListener('click', this.handleFlipbookClick);
        }

        if (this.zoomOverlay) {
            this.zoomOverlay.addEventListener('mousedown', this.handleMouseDown);
            this.zoomOverlay.addEventListener('wheel', this.handleWheel, { passive: false });
            this.zoomOverlay.addEventListener('mouseup', this.handleMouseUp);
            this.zoomOverlay.addEventListener('mousemove', this.handleMouseMove);
            this.zoomOverlay.addEventListener('click', this.handleFlipbookClick);
        }
    }

    applyZoom(delta) {
        this.currentZoom = Math.max(0.5, Math.min(this.currentZoom + delta, 4.0));
        this.updateZoomState();
    }

    getPaginasVisibles() {
        if (!this.pageFlipInstance) {
            return [this.currentPage || 0];
        }
        try {
            const pageCol = this.pageFlipInstance.getPageCollection ? this.pageFlipInstance.getPageCollection() : null;
            if (pageCol && pageCol.getSpread) {
                const spreads = pageCol.getSpread();
                const curPage = this.pageFlipInstance.getCurrentPageIndex();
                const spreadIdx = pageCol.getSpreadIndexByPage(curPage);
                if (spreadIdx !== null && spreads && spreads[spreadIdx]) {
                    const paginas = spreads[spreadIdx].filter(p => typeof p === 'number' && p >= 0 && p < this.imageList.length);
                    if (paginas.length > 0) return paginas;
                }
            }
        } catch (e) {
            console.warn("Error obteniendo páginas visibles de PageFlip:", e);
        }

        // Fallback robusto según orientación y dimensiones de pantalla
        const isLandscape = !this.isMobile && (window.innerWidth > 768);
        if (!isLandscape || this.currentPage === 0) {
            return [this.currentPage || 0];
        }
        const leftPage = (this.currentPage % 2 === 1) ? this.currentPage : this.currentPage - 1;
        const rightPage = leftPage + 1;
        if (rightPage < this.imageList.length) {
            return [leftPage, rightPage];
        }
        return [leftPage];
    }

    updateZoomState() {
        this.isZoomMode = Math.abs(this.currentZoom - 1.0) > 0.001;

        if (this.isZoomMode) {
            if (this.zoomOverlay) {
                this.zoomOverlay.classList.add('active');
                this.renderizarZoomHD();
            }
            this.preventFlipInteractions = true;
        } else {
            if (this.zoomOverlay) {
                this.zoomOverlay.classList.remove('active');
                this.zoomOverlay.innerHTML = '';
                delete this.zoomOverlay.dataset.spread;
            }
            this.canvasPan = { x: 0, y: 0 };
            this.preventFlipInteractions = false;
        }
    }

    renderizarZoomHD() {
        if (!this.zoomOverlay || !this.imageList || !this.imageList.length) return;

        const paginas = this.getPaginasVisibles();
        const esDual = paginas.length === 2;
        const claveSpread = paginas.join('-');

        let spread = this.zoomOverlay.querySelector('#zoomHdSpread');
        let badge = this.zoomOverlay.querySelector('.zoom-hd-badge');

        // Si cambió el par de páginas o el overlay no se ha construido
        if (!spread || this.zoomOverlay.dataset.spread !== claveSpread) {
            this.zoomOverlay.dataset.spread = claveSpread;

            const etiquetaTexto = esDual 
                ? `Páginas ${paginas[0] + 1} y ${paginas[1] + 1} · Modo HD · ${Math.round(this.currentZoom * 100)}%`
                : `Página ${paginas[0] + 1} · Modo HD · ${Math.round(this.currentZoom * 100)}%`;

            let paginasHtml = '';
            paginas.forEach(pIdx => {
                paginasHtml += `
                    <div class="zoom-hd-page-wrapper">
                        <img class="zoom-hd-img" src="${this.imageList[pIdx]}" alt="Página ${pIdx + 1} HD" draggable="false">
                        <span class="zoom-hd-page-label">Pág. ${pIdx + 1}</span>
                    </div>
                `;
            });

            this.zoomOverlay.innerHTML = `
                <div class="zoom-hd-badge">
                    <i class="fas fa-magnifying-glass-plus"></i>
                    <span>${etiquetaTexto}</span>
                </div>
                <div class="zoom-hd-actions">
                    <button type="button" class="zoom-hd-btn" id="btnZoomHDOut" title="Reducir zoom (-)">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="zoom-hd-btn" id="btnZoomHDIn" title="Aumentar zoom (+)">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="zoom-hd-btn" id="btnZoomHDReset" title="Restablecer vista (0)">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="zoom-hd-close-btn" id="btnCerrarZoomHD" title="Salir de Zoom (Esc)">
                        <i class="fas fa-times"></i> Salir de Zoom
                    </button>
                </div>
                <button type="button" class="zoom-hd-nav-btn prev" id="btnZoomNavPrev" title="Página anterior (←)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="zoom-hd-nav-btn next" id="btnZoomNavNext" title="Página siguiente (→)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="zoom-hd-stage">
                    <div class="zoom-hd-spread ${esDual ? 'dual' : 'single'}" id="zoomHdSpread">
                        ${paginasHtml}
                    </div>
                </div>
            `;

            // Vincular botones de acción del Zoom HD
            this.zoomOverlay.querySelector('#btnCerrarZoomHD')?.addEventListener('click', (e) => {
                e.stopPropagation();
                this.resetZoom();
            });

            this.zoomOverlay.querySelector('#btnZoomHDIn')?.addEventListener('click', (e) => {
                e.stopPropagation();
                this.applyZoom(0.2);
            });

            this.zoomOverlay.querySelector('#btnZoomHDOut')?.addEventListener('click', (e) => {
                e.stopPropagation();
                this.applyZoom(-0.2);
            });

            this.zoomOverlay.querySelector('#btnZoomHDReset')?.addEventListener('click', (e) => {
                e.stopPropagation();
                this.currentZoom = 1.0;
                this.canvasPan = { x: 0, y: 0 };
                this.updateZoomState();
            });

            this.zoomOverlay.querySelector('#btnZoomNavPrev')?.addEventListener('click', (e) => {
                e.stopPropagation();
                if (this.pageFlipInstance) this.pageFlipInstance.flipPrev();
            });

            this.zoomOverlay.querySelector('#btnZoomNavNext')?.addEventListener('click', (e) => {
                e.stopPropagation();
                if (this.pageFlipInstance) this.pageFlipInstance.flipNext();
            });

            spread = this.zoomOverlay.querySelector('#zoomHdSpread');
            badge = this.zoomOverlay.querySelector('.zoom-hd-badge');
        } else {
            // Actualizar etiqueta del badge con el nuevo zoom
            if (badge) {
                const etiquetaTexto = esDual 
                    ? `Páginas ${paginas[0] + 1} y ${paginas[1] + 1} · Modo HD · ${Math.round(this.currentZoom * 100)}%`
                    : `Página ${paginas[0] + 1} · Modo HD · ${Math.round(this.currentZoom * 100)}%`;
                badge.innerHTML = `<i class="fas fa-magnifying-glass-plus"></i> <span>${etiquetaTexto}</span>`;
            }
        }

        if (spread) {
            spread.style.transform = `translate3d(${this.canvasPan.x}px, ${this.canvasPan.y}px, 0) scale(${this.currentZoom})`;
        }
    }

    handleMouseDown(e) {
        if (!this.isZoomMode) return;
        if (e.target.closest('#btnCerrarZoomHD') || e.target.closest('.zoom-hd-btn') || e.target.closest('.zoom-hd-nav-btn')) return;
        e.preventDefault();
        e.stopPropagation();
        this.isDragging = true;
        this.startPos = { x: e.clientX, y: e.clientY };
        if (this.zoomOverlay) this.zoomOverlay.classList.add('grabbing');
    }

    handleMouseMove(e) {
        if (!this.isDragging) return;
        e.preventDefault();
        const dx = e.clientX - this.startPos.x;
        const dy = e.clientY - this.startPos.y;
        this.startPos = { x: e.clientX, y: e.clientY };
        this.canvasPan.x += dx;
        this.canvasPan.y += dy;
        this.updateZoomState();
    }

    handleMouseUp() {
        this.isDragging = false;
        if (this.zoomOverlay) this.zoomOverlay.classList.remove('grabbing');
    }

    handleWheel(e) {
        if (e.ctrlKey || this.isZoomMode || Math.abs(this.currentZoom - 1.0) > 0.001) {
            e.preventDefault();
            e.stopPropagation();
            const delta = e.deltaY > 0 ? -0.1 : 0.1;
            this.applyZoom(delta);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('flipbook')) {
        window.viewer = new FlipbookViewer();
        window.viewer.inicializar();
    }
});