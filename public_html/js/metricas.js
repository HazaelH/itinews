/**
 * Métricas anónimas del portal ITINEWS.
 *
 * Envía eventos (apertura de portal, apertura de revista, cambio de página y
 * cierre) a los endpoints públicos /api/metricas/*. No requiere login: cada
 * visitante se identifica con un token aleatorio guardado en localStorage y en
 * una cookie de primer nivel.
 *
 * Expone `window.ItinMetricas` para que el visor (flipbook-viewer.js) reporte
 * los cambios de página y el tiempo de lectura.
 */
(function () {
    'use strict';

    if (window.__itinMetricasLoaded) {
        return;
    }
    window.__itinMetricasLoaded = true;

    var BASE = (window.ITIN_METRICAS_BASE || '/api/metricas').replace(/\/$/, '');
    var VISITOR_KEY = 'itin_visitor_token';
    var COOKIE_NAME = 'itin_visitor';
    var COOKIE_DAYS = 365;

    function uuid() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }
        // Fallback RFC4122 v4
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0;
            var v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    function readCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function writeCookie(name, value, days) {
        var expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value) +
            '; expires=' + expires + '; path=/; SameSite=Lax';
    }

    function getVisitorToken() {
        var token = null;

        try {
            token = window.localStorage.getItem(VISITOR_KEY);
        } catch (e) { /* localStorage no disponible */ }

        if (!token) {
            token = readCookie(COOKIE_NAME);
        }

        if (!token) {
            token = uuid();
        }

        try {
            window.localStorage.setItem(VISITOR_KEY, token);
        } catch (e) { /* ignora */ }

        writeCookie(COOKIE_NAME, token, COOKIE_DAYS);

        return token;
    }

    function jsonBeacon(url, payload) {
        if (navigator.sendBeacon) {
            try {
                var blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
                if (navigator.sendBeacon(url, blob)) {
                    return Promise.resolve();
                }
            } catch (e) { /* cae al fetch */ }
        }

        return postJson(url, payload);
    }

    function postJson(url, payload) {
        if (!window.fetch) {
            return Promise.resolve(null);
        }

        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload),
            keepalive: true
        }).catch(function () { return null; });
    }

    var visitorToken = getVisitorToken();
    var state = { visitToken: null, cerrado: false };

    var api = {
        visitorToken: visitorToken,

        /**
         * Abre una visita de revista y guarda el token devuelto por el servidor.
         */
        startRevista: function (revistaId) {
            return postJson(BASE + '/revista', {
                visitor_token: visitorToken,
                revista_id: revistaId
            }).then(function (response) {
                if (!response || !response.json) {
                    return null;
                }
                return response.json().then(function (data) {
                    if (data && data.visit_token) {
                        state.visitToken = data.visit_token;
                        state.cerrado = false;
                    }
                    return data;
                });
            });
        },

        /**
         * Registra una página vista (1-based).
         */
        page: function (pageNumber) {
            if (!state.visitToken || !pageNumber) {
                return;
            }
            postJson(BASE + '/pagina', {
                visit_token: state.visitToken,
                page_number: pageNumber
            });
        },

        /**
         * Cierra la visita con el tiempo y páginas acumuladas.
         */
        close: function (durationSeconds, pagesViewed, totalPages) {
            if (!state.visitToken) {
                return;
            }
            jsonBeacon(BASE + '/cerrar', {
                visit_token: state.visitToken,
                duration_seconds: Math.max(0, Math.round(durationSeconds || 0)),
                pages_viewed: pagesViewed || 0,
                total_pages: totalPages || 0
            });
            state.cerrado = true;
        },

        /**
         * Apertura del portal.
         */
        portal: function () {
            jsonBeacon(BASE + '/portal', {
                visitor_token: visitorToken,
                path: window.location.pathname
            });
        }
    };

    window.ItinMetricas = api;

    // Cierre automático al salir de la página (por si el visor no lo hace).
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden' && window.__itinCerrarVisita) {
            window.__itinCerrarVisita();
        }
    });

    // Tracking automático del portal cuando la página lo declara.
    function marcarPortal() {
        var marca = document.body && document.body.dataset ? document.body.dataset.metricaPage : null;
        if (marca === 'portal' || marca === 'landing') {
            api.portal();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', marcarPortal);
    } else {
        marcarPortal();
    }
})();
