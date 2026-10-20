/* =====================================================================
   Carrusel de portadas — pantalla de acceso ITINEWS
   Cambio automático con fundido, puntos de navegación y pausa al pasar
   el cursor. Sin dependencias.
   ===================================================================== */
(function () {
    'use strict';

    var stage = document.getElementById('authCarrusel');
    if (!stage) return;

    var slides = Array.prototype.slice.call(stage.querySelectorAll('.auth-slide'));
    if (!slides.length) return;

    if (slides.length < 2) {
        slides[0].classList.add('is-active');
        return;
    }

    var dotsWrap = document.getElementById('authCarruselDots');
    var current = 0;
    var timer = null;
    var DELAY = 4200;

    function paint(i) {
        if (slides[current]) slides[current].classList.remove('is-active');
        if (dotsWrap && dotsWrap.children[current]) {
            dotsWrap.children[current].classList.remove('is-active');
        }

        current = (i + slides.length) % slides.length;

        if (slides[current]) slides[current].classList.add('is-active');
        if (dotsWrap && dotsWrap.children[current]) {
            dotsWrap.children[current].classList.add('is-active');
        }
    }

    function next() {
        paint(current + 1);
    }

    function stop() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    function start() {
        stop();
        timer = setInterval(next, DELAY);
    }

    if (dotsWrap) {
        slides.forEach(function (_, i) {
            var dot = document.createElement('button');
            dot.type = 'button';
            dot.setAttribute('aria-label', 'Ver portada ' + (i + 1));
            if (i === 0) dot.classList.add('is-active');
            dot.addEventListener('click', function () {
                paint(i);
                start();
            });
            dotsWrap.appendChild(dot);
        });
    }

    slides[0].classList.add('is-active');

    stage.addEventListener('mouseenter', stop);
    stage.addEventListener('mouseleave', start);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stop();
        } else {
            start();
        }
    });

    start();
})();
