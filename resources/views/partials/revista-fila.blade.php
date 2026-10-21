@php
    // Fila de la tabla de revistas publicadas.
    $paginas = (int) ($revista->paginas_count ?? $revista->paginas()->count());
    $fecha = $revista->fecha_publicacion;
@endphp

<tr class="rev-fila revista-row"
    data-revista-item
    data-status="{{ $revista->estado_procesamiento }}"
    data-activo="{{ $revista->estaActiva() ? '1' : '0' }}"
    data-titulo="{{ \Illuminate\Support\Str::lower($revista->titulo) }}"
    data-descripcion="{{ \Illuminate\Support\Str::lower($revista->descripcion ?? '') }}"
    data-fecha="{{ optional($fecha)->format('Y-m-d') }}"
    data-paginas="{{ $paginas }}">

    <td class="rev-celda-revista">
        <div class="rev-celda-revista-inner">
            <a class="rev-portada"
               href="{{ route('revistas.show', $revista) }}"
               title="Ver {{ $revista->titulo }}"
               tabindex="-1"
               aria-hidden="true">
                <img src="{{ asset('storage/' . $revista->ruta_portada) }}"
                     alt=""
                     loading="lazy">
            </a>
            <div class="rev-datos">
                <a class="rev-titulo revista-titulo" href="{{ route('revistas.show', $revista) }}">
                    {{ $revista->titulo }}
                </a>
                @if($revista->esDestacada())
                    <span class="badge-destacada" title="Revista Destacada" style="display:inline-flex; align-items:center; gap:4px; font-size:0.72rem; padding:2px 7px; background:rgba(245, 158, 11, 0.15); color:#d97706; border-radius:999px; width:fit-content; font-weight:600; margin-top:2px;">
                        <i class="fas fa-star" style="font-size:0.68rem; color:#f59e0b;"></i> Destacada
                    </span>
                @endif
                @if($revista->descripcion)
                    <span class="rev-desc revista-descripcion">
                        {{ \Illuminate\Support\Str::limit($revista->descripcion, 80) }}
                    </span>
                @endif
            </div>
        </div>
    </td>

    <td class="rev-celda-estado">
        @include('partials.revista-estado', ['revista' => $revista])
        @unless($revista->estaActiva())
            <span class="rev-visibilidad oculta"><i class="fas fa-eye-slash" aria-hidden="true"></i> Oculta</span>
        @endunless
    </td>

    <td class="rev-celda-fecha revista-fecha">
        <i class="far fa-calendar" aria-hidden="true"></i>
        {{ optional($fecha)->format('d/m/Y') ?? '—' }}
    </td>

    <td class="rev-celda-paginas revista-paginas">
        <i class="far fa-file-lines" aria-hidden="true"></i>
        {{ $paginas }}
    </td>

    <td class="rev-celda-acciones revista-acciones">
        @include('partials.revista-acciones', ['revista' => $revista])
    </td>
</tr>
