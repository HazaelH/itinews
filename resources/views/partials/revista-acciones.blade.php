@php
    // Botones de acción de una revista. Se comparten entre la vista de tabla y
    // la de tarjetas. Conserva las clases `action-link` para compatibilidad.
@endphp

<div class="rev-acciones action-links">
    <a href="{{ route('revistas.show', $revista) }}"
       class="rev-accion ver action-link view"
       title="Ver revista"
       aria-label="Ver revista">
        <i class="far fa-eye" aria-hidden="true"></i>
    </a>

    <a href="{{ route('admin.revistas.edit', $revista) }}"
       class="rev-accion editar action-link edit"
       title="Editar revista"
       aria-label="Editar revista">
        <i class="fas fa-pen" aria-hidden="true"></i>
    </a>

    @if($revista->estaProcesada())
        <form action="{{ route('admin.revistas.reprocesar', $revista) }}"
              method="POST"
              onsubmit="return confirm('¿Reprocesar el PDF de esta revista?')">
            @csrf
            <button type="submit"
                    class="rev-accion reprocesar action-link reprocess"
                    title="Reprocesar PDF"
                    aria-label="Reprocesar PDF">
                <i class="fas fa-rotate" aria-hidden="true"></i>
            </button>
        </form>
    @endif

    @if(auth()->user()->isAdmin())
        <form action="{{ route('admin.revistas.toggle', $revista) }}"
              method="POST">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="rev-accion {{ $revista->estaActiva() ? 'ambar' : 'ver' }} action-link toggle"
                    title="{{ $revista->estaActiva() ? 'Ocultar del portal' : 'Mostrar en el portal' }}"
                    aria-label="{{ $revista->estaActiva() ? 'Ocultar del portal' : 'Mostrar en el portal' }}">
                <i class="fas {{ $revista->estaActiva() ? 'fa-eye-slash' : 'fa-eye' }}" aria-hidden="true"></i>
            </button>
        </form>

        <form action="{{ url('/admin/revistas/' . $revista->id) }}"
              method="POST"
              onsubmit="return confirm('¿Eliminar esta revista permanentemente?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="rev-accion eliminar action-link delete"
                    title="Eliminar revista"
                    aria-label="Eliminar revista">
                <i class="far fa-trash-can" aria-hidden="true"></i>
            </button>
        </form>
    @endif
</div>
