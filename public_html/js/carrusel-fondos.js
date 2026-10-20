/* =====================================================================
   Carrusel de fondos del portal (público)
   - [data-fondos-bg]    : fondo de página con capas .fondo-capa (cross-fade)
   - [data-carrusel-img] : imagen destacada; lee data-srcs (JSON) y data-intervalo
   - Dots opcionales en [data-carrusel-dots] dentro del mismo contenedor.
   Sin dependencias.
   ===================================================================== */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function leerSrcs(el) {
        try {
            var lista = JSON.parse(el.getAttribute('data-srcs') || '[]');
            return Array.isArray(lista) ? lista.filter(Boolean) : [];
        } catch (e) {
            return [];
        }
    }

    function intervalo(el, porDefecto) {
        var v = parseInt(el.getAttribute('data-intervalo') || '', 10);
        return (isNaN(v) || v < 1500) ? porDefecto : v;
    }

    ready(function () {
        /* Registro de controles para pausar/reanudar con la pestaña. */
        var controles = [];

        /* -------------------------------------------------------------
           1. Fondo de página (capas fijas con cross-fade)
           ------------------------------------------------------------- */
        document.querySelectorAll('[data-fondos-bg]').forEach(function (bg) {
            var capas = bg.querySelectorAll('.fondo-capa');
            if (capas.length < 2) {
                return;
            }

            var actual = 0;
            var delay = intervalo(bg, 6000);
            var timer = null;

            function avanzar() {
                capas[actual].classList.remove('is-active');
                actual = (actual + 1) % capas.length;
                capas[actual].classList.add('is-active');
            }

            function stop() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }

            function start() {
                stop();
                timer = setInterval(avanzar, delay);
            }

            controles.push({ start: start, stop: stop });

            start();
        });

        /* -------------------------------------------------------------
           2. Imagen destacada (funde y cambia el src)
           ------------------------------------------------------------- */
        document.querySelectorAll('[data-carrusel-img]').forEach(function (img) {
            var srcs = leerSrcs(img);
            if (srcs.length < 2) {
                return;
            }

            var contenedor = img.closest('.carrusel-fondos') || img.parentElement;
            var dots = contenedor ? contenedor.querySelector('[data-carrusel-dots]') : null;
            var delay = intervalo(img, 5000);
            var actual = 0;
            var timer = null;

            function pintarDots() {
                if (!dots) {
                    return;
                }
                dots.innerHTML = '';
                srcs.forEach(function (_, i) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.setAttribute('aria-label', 'Ver imagen ' + (i + 1));
                    if (i === actual) {
                        b.classList.add('is-active');
                    }
                    b.addEventListener('click', function () {
                        irA(i);
                        reiniciar();
                    });
                    dots.appendChild(b);
                });
            }

            function irA(indice) {
                var destino = srcs[(indice + srcs.length) % srcs.length];
                actual = (indice + srcs.length) % srcs.length;

                img.style.opacity = '0';

                var pre = new Image();
                function mostrar() {
                    img.src = destino;
                    img.style.opacity = '1';
                    pintarDots();
                }
                pre.onload = mostrar;
                pre.onerror = mostrar;
                pre.src = destino;
            }

            function avanzar() {
                irA(actual + 1);
            }

            function stop() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }

            function start() {
                stop();
                timer = setInterval(avanzar, delay);
            }

            function reiniciar() {
                stop();
                timer = setInterval(avanzar, delay);
            }

            controles.push({ start: start, stop: stop });

            pintarDots();
            start();

            /* Pausa al pasar el mouse */
            if (contenedor) {
                contenedor.addEventListener('mouseenter', stop);
                contenedor.addEventListener('mouseleave', start);
            }
        });

        /* -------------------------------------------------------------
           3. Pausa global al ocultar la pestaña (y reanuda al volver)
           ------------------------------------------------------------- */
        document.addEventListener('visibilitychange', function () {
            controles.forEach(function (c) {
                document.hidden ? c.stop() : c.start();
            });
        });
    });
})();
