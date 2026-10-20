@extends('layouts.portal')

@section('title', 'Revistas ITINEWS')

@section('content')
    <div class="container" id="contenido-inicio">
        {{-- BARRA DE BÚSQUEDA --}}
        <div class="search-bar-container">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchRevistas" placeholder="Buscar revistas por título, año o descripción...">
                <span id="resultados-busqueda" class="resultados-busqueda"></span>
            </div>
        </div>

        {{-- REVISTA DEL MES --}}
        <div id="revista-del-mes" style="display: none;"></div>

        <header class="page-header">
            <h1 class="page-title">Portal ITINEWS</h1>
            <p class="page-subtitle">Explora nuestras revistas académicas y mantente actualizado con las últimas investigaciones.</p>
        </header>
        
        <section class="galeria-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-book stat-icon"></i>
                    <div class="stat-number">{{ $revistas->total() }}</div>
                    <div class="stat-label">Total Revistas</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar stat-icon"></i>
                    @php
                        $aniosUnicos = \App\Models\Revista::activas()
                            ->selectRaw('YEAR(fecha_publicacion) as anio')
                            ->distinct()
                            ->count();
                    @endphp
                    <div class="stat-number">{{ $aniosUnicos }}</div>
                    <div class="stat-label">Años Publicados</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-heart stat-icon"></i>
                    <div class="stat-number" id="favoritos-count">0</div>
                    <div class="stat-label">Tus Favoritos</div>
                </div>
            </div>

            <div class="galeria" id="galeria">
                @forelse ($revistas as $revista)
                    <div class="revista" 
                         data-id-revista="{{ $revista->id }}" 
                         data-titulo="{{ $revista->titulo }}" 
                         data-descripcion="{{ $revista->descripcion }}"
                         data-portada="{{ asset('storage/' . $revista->ruta_portada) }}"
                         data-url-visor="{{ route('revistas.show', $revista) }}"
                         data-estacion="{{ $revista->fecha_publicacion->format('Y') }}">
                        
                        <div class="revista-img-container">
                            <div class="skeleton-loader"></div>
                            <img src="{{ asset('storage/' . $revista->ruta_portada) }}" 
                                 alt="Portada de {{ $revista->titulo }}" 
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.previousElementSibling.style.display='none'; this.nextElementSibling.style.display='flex';"
                                 onload="this.previousElementSibling.style.display='none';">
                            <div class="imagen-error" style="display: none;">
                                <i class="fas fa-image"></i>
                                <span>Imagen no disponible</span>
                            </div>
                        </div>
                        
                        <p>{{ $revista->titulo }}</p>
                        
                        <span class="estacion-badge">{{ $revista->fecha_publicacion->format('Y') }}</span>
                        
                        <button class="favorito-btn" title="Añadir a favoritos" data-revista-id="{{ $revista->id }}">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                @empty
                    <p style="text-align: center; grid-column: 1 / -1; padding: 2rem; opacity: 0.7;">
                        No hay revistas publicadas por el momento.
                    </p>
                @endforelse
            </div>

            @if($revistas->hasPages())
                <div class="pagination-container">
                    {{ $revistas->links() }}
                </div>
            @endif
        </section>
    </div>

    {{-- BOTÓN VOLVER ARRIBA --}}
    <button id="btnScrollTop" class="scroll-top-btn" title="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </button>

    <div id="preview-container" style="display: none;" aria-hidden="true">
        <div id="preview-modal" role="dialog" aria-modal="true" aria-labelledby="preview-title">
            <button id="preview-cerrar" class="control-btn" title="Cerrar" aria-label="Cerrar ventana de detalles">
                <i class="fas fa-times"></i>
            </button>
            <div class="preview-content">
                <div class="preview-cover">
                    <img id="preview-img" src="" alt="Portada de la revista">
                </div>
                <div class="preview-details">
                    <div class="preview-header-area">
                        <span class="preview-badge" id="preview-badge">Revista Académica</span>
                        <h2 id="preview-title"></h2>
                    </div>
                    <div class="preview-details-body">
                        <p id="preview-desc"></p>
                    </div>
                    <div class="preview-details-footer">
                        <a id="start-reading-btn" href="#" class="btn-leer">
                            <i class="fas fa-book-open"></i>
                            <span>Comenzar a leer</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @auth
    <meta name="user-id" content="{{ Auth::id() }}">
    @endauth
@endsection

