/* =====================================================================
   Pantallas de autenticación — contraseña
   - Inyecta un botón "mostrar/ocultar" en cada input[type=password]
     dentro de un .inputBox.
   - Si el input lleva [data-pw-strength], agrega el medidor de fuerza.
   Sin dependencias. Se carga desde layouts/guest.blade.php.
   ===================================================================== */
(function () {
    'use strict';

    var ETIQUETAS = ['Muy débil', 'Débil', 'Aceptable', 'Fuerte', 'Muy fuerte'];
    var AYUDA_BASE = 'Mínimo 8 caracteres, con letras y números.';

    function evaluar(valor) {
        if (valor.length === 0) {
            return 0;
        }
        var score = 0;
        if (valor.length >= 8) score++;
        if (/[a-z]/.test(valor) && /[A-Z]/.test(valor)) score++;
        if (/\d/.test(valor)) score++;
        if (/[^A-Za-z0-9]/.test(valor)) score++;
        return score;
    }

    function crearToggle(input, box) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'auth-pw-toggle';
        btn.setAttribute('aria-label', 'Mostrar contraseña');
        btn.innerHTML = '<i class="fas fa-eye" aria-hidden="true"></i>';

        btn.addEventListener('click', function () {
            var mostrar = input.type === 'password';
            input.type = mostrar ? 'text' : 'password';

            var icono = btn.querySelector('i');
            if (icono) {
                icono.classList.toggle('fa-eye', !mostrar);
                icono.classList.toggle('fa-eye-slash', mostrar);
            }

            btn.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
            input.focus();
        });

        box.appendChild(btn);
        box.classList.add('has-pw');
    }

    function crearMedidor(input, box) {
        var medidor = document.createElement('div');
        medidor.className = 'auth-pw-fuerza pw-fuerza';
        medidor.setAttribute('data-pw-bars', '');
        medidor.setAttribute('aria-hidden', 'true');
        for (var i = 0; i < 4; i++) {
            medidor.appendChild(document.createElement('span'));
        }

        var ayuda = document.createElement('small');
        ayuda.className = 'auth-pw-help';
        ayuda.setAttribute('data-pw-help', '');
        ayuda.setAttribute('aria-live', 'polite');
        ayuda.textContent = AYUDA_BASE;

        // Van fuera del .inputBox para no deformar la línea animada inferior.
        box.parentNode.insertBefore(medidor, box.nextSibling);
        box.parentNode.insertBefore(ayuda, medidor.nextSibling);

        input.addEventListener('input', function () {
            var score = evaluar(input.value);
            medidor.className = 'auth-pw-fuerza pw-fuerza' + (score > 0 ? ' n' + score : '');
            ayuda.textContent = input.value.length === 0
                ? AYUDA_BASE
                : 'Seguridad: ' + ETIQUETAS[score];
        });
    }

    function init() {
        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            var box = input.closest('.inputBox');
            if (!box || box.dataset.pwInit === '1') {
                return;
            }
            box.dataset.pwInit = '1';

            crearToggle(input, box);

            if (input.hasAttribute('data-pw-strength')) {
                crearMedidor(input, box);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
