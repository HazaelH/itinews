class ProgresoRevista {
    constructor(revistaId) {
        this.revistaId = revistaId;
        this.intervalo = null;
        this.init();
    }

    init() {
        this.actualizarProgreso();
        this.intervalo = setInterval(() => this.actualizarProgreso(), 2000);
        window.addEventListener('beforeunload', () => this.detener());
    }

    async actualizarProgreso() {
        try {
            const response = await fetch(`/revista/${this.revistaId}/progreso`);
            
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            
            const data = await response.json();
            this.actualizarUI(data);
            
        } catch (error) {
            console.error('Error al obtener progreso:', error);
            this.mostrarErrorConexion();
        }
    }

    actualizarUI(data) {
        const barraProgreso = document.getElementById('barra-progreso');
        const porcentajeElement = document.getElementById('porcentaje');
        const contadorElement = document.getElementById('contador-paginas');
        const estadoElement = document.getElementById('estado');
        const detallesElement = document.getElementById('detalles');
        
        if (barraProgreso) {
            barraProgreso.style.width = data.progreso + '%';
        }
        if (porcentajeElement) {
            porcentajeElement.textContent = Math.round(data.progreso) + '%';
        }
        if (contadorElement) {
            contadorElement.textContent = data.paginas_procesadas + '/' + data.total_paginas + ' páginas';
        }
        
        this.actualizarEstado(data, estadoElement, detallesElement);
        
        this.actualizarVisibilidad(data);
    }

    actualizarEstado(data, estadoElement, detallesElement) {
        if (!estadoElement || !detallesElement) return;

        let estadoTexto = '';
        let detallesTexto = '';
        let estadoColor = '';
        
        switch(data.estado) {
            case 'pendiente':
                estadoTexto = 'En espera';
                detallesTexto = 'La revista está en cola para procesarse';
                estadoColor = 'text-yellow-600';
                break;
            case 'procesando':
                estadoTexto = 'Procesando';
                detallesTexto = `Convirtiendo página ${data.paginas_procesadas} de ${data.total_paginas}`;
                estadoColor = 'text-blue-600';
                break;
            case 'completado':
                estadoTexto = 'Completado';
                detallesTexto = 'Todas las páginas han sido convertidas exitosamente';
                estadoColor = 'text-green-600';
                break;
            case 'error':
                estadoTexto = 'Error';
                detallesTexto = 'Ha ocurrido un error durante el procesamiento';
                estadoColor = 'text-red-600';
                break;
        }
        
        estadoElement.textContent = estadoTexto;
        estadoElement.className = `text-lg font-semibold ${estadoColor} mb-2`;
        detallesElement.textContent = detallesTexto;
    }

    actualizarVisibilidad(data) {
        const spinner = document.getElementById('spinner');
        const completado = document.getElementById('completado');
        const error = document.getElementById('error');
        const badge = document.getElementById('badgeProgreso');
        const icono = document.getElementById('iconoProgreso');
        const subtitulo = document.getElementById('subtituloProgreso');
        
        if (data.completado) {
            this.ocultarElemento(spinner);
            this.mostrarElemento(completado);
            this.ocultarElemento(error);
            if (badge) badge.className = 'upload-modal-badge badge-success';
            if (icono) icono.className = 'fas fa-check';
            if (subtitulo) subtitulo.textContent = 'Procesamiento completado con éxito.';
            this.detener();
        } else if (data.error) {
            this.ocultarElemento(spinner);
            this.ocultarElemento(completado);
            this.mostrarElemento(error);
            if (badge) badge.className = 'upload-modal-badge badge-error';
            if (icono) icono.className = 'fas fa-triangle-exclamation';
            if (subtitulo) subtitulo.textContent = 'Ocurrió un error durante el procesamiento.';
            this.detener();
        } else if (data.estado === 'procesando') {
            this.mostrarElemento(spinner);
            this.ocultarElemento(completado);
            this.ocultarElemento(error);
        } else {
            this.mostrarElemento(spinner);
            this.ocultarElemento(completado);
            this.ocultarElemento(error);
        }
    }

    mostrarElemento(elemento) {
        if (elemento) {
            elemento.classList.remove('hidden');
        }
    }

    ocultarElemento(elemento) {
        if (elemento) {
            elemento.classList.add('hidden');
        }
    }

    mostrarErrorConexion() {
        const estadoElement = document.getElementById('estado');
        const detallesElement = document.getElementById('detalles');
        
        if (estadoElement) {
            estadoElement.textContent = 'Error de conexión';
            estadoElement.className = 'text-lg font-semibold text-red-600 mb-2';
        }
        if (detallesElement) {
            detallesElement.textContent = 'No se pudo conectar con el servidor';
        }
    }

    detener() {
        if (this.intervalo) {
            clearInterval(this.intervalo);
            this.intervalo = null;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const revistaIdElement = document.getElementById('revista-id');
    if (revistaIdElement) {
        const revistaId = revistaIdElement.value;
        new ProgresoRevista(revistaId);
    }
});