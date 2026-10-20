@extends('layouts.visor')

@section('visor-content')
<div id="app-container" 
     class="visor-container" 
     data-revista-id="{{ $revista->id }}"
     data-images="{{ json_encode($revista->paginas->map(fn($p) => asset('storage/' . $p->ruta_imagen))) }}">
    
    <header class="visor-header">
        <div class="header-info">
            <h1 class="revista-titulo">{{ $revista->titulo }}</h1>
            <span class="revista-fecha">
                <i class="far fa-calendar-alt"></i> 
                {{ \Carbon\Carbon::parse($revista->fecha_publicacion)->format('d M, Y') }}
            </span>
        </div>
        
        <a href="{{ route('revistas.index') }}" class="btn-cerrar" title="Salir del visor">
            <i class="fas fa-times"></i>
            <span class="text-cerrar">Cerrar Visor</span>
        </a>
    </header>

    @if($oculta ?? false)
        <div style="display:flex; align-items:center; gap:10px; justify-content:center;
                    background:#d98c1f; color:#3b2a08; font-weight:700; padding:10px 16px;
                    font-size:.9rem; text-align:center;">
            <i class="fas fa-eye-slash" aria-hidden="true"></i>
            Revista oculta: solo administradores y editores pueden ver esta vista previa.
        </div>
    @endif

    <main class="visor-stage">
        <div id="visor-spinner" class="spinner-overlay">
            <div class="spinner"></div>
            <p>Cargando revista...</p>
        </div>

        <div id="flipbook"></div>
    </main>

    <footer class="visor-controls hidden-ui">
        <button id="prev" class="btn-control" title="Página anterior">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <div class="page-counter" id="contadorPaginas">Cargando...</div>
        
        <button id="next" class="btn-control" title="Página siguiente">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="divider"></div>
        
        <button id="zoom-out" class="btn-control" title="Zoom out">
            <i class="fas fa-search-minus"></i>
        </button>
        
        <button id="zoom-in" class="btn-control" title="Zoom in">
            <i class="fas fa-search-plus"></i>
        </button>
        
        <div class="divider"></div>
        
        <button id="btn-fullscreen" class="btn-control" title="Pantalla completa">
            <i class="fas fa-expand"></i>
        </button>
    </footer>

    <div id="zoom-overlay"></div>
</div>

@endsection