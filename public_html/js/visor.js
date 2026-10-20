// JavaScript para la página del visor (revistas.show.blade.php)

document.addEventListener('DOMContentLoaded', () => {
    // Verificamos que los datos de la revista existan
    if (typeof REVISTA_DATOS === 'undefined' || !REVISTA_DATOS) {
        console.error('No se encontraron datos de la revista (variable REVISTA_DATOS).');
        alert('Error: No se pudieron cargar los datos de la revista.');
        return;
    }
    
    // Verificamos que la librería StPageFlip esté cargada
    if (typeof St === 'undefined' || !St.PageFlip) {
        console.error('La librería StPageFlip no está cargada.');
        alert('Error: No se pudo cargar el visor de revistas.');
        return;
    }

    console.log('Iniciando visor con:', REVISTA_DATOS);
    
    let pageFlipInstance = null;
    let currentZoom = 1;
    let canvasPan = { x: 0, y: 0 };
    let isDragging = false;
    let startPos = { x: 0, y: 0 };
    let isZoomMode = false;
    let isFullscreen = false;
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    const loader = document.getElementById('flipbook-loader');
    const flipbookContainer = document.getElementById('flipbook-container');
    const flipbookEl = document.getElementById('flipbook');
    const zoomOverlay = document.getElementById('zoom-overlay');

    if (!flipbookContainer || !flipbookEl || !loader || !zoomOverlay) {
        console.error('Faltan elementos HTML esenciales para el visor.');
        return;
    }

    // --- Funciones Principales ---

    function calculateDimensions() {
        if (isMobile) {
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            let width, height;
            if (windowHeight > windowWidth) { // Portrait
                width = windowWidth * 0.95; // Usar más espacio
                height = width * 1.41; // Proporción A4 aprox
            } else { // Landscape
                height = windowHeight * 0.9;
                width = height / 1.41;
            }
            return { width: Math.floor(width), height: Math.floor(height) };
        } else {
            // Dimensiones de UNA página para PC
            return { width: 550, height: 778 }; // Proporción A4
        }
    }

    function iniciarFlipbook() {
        try {
            const dimensions = calculateDimensions();
            const settings = {
                width: dimensions.width,
                height: dimensions.height,
                size: 'fixed',
                maxShadowOpacity: 0.5,
                showCover: true,
                mobileScrollSupport: isMobile
            };

            pageFlipInstance = new St.PageFlip(flipbookEl, settings);

            pageFlipInstance.on('init', () => {
                loader.classList.remove('visible');
                actualizarContador();
                configurarEventosVisor();
            });

            pageFlipInstance.on('error', (e) => {
                console.error('Error en librería:', e.data);
                loader.classList.remove('visible');
                alert(`Error al cargar la revista: ${e.data.message}`);
            });

            // Cargamos las URLs desde la variable global
            const urls = REVISTA_DATOS.paginas.map(p => p.url);
            pageFlipInstance.loadFromImages(urls);

            pageFlipInstance.on('flip', (e) => {
                actualizarContador();
                resetZoom();
            });

        } catch (error) {
            console.error('Error creando la instancia:', error);
            loader.classList.remove('visible');
            alert('No se pudo crear el visor.');
        }
    }

    function configurarEventosVisor() {
        // Eventos de PC
        zoomOverlay.addEventListener('mousedown', handleCanvasMouseDown);
        zoomOverlay.addEventListener('wheel', handleCanvasWheel);
        
        // Eventos de Móvil (¡Importante!)
        zoomOverlay.addEventListener('touchstart', handleTouchStart, { passive: false });
        zoomOverlay.addEventListener('touchmove', handleTouchMove, { passive: false });
        zoomOverlay.addEventListener('touchend', handleTouchEnd, { passive: false });

        // Controles de la UI
        document.getElementById('prev')?.addEventListener('click', volverPagina);
        document.getElementById('next')?.addEventListener('click', avanzarPagina);
        document.getElementById('fullscreen-btn')?.addEventListener('click', toggleFullscreen);
        document.getElementById('zoom-in-btn')?.addEventListener('click', () => applyZoom(0.5));
        document.getElementById('zoom-out-btn')?.addEventListener('click', () => applyZoom(-0.5));
        
        document.addEventListener('keydown', handleKeydown);
        document.addEventListener('fullscreenchange', handleFullscreenChange);
    }
    
    // --- Lógica de Zoom y Arrastre ---

    function handleTouchStart(e) {
        if (e.touches.length === 1 && currentZoom > 1) {
            e.preventDefault();
            isDragging = true;
            startPos = { x: e.touches[0].clientX, y: e.touches[0].clientY };
        } else if (e.touches.length === 2) {
            e.preventDefault();
            // Lógica de Pinch (simplificada)
            isDragging = true; 
        }
    }
    
    function handleTouchMove(e) {
        if (!isDragging || currentZoom <= 1) return;
        
        if (e.touches.length === 1) {
            e.preventDefault();
            const touch = e.touches[0];
            const dx = touch.clientX - startPos.x;
            const dy = touch.clientY - startPos.y;
            startPos = { x: touch.clientX, y: touch.clientY };
            canvasPan.x += dx;
            canvasPan.y += dy;
            aplicarTransformacionCanvas();
        }
    }
    
    function handleTouchEnd(e) {
        if (isDragging) {
            isDragging = false;
        }
    }

    function handleCanvasMouseDown(e) {
        if (currentZoom <= 1) return;
        e.preventDefault();
        isDragging = true;
        startPos = { x: e.clientX, y: e.clientY };
        zoomOverlay.style.cursor = 'grabbing';
        document.addEventListener('mousemove', handleCanvasMouseMove);
        document.addEventListener('mouseup', terminarArrastre, { once: true });
    }

    function handleCanvasMouseMove(e) {
        if (!isDragging) return;
        e.preventDefault();
        const dx = e.clientX - startPos.x;
        const dy = e.clientY - startPos.y;
        startPos = { x: e.clientX, y: e.clientY };
        canvasPan.x += dx;
        canvasPan.y += dy;
        aplicarTransformacionCanvas();
    }

    function terminarArrastre() {
        if (isDragging) {
            isDragging = false;
            zoomOverlay.style.cursor = 'grab';
            document.removeEventListener('mousemove', handleCanvasMouseMove);
        }
    }

    function handleCanvasWheel(e) {
        if (currentZoom > 1) {
            e.preventDefault();
        }
        const zoomChange = e.deltaY > 0 ? -0.2 : 0.2;
        applyZoom(zoomChange);
    }

    function applyZoom(zoomChange) {
        if (!pageFlipInstance) return;
        currentZoom = Math.max(1, Math.min(currentZoom + zoomChange, 3));
        actualizarModoZoom();
        aplicarTransformacionCanvas();
        actualizarInterfazZoom();
    }
    
    function resetZoom() {
        currentZoom = 1;
        actualizarModoZoom();
        aplicarTransformacionCanvas();
        actualizarInterfazZoom();
    }

    function actualizarModoZoom() {
        const oldZoomMode = isZoomMode;
        isZoomMode = currentZoom > 1;

        if (isZoomMode !== oldZoomMode) {
            if (isZoomMode) {
                zoomOverlay.classList.add('active');
                zoomOverlay.style.cursor = 'grab';
                pageFlipInstance.updateSettings({ mobileScrollSupport: false });
            } else {
                isDragging = false;
                zoomOverlay.classList.remove('active');
                zoomOverlay.style.cursor = 'default';
                canvasPan = { x: 0, y: 0 };
                aplicarTransformacionCanvas();
                pageFlipInstance.updateSettings({ mobileScrollSupport: isMobile });
            }
        }
    }

    function aplicarTransformacionCanvas() {
        const canvas = flipbookEl.querySelector('canvas');
        if (!canvas) return;
        if (currentZoom > 1) {
            canvas.style.transform = `translate(${canvasPan.x}px, ${canvasPan.y}px) scale(${currentZoom})`;
            canvas.style.transformOrigin = 'center center';
        } else {
            canvas.style.transform = 'none';
            canvasPan = { x: 0, y: 0 };
        }
    }
    
    // --- Funciones de UI y Utilidad ---

    function actualizarInterfazZoom() {
        document.getElementById('zoom-in-btn').disabled = currentZoom >= 3;
        document.getElementById('zoom-out-btn').disabled = currentZoom <= 1;
        document.getElementById('prev').disabled = isZoomMode;
        document.getElementById('next').disabled = isZoomMode;
    }

    function actualizarContador() {
        if (!pageFlipInstance) return;
        try {
            const currentPage = pageFlipInstance.getCurrentPageIndex();
            const totalPages = pageFlipInstance.getPageCount();
            document.getElementById('contadorPaginas').innerText = `Página ${currentPage + 1} de ${totalPages}`;
        } catch (error) {}
    }

    function volverPagina() {
        if (!isZoomMode && pageFlipInstance) {
            pageFlipInstance.flipPrev();
        }
    }

    function avanzarPagina() {
        if (!isZoomMode && pageFlipInstance) {
            pageFlipInstance.flipNext();
        }
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            flipbookContainer.requestFullscreen().catch(err => {
                console.error('Error en pantalla completa:', err);
            });
        } else {
            document.exitFullscreen();
        }
    }
    
    function handleKeydown(e) {
        if (!pageFlipInstance) return;
        switch (e.key) {
            case "ArrowLeft":
                volverPagina();
                break;
            case "ArrowRight":
                avanzarPagina();
                break;
            case "f":
                toggleFullscreen();
                break;
            case "+":
                applyZoom(0.5);
                break;
            case "-":
                applyZoom(-0.5);
                break;
        }
    }
    
    function handleFullscreenChange() {
        isFullscreen = !!document.fullscreenElement;
        const icon = document.getElementById('fullscreen-btn')?.querySelector('i');
        if (icon) {
            icon.className = isFullscreen ? 'fas fa-compress' : 'fas fa-expand';
        }
    }

    iniciarFlipbook();
});