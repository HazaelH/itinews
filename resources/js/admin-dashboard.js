class NotificationSystem {
    constructor() {
        this.container = document.getElementById('notifications-container');
        if (!this.container) {
            this.createNotificationsContainer();
        }
    }

    createNotificationsContainer() {
        // Creamos el contenedor si no existe
        this.container = document.createElement('div');
        this.container.id = 'notifications-container';
        this.container.className = 'notifications-wrapper';
        document.body.appendChild(this.container);
    }

    getIcon(type) {
        // Iconos usando FontAwesome
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-times-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-info-circle"></i>'
        };
        return icons[type] || icons.info;
    }

    show(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification-toast notification-${type}`;
        
        notification.innerHTML = `
            <div class="notification-icon">
                ${this.getIcon(type)}
            </div>
            <div class="notification-content">
                <p class="notification-message">${message}</p>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.add('hide'); setTimeout(() => this.parentElement.remove(), 400);">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Append child al final para que se apilen
        this.container.appendChild(notification);

        // Forzar reflow para que la animación CSS funcione
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });

        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('show');
                notification.classList.add('hide');
                setTimeout(() => notification.remove(), 400); // Esperar a que termine la animación
            }
        }, 5000);

        return notification;
    }

    success(message) { return this.show(message, 'success'); }
    error(message) { return this.show(message, 'error'); }
    info(message) { return this.show(message, 'info'); }
    warning(message) { return this.show(message, 'warning'); }
}

class SearchAndFilter {
    constructor() {
        this.searchInput = document.getElementById('searchRevistas');
        this.filterSelect = document.getElementById('filterStatus');
        this.rows = document.querySelectorAll('#revistasTableBody .revista-row');
        this.init();
    }

    init() {
        if (this.searchInput) this.searchInput.addEventListener('input', this.handleSearch.bind(this));
        if (this.filterSelect) this.filterSelect.addEventListener('change', this.handleFilter.bind(this));
    }

    handleSearch(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        this.applyFilters(searchTerm);
    }

    handleFilter() {
        this.applyFilters(this.searchInput?.value.toLowerCase().trim() || '');
    }

    applyFilters(searchTerm = '') {
        let visibleCount = 0;
        this.rows.forEach(row => {
            const matchesSearch = this.matchesSearch(row, searchTerm);
            const matchesFilter = this.matchesFilter(row);
            row.style.display = matchesSearch && matchesFilter ? '' : 'none';
            if (matchesSearch && matchesFilter) visibleCount++;
        });
        this.updateResultsCounter(visibleCount);
    }

    matchesSearch(row, searchTerm) {
        if (!searchTerm) return true;
        const titulo = row.querySelector('.revista-titulo')?.textContent.toLowerCase() || '';
        const descripcion = row.querySelector('.revista-descripcion')?.textContent.toLowerCase() || '';
        return (titulo + ' ' + descripcion).includes(searchTerm);
    }

    matchesFilter(row) {
        const filterStatus = this.filterSelect?.value;
        if (!filterStatus) return true;
        return row.getAttribute('data-status') === filterStatus;
    }

    updateResultsCounter(count) {
        let contador = document.getElementById('resultadosContador');
        if (!contador) {
            contador = document.createElement('div');
            contador.id = 'resultadosContador';
            contador.className = 'resultados-contador';
            const header = document.querySelector('.dashboard-table-header');
            if (header) header.appendChild(contador);
        }
        contador.textContent = `${count} revista(s) encontrada(s)`;
    }
}

class ProgressUpdater {
    constructor() {
        this.notifications = new NotificationSystem();
        this.updateInterval = null;
        this.init();
    }

    init() {
        if (this.hasProcessingRevistas()) {
            this.startAutoUpdate();
        }
    }

    hasProcessingRevistas() {
        return document.querySelectorAll('.status-processing').length > 0;
    }

    startAutoUpdate() {
        this.updateInterval = setInterval(() => this.updateAllProgresses(), 3000);
    }

    stopAutoUpdate() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    async updateAllProgresses() {
        const revistasProcesando = document.querySelectorAll('.status-processing');
        if (revistasProcesando.length === 0) {
            this.stopAutoUpdate();
            return;
        }
        const updatePromises = Array.from(revistasProcesando).map(statusElement => 
            this.updateRevistaProgress(statusElement)
        );
        await Promise.allSettled(updatePromises);
    }

    async updateRevistaProgress(statusElement) {
        const row = statusElement.closest('.revista-row');
        const progressBar = row?.querySelector('.progress-bar-fill');
        if (!progressBar) return;

        const revistaId = progressBar.getAttribute('data-revista-id');
        try {
            const response = await fetch(`/revista/${revistaId}/progreso`);
            if (!response.ok) throw new Error('Error servidor');
            
            const data = await response.json();
            this.updateProgressUI(row, data);

            if (data.completado || data.error) {
                this.handleCompletion(data, revistaId);
            }
        } catch (error) {
            console.error('Error progreso:', error);
        }
    }

    updateProgressUI(row, data) {
        const progressBar = row.querySelector('.progress-bar-fill');
        const progresoElement = row.querySelector('.revista-progreso');
        if (progressBar) progressBar.style.width = data.progreso + '%';
        if (progresoElement) progresoElement.textContent = Math.round(data.progreso) + '%';
    }

    handleCompletion(data, revistaId) {
        const message = data.completado ? 'Revista procesada correctamente' : 'Error al procesar revista';
        const type = data.completado ? 'success' : 'error';
        this.notifications.show(message, type);
        setTimeout(() => location.reload(), 2000);
    }
}

class AdminDashboard {
    constructor() {
        const isAdminRoute = window.location.pathname.includes('/admin') || 
                             window.location.pathname.includes('/dashboard');
        const hasAdminElement = document.querySelector('.dashboard-container') !== null;

        this.notifications = new NotificationSystem();

        if (isAdminRoute || hasAdminElement) {
            this.searchFilter = null;
            this.progressUpdater = null;
            this.init();
        }
    }

    init() {
        this.initializeSearchAndFilter();
        this.initializeProgressUpdater();
        this.initializeSessionNotifications();
        
        if (!sessionStorage.getItem('welcomeShown')) {
             this.initializeWelcomeNotification();
             sessionStorage.setItem('welcomeShown', 'true');
        }
    }

    initializeSearchAndFilter() {
        // El panel de revistas del dashboard ya lo maneja dashboard-revistas.js.
        if (window.__ITIN_REVISTAS_UI__) {
            return;
        }
        if (document.getElementById('searchRevistas')) {
            this.searchFilter = new SearchAndFilter();
        }
    }

    initializeProgressUpdater() {
        // El progreso de las revistas en proceso ya lo sigue dashboard-revistas.js.
        if (window.__ITIN_REVISTAS_UI__) {
            return;
        }
        this.progressUpdater = new ProgressUpdater();
    }

    initializeSessionNotifications() {
        if (typeof window.laravelNotifications !== 'undefined') {
            window.laravelNotifications.forEach(notification => {
                this.notifications.show(notification.message, notification.type);
            });
        }
    }

    initializeWelcomeNotification() {
        setTimeout(() => {
            this.notifications.info('Panel de administración listo');
        }, 1000);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.adminDashboard = new AdminDashboard();
    window.mostrarNotificacion = function(mensaje, tipo = 'info') {
        if (window.adminDashboard && window.adminDashboard.notifications) {
            window.adminDashboard.notifications.show(mensaje, tipo);
        }
    };
});