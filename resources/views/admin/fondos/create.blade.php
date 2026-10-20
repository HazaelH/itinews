<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    <div class="panel-page">
        <div class="panel-wrap">

            <header class="panel-head">
                <div>
                    <h1>Nuevo Fondo</h1>
                    <p>Agrega una imagen al carrusel del portal (inicio, revistas o ambas).</p>
                </div>
                <div class="panel-actions">
                    <button type="button" class="btn-panel ghost" data-fondo-guia-btn>
                        <i class="fas fa-circle-question" aria-hidden="true"></i> ¿Cómo funciona?
                    </button>
                    <a href="{{ route('admin.fondos.index') }}" class="btn-panel ghost" data-nav-link>
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a Fondos
                    </a>
                </div>
            </header>

            <div class="panel-form-card">
                <form action="{{ route('admin.fondos.store') }}" method="POST" enctype="multipart/form-data" id="fondoForm">
                    @csrf

                    @include('admin.fondos._form')

                    <div class="panel-form-pie">
                        <a href="{{ route('admin.fondos.index') }}" class="btn-panel ghost" data-nav-link>
                            <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-panel">
                            <i class="fas fa-floppy-disk" aria-hidden="true"></i> Guardar Fondo
                        </button>
                    </div>
                </form>
            </div>

            @include('admin.fondos._modales')

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/admin-fondos.js') }}" defer></script>
    @endpush
</x-app-layout>