@push('styles')
    <style>
        /* ============================================
           VARIABLES DE COLOR ITINEWS
        ============================================ */
        :root {
            --vino: #7a1830;
            --vino-light: #9a1f3d;
            --vino-dark: #5e1023;
            --verde: #1f5b4f;
            --verde-light: #2a7a6a;
            --verde-dark: #164236;
            --crema: #f8f2ee;
            --crema-dark: #e8d5c8;
            --dark: #1e1f23;
            --white: #ffffff;
            --text-secondary: #4b5563;
        }

        /* MODO OSCURO - Cambia las variables para textos visibles con alto contraste */
        body.dark-mode {
            --dark: #f8f2ee !important;
            --white: #2d2d2d !important;
            --crema: #353535 !important;
            --crema-dark: #2d2d2d !important;
            --text-secondary: #cbd5e1 !important;
        }

        html.dark-mode {
            --dark: #f8f2ee !important;
            --white: #2d2d2d !important;
            --crema: #353535 !important;
            --crema-dark: #2d2d2d !important;
            --text-secondary: #cbd5e1 !important;
        }

        /* ============================================
           BARRA DE BÚSQUEDA
        ============================================ */
        .search-bar-container {
            margin-bottom: 1.5rem;
        }

        .search-bar {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--white);
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            box-shadow: 0 4px 20px rgba(30, 31, 35, 0.08);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .search-bar:focus-within {
            border-color: var(--verde);
            box-shadow: 0 6px 25px rgba(31, 91, 79, 0.15);
        }

        .search-bar i {
            color: var(--verde);
            font-size: 1.2rem;
            margin-right: 0.8rem;
        }

        .search-bar input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-size: 1rem;
            color: var(--dark);
            padding: 0.8rem 0;
            font-family: 'Inter', sans-serif;
        }

        .search-bar input::placeholder {
            color: var(--text-secondary);
        }

        .resultados-busqueda {
            font-size: 0.85rem;
            color: var(--text-secondary);
            white-space: nowrap;
            margin-left: 1rem;
        }

        /* ============================================
           REVISTA DEL MES
        ============================================ */
        .revista-destacada-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(30, 31, 35, 0.1);
            margin-bottom: 2rem;
            border: 2px solid var(--verde);
            position: relative;
        }

        .revista-destacada-badge {
            background: linear-gradient(135deg, var(--verde), var(--verde-dark));
            color: #ffffff;
            padding: 0.6rem 1.5rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .revista-destacada-badge i {
            margin-right: 0.5rem;
        }

        .revista-destacada-content {
            display: flex;
            padding: 2rem;
            gap: 2rem;
            align-items: center;
        }

        .revista-destacada-cover {
            flex: 0 0 200px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .revista-destacada-cover:hover {
            transform: scale(1.05);
        }

        .revista-destacada-cover img {
            width: 100%;
            height: auto;
            display: block;
        }

        .revista-destacada-info {
            flex: 1;
        }

        .revista-destacada-info h3 {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .revista-destacada-anio {
            display: inline-block;
            background: var(--verde);
            color: #ffffff;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .revista-destacada-info p {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .revista-destacada-content {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
            }
            .revista-destacada-cover {
                flex: 0 0 auto;
                max-width: 200px;
                margin: 0 auto;
            }
        }

        /* ============================================
           SKELETON LOADER
        ============================================ */
        .skeleton-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, var(--crema) 25%, var(--crema-dark) 50%, var(--crema) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            z-index: 1;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* ============================================
           BOTÓN VOLVER ARRIBA
        ============================================ */
        .scroll-top-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--verde), var(--verde-dark));
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(31, 91, 79, 0.3);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .scroll-top-btn.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .scroll-top-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(31, 91, 79, 0.5);
        }

        /* ============================================
           TOOLTIP EN FAVORITOS
        ============================================ */
        .favorito-btn::after {
            content: attr(title);
            position: absolute;
            bottom: -35px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark);
            color: var(--white);
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 20;
        }

        .favorito-btn:hover::after {
            opacity: 1;
            bottom: -40px;
        }

        /* ============================================
           HEADER DE PÁGINA
        ============================================ */
        .page-header {
            text-align: center;
            padding: 3rem 2rem;
            margin-bottom: 3rem;
            background: var(--white);
            border-radius: 20px;
            border: 2px solid rgba(31, 91, 79, 0.15);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--verde), var(--vino), var(--verde));
        }

        .page-title {
            color: var(--dark);
            font-size: 2.8rem;
            font-weight: 900;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            color: var(--verde);
            font-size: 1.2rem;
            font-weight: 400;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* ============================================
           STATS GRID
        ============================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2.5rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(30, 31, 35, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--verde);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(31, 91, 79, 0.15);
            border-color: rgba(31, 91, 79, 0.2);
        }

        .stat-card:hover::after {
            transform: scaleX(1);
        }

        .stat-card:nth-child(2)::after {
            background: var(--vino);
        }

        .stat-card:nth-child(2):hover {
            box-shadow: 0 12px 30px rgba(122, 24, 48, 0.15);
            border-color: rgba(122, 24, 48, 0.2);
        }

        .stat-card:nth-child(3)::after {
            background: var(--vino);
        }

        .stat-card:nth-child(3):hover {
            box-shadow: 0 12px 30px rgba(122, 24, 48, 0.15);
            border-color: rgba(122, 24, 48, 0.2);
        }

        .stat-icon {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            color: var(--verde);
            transition: transform 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }

        .stat-card:nth-child(2) .stat-icon,
        .stat-card:nth-child(3) .stat-icon {
            color: var(--vino);
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 900;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .stat-label {
            color: var(--verde);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
        }

        .stat-card:nth-child(2) .stat-label,
        .stat-card:nth-child(3) .stat-label {
            color: var(--vino);
        }

        /* ============================================
           GALERÍA DE REVISTAS
        ============================================ */
        .galeria {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 1rem 0;
        }

        .revista {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(30, 31, 35, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            border: 2px solid transparent;
        }

        .revista:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(31, 91, 79, 0.2);
            border-color: var(--verde);
        }

        .revista-img-container {
            position: relative;
            width: 100%;
            padding-top: 140%;
            background: linear-gradient(135deg, var(--crema), var(--crema-dark));
            overflow: hidden;
        }

        .revista-img-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .revista:hover .revista-img-container img {
            transform: scale(1.08);
        }

        .imagen-error {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--crema), var(--crema-dark));
            color: var(--verde);
            font-size: 1.1rem;
            gap: 1rem;
        }

        .imagen-error i {
            font-size: 3.5rem;
            opacity: 0.4;
        }

        .imagen-error span {
            opacity: 0.7;
            font-weight: 500;
        }

        .revista p {
            padding: 1.2rem 1rem;
            margin: 0;
            color: var(--dark);
            font-weight: 700;
            font-size: 1.1rem;
            text-align: center;
            line-height: 1.4;
        }

        .estacion-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, var(--verde), var(--verde-dark));
            color: #ffffff;
            padding: 0.4rem 1rem;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(31, 91, 79, 0.3);
            z-index: 2;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .revista:hover .estacion-badge {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(31, 91, 79, 0.5);
        }

        .favorito-btn {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid var(--vino);
            color: var(--vino);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 2;
            backdrop-filter: blur(10px);
            font-size: 1.1rem;
        }

        .favorito-btn:hover {
            background: var(--vino);
            color: #ffffff;
            transform: scale(1.15);
            box-shadow: 0 4px 15px rgba(122, 24, 48, 0.3);
        }

        .favorito-btn.active {
            background: var(--vino);
            color: #ffffff;
            animation: heartBeat 0.3s ease;
        }

        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }

        /* ============================================
           PREVIEW MODAL
        ============================================ */
        #preview-container {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            background: rgba(18, 20, 24, 0.88);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            backdrop-filter: blur(12px);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        #preview-modal {
            background: var(--white);
            border-radius: 24px;
            padding: 2.25rem;
            max-width: 820px;
            width: 100%;
            max-height: min(90vh, 760px);
            position: relative;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
            border: 2px solid var(--verde);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin: auto;
            animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(25px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .preview-content {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: stretch;
            overflow: hidden;
            height: 100%;
        }

        .preview-cover {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 2px solid rgba(31, 91, 79, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--crema);
            max-height: 420px;
            height: 100%;
        }

        .preview-cover img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            max-height: 400px;
        }

        .preview-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            min-width: 0;
        }

        .preview-header-area {
            margin-bottom: 0.75rem;
            padding-right: 2.5rem;
        }

        .preview-badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.3rem 0.75rem;
            border-radius: 50px;
            background: rgba(31, 91, 79, 0.12);
            color: var(--verde);
            margin-bottom: 0.5rem;
        }

        .preview-details h2 {
            color: var(--dark);
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.25;
            margin: 0;
            text-align: left;
            letter-spacing: -0.02em;
            word-break: break-word;
        }

        .preview-details-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding-right: 0.5rem;
            margin-bottom: 1.25rem;
            -webkit-overflow-scrolling: touch;
        }

        .preview-details-body::-webkit-scrollbar {
            width: 6px;
        }

        .preview-details-body::-webkit-scrollbar-thumb {
            background: rgba(31, 91, 79, 0.3);
            border-radius: 10px;
        }

        .preview-details p {
            color: var(--text-secondary);
            line-height: 1.65;
            font-size: 1rem;
            text-align: justify;
            margin: 0;
            white-space: pre-line;
        }

        .preview-details-footer {
            flex-shrink: 0;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
        }

        .btn-leer {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.95rem 2.2rem;
            background: linear-gradient(135deg, var(--verde), var(--verde-dark));
            color: #ffffff !important;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.05rem;
            transition: all 0.25s ease;
            box-shadow: 0 6px 20px rgba(31, 91, 79, 0.35);
            letter-spacing: 0.5px;
            width: auto;
        }

        .btn-leer:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(31, 91, 79, 0.45);
            background: linear-gradient(135deg, var(--verde-light), var(--verde));
            color: #ffffff !important;
            text-decoration: none;
        }

        .control-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--vino), var(--vino-dark));
            color: #ffffff !important;
            border: 2px solid var(--white);
            cursor: pointer;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(122, 24, 48, 0.35);
            z-index: 20;
        }

        .control-btn:hover {
            transform: scale(1.1) rotate(90deg);
            background: linear-gradient(135deg, var(--vino-light), var(--vino));
        }

        /* Soporte para modo oscuro en el modal de preview */
        body.dark-mode #preview-modal,
        html.dark-mode #preview-modal {
            background: #23252b !important;
            border-color: #3a9a8a !important;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.75) !important;
        }

        body.dark-mode .preview-cover,
        html.dark-mode .preview-cover {
            background: #18191d !important;
            border-color: rgba(58, 154, 138, 0.3) !important;
        }

        body.dark-mode .preview-badge,
        html.dark-mode .preview-badge {
            background: rgba(58, 154, 138, 0.2) !important;
            color: #5ac8b5 !important;
        }

        body.dark-mode .preview-details h2,
        html.dark-mode .preview-details h2 {
            color: #f8f2ee !important;
        }

        body.dark-mode .preview-details p,
        html.dark-mode .preview-details p {
            color: #d1d5db !important;
        }

        body.dark-mode .preview-details-footer,
        html.dark-mode .preview-details-footer {
            border-top-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* ============================================
           PAGINACIÓN
        ============================================ */
        .pagination-container {
            padding: 25px;
            background: var(--crema);
            border-radius: 15px;
            margin-top: 2rem;
            border: 2px solid rgba(31, 91, 79, 0.15);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 0;
        }

        .page-item .page-link {
            color: var(--verde);
            border: 2px solid rgba(31, 91, 79, 0.2);
            background: var(--white);
            border-radius: 10px;
            padding: 0.6rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .page-item .page-link:hover {
            background: var(--verde);
            color: #ffffff;
            border-color: var(--verde);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(31, 91, 79, 0.2);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--verde), var(--verde-dark));
            border-color: var(--verde);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(31, 91, 79, 0.3);
        }

        .page-item.disabled .page-link {
            color: #999;
            border-color: rgba(0, 0, 0, 0.1);
            background: var(--crema);
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 1rem;
            }
            .page-title {
                font-size: 2rem;
            }
            .page-subtitle {
                font-size: 1rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .stat-card {
                padding: 1.5rem;
            }
            .stat-number {
                font-size: 2.2rem;
            }
            .galeria {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }
            #preview-container {
                padding: 0.75rem;
                align-items: center;
            }
            #preview-modal {
                padding: 1.25rem 1rem 1rem 1rem;
                border-radius: 20px;
                max-height: calc(100dvh - 1.5rem);
                height: auto;
                width: 100%;
                max-width: 480px;
                margin: auto;
            }
            .preview-content {
                display: flex;
                flex-direction: column;
                gap: 0.85rem;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                max-height: calc(100dvh - 3.5rem);
            }
            .preview-cover {
                min-height: auto;
                max-height: 180px;
                height: 180px;
                width: 130px;
                margin: 0 auto;
                flex-shrink: 0;
                background: transparent;
                border: none;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
            }
            .preview-cover img {
                max-height: 180px;
                border-radius: 8px;
                object-fit: contain;
            }
            .preview-header-area {
                margin-bottom: 0.5rem;
                padding-right: 2.25rem;
                text-align: left;
            }
            .preview-badge {
                font-size: 0.7rem;
                padding: 0.25rem 0.6rem;
                margin-bottom: 0.35rem;
            }
            .preview-details h2 {
                font-size: 1.3rem;
                line-height: 1.25;
            }
            .preview-details-body {
                max-height: 150px;
                margin-bottom: 0.75rem;
                padding-right: 0.35rem;
            }
            .preview-details p {
                font-size: 0.92rem;
                line-height: 1.55;
                text-align: left;
            }
            .preview-details-footer {
                padding-top: 0.5rem;
            }
            .btn-leer {
                width: 100%;
                justify-content: center;
                padding: 0.85rem 1.25rem;
                font-size: 1rem;
            }
            .control-btn {
                top: 0.65rem;
                right: 0.65rem;
                width: 34px;
                height: 34px;
                font-size: 0.95rem;
            }
            .scroll-top-btn {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }

        @media (max-width: 480px) {
            .galeria {
                grid-template-columns: 1fr;
            }
            .estacion-badge {
                font-size: 0.75rem;
                padding: 0.3rem 0.7rem;
            }
            .favorito-btn {
                width: 35px;
                height: 35px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/flipbook-manager.js') }}"></script>
@endpush