@php
    // Tarjeta de la vista alternativa de revistas publicadas.
    $paginas = (int) ($revista->paginas_count ?? $revista->paginas()->count());
    $fecha = $revista->fecha_publicacion;
@endphp

<article class="rev-tarjeta"
    data-revista-item
    data-status="{{ $revista->estado_procesamiento }}"
    data-activo="{{ $revista->estaActiva() ? '1' : '0' }}"
    data-titulo="{{ \Illuminate\Support\Str::lower($revista->titulo) }}"
    data-descripcion="{{ \Illuminate\Support\Str::lower($revista->descripcion ?? '') }}"
    data-fecha="{{ optional($fecha)->format('Y-m-d') }}"
    data-paginas="{{ $paginas }}">

    <a class="rev-tarjeta-portada"
       href="{{ route('revistas.show', $revista) }}"
       title="Ver {{ $revista->titulo }}">
        <img src="{{ asset('storage/' . $revista->ruta_portada) }}"
             alt="Portada de {{ $revista->titulo }}"
             loading="lazy">
        <span class="rev-tarjeta-overlay"><i class="far fa-eye" aria-hidden="true"></i> Ver revista</span>
    </a>

    <div class="rev-tarjeta-cuerpo">
        <h4 class="rev-titulo revista-titulo">{{ $revista->titulo }}</h4>

        @if($revista->descripcion)
            <p class="rev-desc revista-descripcion">
                {{ \Illuminate\Support\Str::limit($revista->descripcion, 90) }}
            </p>
        @endif

        <div class="rev-tarjeta-estado">
            @include('partials.revista-estado', ['revista' => $revista])
            @unless($revista->estaActiva())
                <span class="rev-visibilidad oculta"><i class="fas fa-eye-slash" aria-hidden="true"></i> Oculta</span>
            @endunless
        </div>

        <div class="rev-tarjeta-meta">
            <span class="rev-celda-fecha revista-fecha">
                <i class="far fa-calendar" aria-hidden="true"></i>
                {{ optional($fecha)->format('d/m/Y') ?? '—' }}
            </span>
            <span class="rev-celda-paginas revista-paginas">
                <i class="far fa-file-lines" aria-hidden="true"></i>
                {{ $paginas }} {{ $paginas === 1 ? 'pág.' : 'págs.' }}
            </span>
        </div>

        <div class="rev-tarjeta-acciones">
            @include('partials.revista-acciones', ['revista' => $revista])
        </div>
    </div>
</article>
