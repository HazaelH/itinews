/**
 * Gestión de usuarios (panel ITINEWS).
 *
 *   1. Buscador en vivo + filtros (rol / estado) y contador de resultados.
 *   2. Mostrar/ocultar contraseña y medidor de fortaleza.
 *
 * Es tolerante a ausencia de elementos: sirve tanto en el listado como en
 * los formularios de crear/editar.
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    ready(function () {
        // -------------------------------------------------------------
        // 1. Buscador y filtros (solo en el listado)
        // -------------------------------------------------------------
        var buscar = document.getElementById('usrBuscar');
        var filtroRol = document.getElementById('usrRol');
        var filtroEstado = document.getElementById('usrEstado');
        var tbody = document.getElementById('usrTablaBody');
        var resultados = document.getElementById('usrResultados');
        var sinResultados = document.getElementById('usrSinResultados');

        if (tbody) {
            var filas = Array.prototype.slice.call(tbody.querySelectorAll('[data-user-item]'));
            var tabla = tbody.closest('.panel-tabla-scroll');
            var paginacion = tbody.closest('.panel-tabla-card')
                ? tbody.closest('.panel-tabla-card').querySelector('.pagination, nav')
                : null;

            var aplicar = function () {
                var q = buscar ? buscar.value.toLowerCase().trim() : '';
                var rol = filtroRol ? filtroRol.value : '';
                var estado = filtroEstado ? filtroEstado.value : '';

                var visibles = 0;
                filas.forEach(function (fila) {
                    var nombre = fila.dataset.nombre || '';
                    var okTexto = q === '' || nombre.indexOf(q) !== -1;
                    var okRol = rol === '' || fila.dataset.rol === rol;
                    var okEstado = estado === '' || fila.dataset.activo === estado;
                    var visible = okTexto && okRol && okEstado;

                    fila.hidden = !visible;
                    if (visible) {
                        visibles++;
                    }
                });

                var filtrando = q !== '' || rol !== '' || estado !== '';
                if (resultados) {
                    resultados.hidden = !filtrando;
                    resultados.textContent = visibles + ' de ' + filas.length;
                }
                if (sinResultados) {
                    sinResultados.hidden = !(filtrando && visibles === 0);
                }
                if (tabla) {
                    tabla.hidden = filtrando && visibles === 0;
                }
                if (paginacion) {
                    paginacion.hidden = filtrando;
                }
            };

            if (buscar) {
                buscar.addEventListener('input', aplicar);
            }
            if (filtroRol) {
                filtroRol.addEventListener('change', aplicar);
            }
            if (filtroEstado) {
                filtroEstado.addEventListener('change', aplicar);
            }
        }

        // -------------------------------------------------------------
        // 2. Contraseña: mostrar/ocultar y fortaleza
        // -------------------------------------------------------------
        var toggles = document.querySelectorAll('.pw-toggle');
        Array.prototype.forEach.call(toggles, function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.pw);
                if (!input) {
                    return;
                }
                var mostrar = input.type === 'password';
                input.type = mostrar ? 'text' : 'password';
                var icono = btn.querySelector('i');
                if (icono) {
                    icono.className = mostrar ? 'far fa-eye-slash' : 'far fa-eye';
                }
            });
        });

        var pw = document.getElementById('password');
        var indicador = document.querySelector('[data-pw-indicador="password"]');
        if (pw && indicador) {
            pw.addEventListener('input', function () {
                var v = pw.value;
                var puntos = 0;
                if (v.length >= 8) { puntos++; }
                if (/[A-Z]/.test(v) && /[a-z]/.test(v)) { puntos++; }
                if (/\d/.test(v)) { puntos++; }
                if (/[^A-Za-z0-9]/.test(v)) { puntos++; }

                indicador.className = 'pw-fuerza' + (v.length ? ' n' + Math.max(1, puntos) : '');
            });
        }
    });
})();
