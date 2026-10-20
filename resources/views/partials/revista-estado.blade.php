@php
    // Estado de procesamiento de la revista (badge + barra de progreso).
    $estaProcesando = $revista->estaProcesando();

    if ($revista->estaProcesada()) {
        $clase = 'completado';
        $icono = 'fa-circle-check';
        $texto = 'Completada';
        $legacy = 'status-completed';
    } elseif ($revista->tieneError()) {
        $clase = 'error';
        $icono = 'fa-triangle-exclamation';
        $texto = 'Con error';
        $legacy = 'status-error';
    } elseif ($estaProcesando) {
        $clase = 'procesando';
        $icono = 'fa-spinner fa-spin';
        $texto = 'En proceso';
        $legacy = 'status-processing';
    } else {
        $clase = 'pendiente';
        $icono = 'fa-clock';
        $texto = 'Pendiente';
        $legacy = 'status-pending';
    }
@endphp

<span class="rev-estado {{ $clase }} status-badge {{ $legacy }}">
    <i class="fas {{ $icono }}" aria-hidden="true"></i> {{ $texto }}
</span>

@if($estaProcesando)
    <div class="progress-bar-container">
        <div class="progress-bar-fill"
             data-progress="{{ $revista->progreso }}"
             data-revista-id="{{ $revista->id }}"
             style="width: {{ round($revista->progreso) }}%"></div>
    </div>
    <div class="rev-progreso-txt revista-progreso">{{ round($revista->progreso) }}%</div>
@endif
