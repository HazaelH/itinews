/* =====================================================================
   Perfil de usuario — controles del formulario de contraseña
   Mostrar/ocultar contraseña y medidor de fuerza. Sin dependencias.
   ===================================================================== */
(function () {
    'use strict';

    /* ---------- Mostrar / ocultar contraseña ---------- */
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = btn.closest('.pf-input-wrap');
            var input = wrap ? wrap.querySelector('input') : null;
            if (!input) return;

            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';

            var icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            }

            btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    /* ---------- Medidor de fuerza ---------- */
    var strength = document.querySelector('[data-pw-strength]');
    if (!strength) return;

    var bars = document.querySelector('[data-pw-bars]');
    var help = document.querySelector('[data-pw-help]');
    var etiquetas = ['Muy débil', 'Débil', 'Aceptable', 'Fuerte', 'Muy fuerte'];

    function evaluar(valor) {
        var score = 0;
        if (valor.length >= 8) score++;
        if (/[a-z]/.test(valor) && /[A-Z]/.test(valor)) score++;
        if (/\d/.test(valor)) score++;
        if (/[^A-Za-z0-9]/.test(valor)) score++;
        if (valor.length === 0) score = 0;
        return score;
    }

    strength.addEventListener('input', function () {
        var valor = strength.value;
        var score = evaluar(valor);

        if (bars) {
            bars.className = 'pw-fuerza' + (score > 0 ? ' n' + score : '');
        }
        if (help) {
            help.textContent = valor.length === 0
                ? 'Mínimo 8 caracteres.'
                : 'Seguridad: ' + etiquetas[score];
        }
    });
})();
