<x-app-layout>
    <div class="dashboard-container">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="dashboard-table">
                <div class="dashboard-table-header">
                    <div>
                        <h3>Procesando Revista</h3>
                        <p class="text-white opacity-90">Convirtiendo PDF a imágenes...</p>
                    </div>
                </div>

                <div class="p-6">
                    <div id="progreso-container">
                        <div class="text-center">
                            <input type="hidden" id="revista-id" value="{{ $revista->id }}">
                            
                            <div class="mb-6">
                                <div class="progress-bar-container" style="height: 1.5rem;">
                                    <div id="barra-progreso" 
                                         class="progress-bar-fill" 
                                         style="width: 0%"></div>
                                </div>
                                
                                <div class="flex justify-between text-sm text-gray-600 mb-2 mt-3">
                                    <span id="porcentaje" class="font-semibold text-primary">0%</span>
                                    <span id="contador-paginas" class="font-medium text-primary">0/0 páginas</span>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div id="estado" class="text-lg font-semibold text-primary mb-2">Iniciando procesamiento...</div>
                                <div id="detalles" class="text-sm text-light">
                                    Preparando la conversión de páginas...
                                </div>
                            </div>

                            <div class="info-card mb-6">
                                <h3>Información de la Revista</h3>
                                <p><strong>Título:</strong> {{ $revista->titulo }}</p>
                                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($revista->fecha_publicacion)->format('d/m/Y') }}</p>
                            </div>

                            <div id="completado" class="hidden mt-6 p-6 info-card" style="background: linear-gradient(135deg, rgba(34, 91, 78, 0.2), rgba(34, 91, 78, 0.1)); border-color: var(--primary);">
                                <div class="flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <p class="font-semibold text-xl text-primary">¡Procesamiento completado!</p>
                                </div>
                                <p class="text-primary mb-4 text-lg">La revista está lista para ser visualizada.</p>
                                <div class="mt-4 flex space-x-3 justify-center">
                                    <a href="{{ route('revistas.show', $revista) }}" 
                                       class="btn-primary">
                                        Ver Revista
                                    </a>
                                    <a href="{{ route('dashboard') }}" 
                                       class="btn-secondary">
                                        Volver al Dashboard
                                    </a>
                                </div>
                            </div>

                            <div id="error" class="hidden mt-6 p-6" style="background: linear-gradient(135deg, rgba(105, 28, 50, 0.2), rgba(105, 28, 50, 0.1)); border: 2px solid var(--dark); border-radius: 15px;">
                                <div class="flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 mr-3 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="font-semibold text-xl text-dark">Error en el procesamiento</p>
                                </div>
                                <p class="text-dark mb-4 text-lg" id="mensaje-error">Ha ocurrido un error al procesar la revista.</p>
                                <div class="mt-4 flex justify-center">
                                    <a href="{{ route('dashboard') }}" 
                                       class="btn" style="background-color: var(--dark); color: white; border-color: var(--dark);">
                                        Volver al Dashboard
                                    </a>
                                </div>
                            </div>

                            <div id="spinner" class="mt-6">
                                <div class="flex justify-center items-center">
                                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
                                    <span class="ml-3 text-primary font-semibold text-lg">Procesando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/progreso-revista.js') }}"></script>
</x-app-layout>