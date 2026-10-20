<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/panel-admin.css') }}">

    <div class="panel-page">
        <div class="panel-wrap">

            @if(session('error'))
                <div class="panel-alerta err"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ session('error') }}</div>
            @endif

            <header class="panel-head">
                <div>
                    <h1>Editar Fondo</h1>
                    <p>Ajusta la imagen, la ubicación y la vigencia de este fondo.</p>
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
                <form action="{{ route('admin.fondos.update', $fondo) }}" method="POST" enctype="multipart/form-data" id="fondoForm">
                    @csrf
                    @method('PUT')

                    @include('admin.fondos._form')

                    <div class="panel-form-pie">
                        <a href="{{ route('admin.fondos.index') }}" class="btn-panel ghost" data-nav-link>
                            <i class="fas fa-xmark" aria-hidden="true"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-panel">
                            <i class="fas fa-floppy-disk" aria-hidden="true"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

            @include('admin.fondos._modales')

            <div class="danger-zone" style="margin-top:20px;">
                <div class="txt">
                    <strong><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Eliminar fondo</strong>
                    <small>Esta acción es permanente y no se puede deshacer.</small>
                </div>
                <form action="{{ route('admin.fondos.destroy', $fondo) }}" method="POST"
                      data-confirm="¿Eliminar permanentemente el fondo «{{ $fondo->titulo }}»?"
                      data-confirm-titulo="Eliminar fondo"
                      data-confirm-ok-txt="Eliminar">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-panel vino">
                        <i class="far fa-trash-can" aria-hidden="true"></i> Eliminar
                    </button>
                </form>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/admin-fondos.js') }}" defer></script>
    @endpush
</x-app-layout>
