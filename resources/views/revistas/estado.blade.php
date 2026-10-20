<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Estado del Sistema de Revistas
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Resumen del Sistema</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <h4 class="font-semibold">Total Revistas</h4>
                            <p class="text-2xl">{{ $totalRevistas }}</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-lg">
                            <h4 class="font-semibold">Total Páginas</h4>
                            <p class="text-2xl">{{ $totalPaginas }}</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold mb-4">Detalle de Revistas</h3>
                    
                    @if($revistas->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="py-2 px-4 border">ID</th>
                                        <th class="py-2 px-4 border">Título</th>
                                        <th class="py-2 px-4 border">Páginas</th>
                                        <th class="py-2 px-4 border">Creado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($revistas as $revista)
                                    <tr class="border-b">
                                        <td class="py-2 px-4 border">{{ $revista->id }}</td>
                                        <td class="py-2 px-4 border">{{ $revista->titulo }}</td>
                                        <td class="py-2 px-4 border">{{ $revista->paginas_count }}</td>
                                        <td class="py-2 px-4 border">{{ $revista->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-yellow-600">No hay revistas en el sistema.</p>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('revistas.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Crear Nueva Revista
                        </a>
                        <a href="{{ route('revistas.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ml-2">
                            Ver Todas las Revistas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>